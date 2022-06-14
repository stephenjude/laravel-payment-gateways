<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class StripeProvider extends AbstractProvider
{
    public string $provider = 'stripe';

    public function initializeSession(
        string $currency,
        float|int $amount,
        string $email,
        array $meta = []
    ): SessionDataObject {
        $amount *= 100;

        $sessionReference = 'STP_'.Str::random(12);

        $expires = config('payment-gateways.cache.session.expires');

        $callbackUrl = route(config('payment-gateways.routes.callback.name'), [
            'reference' => $sessionReference,
            'provider' => $this->provider,
        ]);

        $stripe = $this->initializeProvider([
            'line_items' => [
                [
                    'price_data' => [
                        'unit_amount' => $amount,
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => $sessionReference,
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'customer_email' => $email,
            'payment_method_types' => $this->getChannels(),
            'metadata' => $meta,
            'mode' => 'payment',
            'success_url' => $callbackUrl,
            'cancel_url' => $callbackUrl,
        ]);

        $sessionCacheKey = config('payment-gateways.cache.session.key').$sessionReference;

        return Cache::remember($sessionCacheKey, $expires, fn () => new SessionDataObject(
            provider: $this->provider,
            sessionReference: $sessionCacheKey,
            paymentReference: $stripe['payment_intent'],
            checkoutUrl: $stripe['url'],
            expires: $expires
        ));
    }

    public function verifyReference(string $paymentReference): PaymentDataObject|null
    {
        $payment = $this->verifyProvider($paymentReference);

        return new PaymentDataObject(
            email: Arr::get($payment['charges'], 'data.0.billing_details.email'),
            meta: $payment['metadata'],
            amount: ($payment['amount'] / 100),
            currency: $payment['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            successful: $payment['status'] === 'succeeded',
            date: null,
        );
    }

    public function initializeProvider(array $params): mixed
    {
        $response = $this->http()->asForm()->post("$this->baseUrl/checkout/sessions", $params);

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
}
