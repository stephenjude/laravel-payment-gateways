<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentTransactionData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class MonnifyProvider extends AbstractProvider
{
    public string $provider = 'monnify';

    public function http(): PendingRequest
    {
        $token = Http::acceptJson()
            ->withHeaders([
                'Authorization' => 'Basic '.base64_encode("$this->publicKey:$this->secretKey"),
            ])
            ->post($this->baseUrl.'api/v1/auth/login')
            ->json('responseBody.accessToken');

        return Http::withToken($token)->acceptJson();
    }

    public function initializeTransaction(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'MNFY_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        $monnify = $this->initializeProvider([
            'customerEmail' => $email = Arr::get($parameters, 'email'),
            'customerName' => Arr::get($parameters, 'meta.name', $email),
            'amount' => Arr::get($parameters, 'amount'),
            'currencyCode' => Arr::get($parameters, 'currency'),
            'contractCode' => config('payment-gateways.providers.monnify.contract_code'),
            'paymentReference' => Arr::get($parameters, 'reference'),
            'paymentMethods' => $this->getChannels(),
            'redirectUrl' => $parameters['callback_url']
                ?? route(config('payment-gateways.routes.callback.name'), [
                    'reference' => $parameters['reference'],
                    'provider' => $this->provider,
                ]),
        ]);

        $sessionData = new SessionData(
            provider: $this->provider,
            sessionReference: $parameters['reference'],
            paymentReference: $monnify['transactionReference'],
            checkoutSecret: null,
            checkoutUrl: $monnify['checkoutUrl'],
            expires: $parameters['expires'],
            closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
        );

        return Cache::remember(
            key: $parameters['session_cache_key'],
            ttl: $parameters['expires'],
            callback: fn () => $sessionData
        );
    }

    public function confirmTransaction(string $reference, ?SerializableClosure $closure): PaymentTransactionData|null
    {
        $monnify = $this->verifyTransaction($reference);

        $payment = new PaymentTransactionData(
            email: $monnify['customerDTO']['email'],
            meta: $monnify['metaData'],
            amount: $monnify['amountPaid'],
            currency: $monnify['currencyCode'],
            reference: $reference,
            provider: $this->provider,
            status: $monnify['paymentStatus'],
            date: Carbon::parse($monnify['completedOn'])->toDateTimeString(),
        );

        $this->executeClosure($closure, $payment);

        return $payment;
    }

    public function initializeProvider(array $parameters): mixed
    {
        $response = $this->http()
            ->acceptJson()
            ->post(
                url: $this->baseUrl.'api/v1/merchant/transactions/init-transaction',
                data: $parameters
            );

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        if ($response->failed()) {
            throw new InitializationException($response->json('message'), $response->status());
        }

        return $response->json('responseBody');
    }

    public function verifyTransaction(string $reference): mixed
    {
        $response = $this->http()->acceptJson()->get($this->baseUrl."api/v1/transactions/$reference");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        if ($response->failed()) {
            throw new VerificationException($response->json('message'), $response->json('status'));
        }

        return $response->json('responseBody');
    }
}
