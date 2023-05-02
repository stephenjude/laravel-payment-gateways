<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Seerbit\Client;
use Seerbit\Service\Standard\StandardService;
use Seerbit\Service\Status\TransactionStatusService;
use Stephenjude\PaymentGateway\DataObjects\PaymentData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class SeerbitProvider extends AbstractProvider
{
    public string $provider = 'seerbit';

    public function initializePayment(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'SEBT_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        return Cache::remember(
            key: $parameters['session_cache_key'],
            ttl: $parameters['expires'],
            callback: function () use ($parameters) {
                $seerbit = $this->initializeProvider([
                    'email' => Arr::get($parameters, 'email'),
                    'amount' => Arr::get($parameters, 'amount'),
                    'currency' => Arr::get($parameters, 'currency'),
                    'country' => Arr::get($parameters, 'currency'),
                    'paymentReference' => Arr::get($parameters, 'reference'),
                    'tokenize' => true,
                    'callbackUrl' => $parameters['callback_url']
                        ?? route(config('payment-gateways.routes.callback.name'), [
                            'reference' => $parameters['reference'],
                            'provider' => $this->provider,
                        ]),
                ]);

                return new SessionData(
                    provider: $this->provider,
                    sessionReference: $parameters['reference'],
                    paymentReference: null,
                    checkoutSecret: null,
                    checkoutUrl: $seerbit['payments']['redirectLink'],
                    expires: $parameters['expires'],
                    closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
                );
            }
        );
    }

    public function confirmPayment(string $paymentReference, ?SerializableClosure $closure): PaymentData|null
    {
        $provider = $this->verifyProvider($paymentReference);

        $provider['payments'] = $provider;

        $payment = new PaymentData(
            email: $provider['data']['payments']['email'],
            meta: ['mobile_number' => $provider['data']['payments']['mobilenumber']],
            amount: $provider['amount'],
            currency: 'NGN',
            reference: $paymentReference,
            provider: $this->provider,
            status: $provider['status'],
            date: Carbon::parse($provider['transaction_date'])->toDateTimeString(),
        );

        if ($closure && $payment) {
            $closure($payment);
        }

        return $payment;
    }

    public function initializeProvider(array $parameters): mixed
    {
        try {
            $response = (new StandardService($this->getClient()))->Initialize($parameters);

            return $response->toArray()['data'];
        } catch (\Exception $exception) {
            throw new InitializationException($exception->getMessage(), $exception->getCode());
        }
    }

    public function verifyProvider(string $reference): mixed
    {
        try {
            $response = (new TransactionStatusService($this->getClient()))->ValidateTransactionStatus($reference);

            return $response->toArray();
        } catch (\Exception $exception) {
            throw new VerificationException($exception->getMessage(), $exception->getCode());
        }
    }

    public function getClient(): Client
    {
        $token = $this->getToken();

        $client = new Client();
        $client->setToken($token);
        $client->setPublicKey($this->publicKey);
        $client->setSecretKey($this->secretKey);

        return $client;
    }

    private function getToken()
    {
        return Http::acceptJson()
            ->contentType('application/json')
            ->post($this->baseUrl.'/encrypt/keys', ['key' => "$this->secretKey.$this->publicKey"])
            ->json('data.EncryptedSecKey.encryptedKey');
    }
}
