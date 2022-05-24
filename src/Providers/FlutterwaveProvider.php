<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;
use Stephenjude\PaymentGateway\Exceptions\PaymentInitializationException;
use Stephenjude\PaymentGateway\Exceptions\PaymentVerificationException;
use Stephenjude\PaymentGateway\Gateways\FlutterwaveGateway;

class FlutterwaveProvider extends AbstractProvider
{
    public string $provider = 'flutterwave';

    public function setChannels(array $channels): self
    {
        $this->channels = $channels;

        return $this;
    }

    public function getChannels(): array|null
    {
        return $this->channels ?? config('payment-gateways.providers.flutterwave.channels');
    }

    public function initializeSession(
        string $currency,
        float|int $amount,
        string $email,
        array $meta = []
    ): SessionDataObject {
        $reference = 'FLW_'.Str::random(10);

        $expires = config('payment-gateways.cache.session.expires');

        $sessionCacheKey = config('payment-gateways.cache.session.key').$reference;

        return Cache::remember($sessionCacheKey, $expires, fn() => new SessionDataObject(
            email: $email,
            meta: $meta,
            amount: $amount * 100,
            currency: $currency,
            channels: $this->getChannels(),
            provider: $this->provider,
            reference: $reference,
            checkoutUrl: URL::signedRoute(config('payment-gateways.routes.checkout.name'), [
                'reference' => $reference,
                'provider' => $this->provider,
            ], $expires),
            callbackUrl: route(config('payment-gateways.routes.callback.name'), [
                'reference' => $reference,
                'provider' => $this->provider,
            ]),
            expires: $expires
        ));
    }

    public function verifyReference(string $paymentReference): PaymentDataObject|null
    {
        $payment = $this->verifyReference($paymentReference);

        return new PaymentDataObject(
            email: $payment['customer']['email'],
            meta: $payment['metadata'] ?? null,
            amount: ($payment['amount'] / 100),
            currency: $payment['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            successful: $payment['status'] === 'success',
            date: Carbon::parse($payment['transaction_date'])->toDateTimeString(),
        );
    }

    public function initializeProvider(array $params): mixed
    {
        $response = $this->http()->acceptJson()->post("$this->baseUrl/payments");

        throw_if($response->failed(), new PaymentInitializationException());

        return $response->json('data');
    }

    public function verifyProvider(string $reference): mixed
    {
        $response = $this->http()->acceptJson()->post("$this->baseUrl/transactions/$reference/verify");

        throw_if($response->failed(), new PaymentVerificationException());

        return $response->json('data');
    }
}
