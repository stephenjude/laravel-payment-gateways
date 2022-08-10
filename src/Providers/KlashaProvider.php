<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class KlashaProvider extends AbstractProvider
{
    public string $provider = 'klasha';

    public function initializePayment(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'KSA_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        $sessionData = $this->initializeProvider($parameters);

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], fn() => new SessionData(
            ...$sessionData
        ));
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
            date: Carbon::parse($provider['transaction_date'])->toDateTimeString(),
        );

        if ($closure && $payment) {
            $closure($payment);
        }

        return $payment;
    }

    public function initializeProvider(array $parameters): mixed
    {
        return [
            'provider' => $this->provider,
            'sessionReference' => $parameters['reference'],
            'paymentReference' => $parameters['reference'],
            'checkoutUrl' => route(config('payment-gateways.routes.checkout.name'), [
                'reference' => $parameters['reference'],
                'provider' => $this->provider,
            ]),
            'expires' => $parameters['expires'],
            'closure' => $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
            'extra' => [
                'email' => $parameters['email'],
                'currency' => $parameters['currency'],
                'amount' => $parameters['amount'],
                'channels' => $this->getChannels(),
                'is_test_mode' => false,
                'callback_url' => route(config('payment-gateways.routes.callback.name'), [
                    'reference' => $parameters['reference'],
                    'provider' => $this->provider,
                ]),
            ],
        ];
    }

    public function verifyProvider(string $reference): mixed
    {
        $response = $this->http()->acceptJson()->post("$this->baseUrl/nucleus/tnx/merchant/status", [
            "tnxRef" => $reference,
        ]);

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        if ($response->failed()) {
            throw new VerificationException('Payment verification was not successful.', $response->status());
        }

        return $response->json('data');
    }
}
