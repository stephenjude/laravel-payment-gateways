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

class FlutterwaveProvider extends AbstractProvider
{
    public string $provider = 'flutterwave';

    public function initializeSession(
        string $currency,
        float|int $amount,
        string $email,
        array $meta = []
    ): SessionDataObject {
        $reference = 'FLW_'.Str::random(10);

        $expires = config('payment-gateways.cache.session.expires');

        $sessionCacheKey = config('payment-gateways.cache.session.key').$reference;

        return Cache::remember(
            $sessionCacheKey,
            $expires,
            function () use ($email, $amount, $currency, $reference, $meta, $expires) {
                $callbackUrl = route(config('payment-gateways.routes.callback.name'), [
                    'reference' => $reference,
                    'provider' => $this->provider,
                ]);

                $flutterwave = $this->initializeProvider([
                    'amount' => $amount,
                    'currency' => $currency,
                    'tx_ref' => $reference,
                    'redirect_url' => $callbackUrl,
                    'payment_options' => implode(", ", $this->getChannels()),
                    'customer' => ['email' => $email,],
                    'meta' => $meta,
                ]);


                return new SessionDataObject(
                    provider: $this->provider,
                    sessionReference: $reference,
                    checkoutUrl: $flutterwave['link'],
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
            meta: $payment['meta'] ?? null,
            amount: $payment['amount'],
            currency: $payment['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            successful: $payment['status'] === 'successful',
            date: Carbon::parse($payment['created_at'])->toDateTimeString(),
        );
    }

    public function initializeProvider(array $params): mixed
    {
        $response = $this->http()->acceptJson()->post("$this->baseUrl/payments", $params);

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if($response->failed(), new InitializationException());

        throw_if(is_null($response->json('data')), new InitializationException());

        return $response->json('data');
    }

    public function verifyProvider(string $reference): mixed
    {
        $response = $this->http()->acceptJson()->get("$this->baseUrl/transactions/$reference/verify");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if($response->failed(), new VerificationException());

        return $response->json('data');
    }
}
