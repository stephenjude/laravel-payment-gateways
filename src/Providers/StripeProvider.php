<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class StripeProvider extends AbstractProvider
{
    public string $provider = 'stripe';

    public function initializePayment(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'STP_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        $parameters['callback_url'] ??= route(config('payment-gateways.routes.callback.name'), [
            'reference' => $parameters['reference'],
            'provider' => $this->provider,
        ]);

        $stripe = $this->initializeProvider($parameters);

        dd($stripe);

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], fn () => new SessionData(
            provider: $this->provider,
            sessionReference: $parameters['session_cache_key'],
            paymentReference: $stripe['id'],
            checkoutSecret: null,
            checkoutUrl: $stripe['url'],
            expires: $parameters['expires'],
            closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
        ));
    }

    public function confirmPayment(string $paymentReference, SerializableClosure|null $closure): PaymentData|null
    {
        $provider = $this->verifyProvider($paymentReference);

        $payment = new PaymentData(
            email: Arr::get($provider['charges'], 'data.0.billing_details.email'),
            meta: $provider['metadata'],
            amount: ($provider['amount'] / 100),
            currency: $provider['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            status: $provider['status'],
            date: null,
        );

        if ($closure && $payment) {
            $closure($payment);
        }

        return $payment;
    }

    public function initializeProvider(array $parameters): mixed
    {
        $response = $this->http()->asForm()->post(
            "$this->baseUrl/checkout/sessions",
            $this->getProviderInitializationRequestParams($parameters)
        );

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if($response->failed(), new InitializationException($response->json('message')));

        return $response->json();
    }

    public function verifyProvider(string $reference): mixed
    {
        $checkoutSession = $this->http()
            ->asForm()
            ->get("$this->baseUrl/checkout/sessions/$reference");

        $this->logResponseIfEnabledDebugMode($this->provider, $checkoutSession);

        throw_if(
            condition: $checkoutSession->failed(),
            exception: new VerificationException($checkoutSession->json('error.message'))
        );

        $paymentIntent = $checkoutSession->json('payment_intent');

        $response = $this->http()->asForm()->post("$this->baseUrl/payment_intents/$paymentIntent");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if(
            condition: $response->failed(),
            exception: new VerificationException($response->json('error.message'))
        );

        return $response->json();
    }

    private function getProviderInitializationRequestParams(array $parameters): array
    {
        return [
            'line_items' => [
                [
                    'price_data' => [
                        'unit_amount' => (Arr::get($parameters, 'amount') * 100),
                        'currency' => strtolower(Arr::get($parameters, 'currency')),
                        'product_data' => [
                            'name' => $parameters['reference'],
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'customer_email' => Arr::get($parameters, 'email'),
            'payment_method_types' => $this->getChannels(),
            'metadata' => Arr::get($parameters, 'meta'),
            'mode' => 'payment',
            'success_url' => $parameters['callback_url'],
            'cancel_url' => $parameters['callback_url'],
        ];
    }
}
