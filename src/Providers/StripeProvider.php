<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;
use Stephenjude\PaymentGateway\Exceptions\PaymentInitializationException;
use Stephenjude\PaymentGateway\Exceptions\PaymentVerificationException;
use Stephenjude\PaymentGateway\Gateways\StripeGateway;

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

        $intent = $this->initializeProvider([
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_types' => $this->getChannels(),
            'metadata' => $meta,
        ]);

        $reference = $intent['id'];

        $expires = config('payment-gateways.cache.session.expires');

        $sessionCacheKey = config('payment-gateways.cache.session.key').$reference;

        $routeParameters = ['reference' => $reference, 'provider' => $this->provider,];

        return Cache::remember($sessionCacheKey, $expires, fn() => new SessionDataObject(
            email: $email,
            amount: $amount,
            currency: $currency,
            provider: $this->provider,
            reference: $reference,
            channels: $this->getChannels(),
            meta: $meta,
            checkoutSecret: $intent['client_secret'],
            checkoutUrl: URL::signedRoute(config('payment-gateways.routes.checkout.name'), $routeParameters, $expires),
            callbackUrl: route(config('payment-gateways.routes.callback.name'), $routeParameters),
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
        $response = $this->http()->asForm()->post("$this->baseUrl/payment_intents", $params);

        throw_if($response->failed(), new PaymentInitializationException());

        return $response->json();
    }

    public function verifyProvider(string $reference): mixed
    {
        $response = $this->http()->asForm()->post("$this->baseUrl/payment_intents/$reference");

        throw_if($response->failed(), new PaymentVerificationException());

        return $response->json();
    }
}
