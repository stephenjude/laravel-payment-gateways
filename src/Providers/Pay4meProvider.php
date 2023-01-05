<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class Pay4meProvider extends AbstractProvider
{
    public string $provider = 'pay4me';

    public function initializePayment(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'P4M'.now()->timestamp;

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], function () use ($parameters) {
            /*
             * Convert and round decimals to the nearest integer because Paystack does not support decimal values.
             */
            $amount = round(num: (Arr::get($parameters, 'amount') * 100), mode: PHP_ROUND_HALF_ODD);

            $paystack = $this->initializeProvider([
                'email' => Arr::get($parameters, 'email'),
                'amount' => $amount,
                'currency' => Arr::get($parameters, 'currency'),
                'reference' => Arr::get($parameters, 'reference'),
                'channels' => $this->getChannels(),
                'metadata' => Arr::get($parameters, 'meta'),
                'callback_url' => $parameters['callback_url']
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
                checkoutUrl: $paystack['authorization_url'],
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
            meta: $provider['metadata'],
            amount: ($provider['amount'] / 100),
            currency: $provider['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            status: $provider['status'],
            date: Carbon::parse(($provider['paid_at'] ?? $provider['created_at']))->toDateTimeString(),
        );

        if ($closure && $payment) {
            $closure($payment);
        }

        return $payment;
    }

    public function initializeProvider(array $parameters): mixed
    {
        $response = $this->http()->acceptJson()->post("$this->baseUrl/transactions/initialize", $parameters);

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        if ($response->failed()) {
            throw new InitializationException($response->json('eror.message'), $response->status());
        }

        return $response->json('data');
    }

    public function verifyProvider(string $reference): mixed
    {
        $response = $this->http()->acceptJson()->get("$this->baseUrl/transactions/verify/$reference");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        if ($response->failed()) {
            throw new VerificationException($response->json('error.message'), $response->json('status'));
        }

        return $response->json('data');
    }
}
