<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class StripeProvider extends AbstractProvider
{
    public string $provider = 'stripe';

    public function initializePayment(array $parameters = []): SessionDataObject
    {
        $parameters['amount'] *= 100;

        $parameters['reference'] = 'STP_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        $parameters['callback_url'] ??= route(config('payment-gateways.routes.callback.name'), [
            'reference' => $parameters['reference'],
            'provider' => $this->provider,
        ]);

        $stripe = $this->initializeProvider($parameters);

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], fn () => new SessionDataObject(
            provider: $this->provider,
            sessionReference: $parameters['session_cache_key'],
            paymentReference: $stripe['payment_intent'],
            checkoutUrl: $stripe['url'],
            expires: $parameters['expires'],
            closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
        ));
    }

    public function confirmPayment(string $paymentReference, SerializableClosure|null $closure): PaymentDataObject|null
    {
        $provider = $this->verifyProvider($paymentReference);

//        dd($provider);

        $payment = new PaymentDataObject(
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

    public function initializeProvider(array $params): mixed
    {
        $response = $this->http()->asForm()->post(
            "$this->baseUrl/checkout/sessions",
            $this->getProviderInitializationRequestParams($params)
        );

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if($response->failed(), new InitializationException());

        return $response->json();
    }

    public function verifyProvider(string $reference): mixed
    {
        $response = $this->http()->asForm()->post("$this->baseUrl/payment_intents/$reference");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if($response->failed(), new VerificationException());

        return $response->json();
    }

    private function getProviderInitializationRequestParams(array $parameters): array
    {
        return [
            'line_items' => [
                [
                    'price_data' => [
                        'unit_amount' => Arr::get($parameters, 'amount'),
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
