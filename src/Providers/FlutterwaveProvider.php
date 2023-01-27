<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class FlutterwaveProvider extends AbstractProvider
{
    public string $provider = 'flutterwave';

    public function initializePayment(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'FLW_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        return Cache::remember(
            $parameters['session_cache_key'],
            $parameters['expires'],
            function () use ($parameters) {
                $flutterwave = $this->initializeProvider([
                    'amount' => Arr::get($parameters, 'amount'),
                    'currency' => Arr::get($parameters, 'currency'),
                    'tx_ref' => Arr::get($parameters, 'reference'),
                    'payment_options' => implode(", ", $this->getChannels()),
                    'customer' => ['email' => Arr::get($parameters, 'email')],
                    'meta' => Arr::get($parameters, 'meta'),
                    'redirect_url' => Arr::get(
                        $parameters,
                        'callback_url',
                        route(config('payment-gateways.routes.callback.name'), [
                            'reference' => $parameters['reference'],
                            'provider' => $this->provider,
                        ])
                    ),
                ]);


                return new SessionData(
                    provider: $this->provider,
                    sessionReference: $parameters['reference'],
                    paymentReference: null,
                    checkoutSecret: null,
                    checkoutUrl: $flutterwave['link'],
                    expires: $parameters['expires'],
                    closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
                );
            }
        );
    }

    public function confirmPayment(string $paymentReference, SerializableClosure|null $closure): PaymentData|null
    {
        $provider = $this->verifyProvider($paymentReference);

        $payment = new PaymentData(
            email: $provider['customer']['email'],
            meta: $provider['meta'] ?? null,
            amount: $provider['amount'],
            currency: $provider['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            status: $provider['status'],
            date: Carbon::parse($provider['created_at'])->toDateTimeString(),
        );

        if ($closure && $payment) {
            $closure($payment);
        }

        return $payment;
    }

    public function initializeProvider(array $parameters): mixed
    {
        $response = $this->http()->acceptJson()->post("$this->baseUrl/payments", $parameters);

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if($response->failed(), new InitializationException($response->json('message')));

        throw_if(is_null($response->json('data')), new InitializationException($response->json('message')));

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
