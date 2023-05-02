<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentData;
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
            ->post($this->baseUrl.'/auth/login')
            ->json('responseBody.accessToken');

        return Http::withToken($token)->acceptJson();
    }

    public function initializePayment(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'MNFY_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], function () use ($parameters) {
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

            return new SessionData(
                provider: $this->provider,
                sessionReference: $parameters['reference'],
                paymentReference: $monnify['transactionReference'],
                checkoutSecret: null,
                checkoutUrl: $monnify['checkoutUrl'],
                expires: $parameters['expires'],
                closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
            );
        });
    }

    public function confirmPayment(string $paymentReference, ?SerializableClosure $closure): PaymentData|null
    {
        $provider = $this->verifyProvider($paymentReference);

        $payment = new PaymentData(
            email: $provider['customer']['email'],
            meta: $provider['metaData'],
            amount: $provider['amountPaid'],
            currency: $provider['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            status: $provider['paymentStatus'],
            date: Carbon::parse($provider['paidOn'])->toDateTimeString(),
        );

        $this->executeClosure($closure, $payment);

        return $payment;
    }

    public function initializeProvider(array $parameters): mixed
    {
        $response = $this->http()
            ->acceptJson()
            ->post(
                url: "$this->baseUrl/merchant/transactions/init-transaction",
                data: $parameters
            );

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        if ($response->failed()) {
            throw new InitializationException($response->json('message'), $response->status());
        }

        return $response->json('responseBody');
    }

    public function verifyProvider(string $reference): mixed
    {
        $response = $this->http()->acceptJson()->get("$this->baseUrl/transactions/$reference");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        if ($response->failed()) {
            throw new VerificationException($response->json('message'), $response->json('status'));
        }

        return $response->json('responseBody');
    }
}
