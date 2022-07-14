<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class PaystackProvider extends AbstractProvider
{
    public string $provider = 'paystack';

    public function initializePayment(array $parameters = []): SessionDataObject
    {
        $parameters['reference'] = 'PTK_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], function () use ($parameters) {
            /*
             * Round up or down to the nearest integer because Paystack does not support decimal values.
             * Convert
             */
            $amount = Arr::get($parameters, 'amount') * 100; //round(num: Arr::get($parameters, 'amount'), mode: PHP_ROUND_HALF_ODD) * 100;

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

            return new SessionDataObject(
                provider: $this->provider,
                sessionReference: $parameters['reference'],
                checkoutUrl: $paystack['authorization_url'],
                expires: $parameters['expires'],
                closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
            );
        });
    }

    public function confirmPayment(string $paymentReference, ?SerializableClosure $closure): PaymentDataObject|null
    {
        $provider = $this->verifyProvider($paymentReference);

        $payment = new PaymentDataObject(
            email: $provider['customer']['email'],
            meta: $provider['metadata'],
            amount: ($provider['amount'] / 100),
            currency: $provider['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            status: $provider['status'],
            date: Carbon::parse($provider['transaction_date'])->toDateTimeString(),
        );

        if ($closure) {
            $closure($payment);
        }

        return $payment;
    }

    public function initializeProvider(array $params): mixed
    {
        logger('Params: ', $params);

        $response = $this->http()->acceptJson()->post("$this->baseUrl/transaction/initialize", $params);

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        if ($response->failed()) {
            throw new InitializationException($response->json('message'), $response->status());
        }

        return $response->json('data');
    }

    public function verifyProvider(string $reference): mixed
    {
        $response = $this->http()->acceptJson()->get("$this->baseUrl/transaction/verify/$reference");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        if ($response->failed()) {
            throw new VerificationException($response->json('message'), $response->json('status'));
        }

        return $response->json('data');
    }
}
