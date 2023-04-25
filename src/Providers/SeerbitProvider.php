<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Seerbit\Client;
use Seerbit\Service\Standard\StandardService;
use Stephenjude\PaymentGateway\DataObjects\PaymentData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class SeerbitProvider extends AbstractProvider
{
    public string $provider = 'seerbit';

    public function http(): PendingRequest
    {
        $bearer = Http::acceptJson()
            ->contentType('application/json')
            ->post($this->baseUrl.'/encrypt/keys', [
                'key' => "$this->secretKey.$this->publicKey",
            ])
            ->json('data.EncryptedSecKey.encryptedKey');

        return Http::withToken($bearer)->acceptJson();
    }

    public function initializePayment(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'PTK_'.Str::random(12);

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
            email: $provider['email'],
            meta: ['mobile_number' => $provider['mobilenumber']],
            amount: $provider['amount'],
            currency: $provider['currency'],
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
            $token = Http::acceptJson()
                ->contentType('application/json')
                ->post($this->baseUrl.'/encrypt/keys', ['key' => "$this->secretKey.$this->publicKey",])
                ->json('data.EncryptedSecKey.encryptedKey');

            $client = new Client();
            $client->setToken($token);
            $client->setPublicKey($this->publicKey);
            $client->setSecretKey($this->secretKey);

            $response = (new StandardService($client))->Initialize($parameters);

            return $response->toArray()['data'];
        } catch (\Exception $exception) {
            throw new InitializationException($exception->getMessage(), $exception->getCode());
        }
    }

    public function verifyProvider(string $reference): mixed
    {
        $response = $this->http()->acceptJson()->get("$this->baseUrl/payments/query/$reference");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        if ($response->failed()) {
            throw new VerificationException($response->json('message'), $response->json('status'));
        }

        return $response->json('data');
    }
}
