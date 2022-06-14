<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class PaystackProvider extends AbstractProvider
{
    public string $provider = 'paystack';

    public function initializeSession(
        string $currency,
        float|int $amount,
        string $email,
        array $meta = []
    ): SessionDataObject {
        $reference = 'PTK_'.Str::random(12);

        $expires = config('payment-gateways.cache.session.expires');

        $sessionCacheKey = config('payment-gateways.cache.session.key').$reference;

        return Cache::remember(
            $sessionCacheKey,
            $expires,
            function () use ($email, $amount, $currency, $reference, $meta, $expires) {
                $amount *= 100;

                $callbackUrl = route(config('payment-gateways.routes.callback.name'), [
                    'reference' => $reference,
                    'provider' => $this->provider,
                ]);

                $paystack = $this->initializeProvider([
                    'email' => $email,
                    'amount' => $amount,
                    'currency' => $currency,
                    'reference' => $reference,
                    'channels' => $this->getChannels(),
                    'metadata' => $meta,
                    'callback_url' => $callbackUrl,
                ]);

                return new SessionDataObject(
                    provider: $this->provider,
                    sessionReference: $reference,
                    checkoutUrl: $paystack['authorization_url'],
                    expires: $expires
                );
            }
        );
    }

    public function verifyReference(string $paymentReference): PaymentDataObject|null
    {
        $payment = $this->verifyProvider($paymentReference);

        return new PaymentDataObject(
            email: $payment['customer']['email'],
            meta: $payment['metadata'],
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
        $response = $this->http()->acceptJson()->post("$this->baseUrl/transaction/initialize", $params);

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if($response->failed(), new InitializationException());

        return $response->json('data');
    }

    public function verifyProvider(string $reference): mixed
    {
        $response = $this->http()->acceptJson()->get("$this->baseUrl/transaction/verify/$reference");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if($response->failed(), new VerificationException());

        return $response->json('data');
    }
}
