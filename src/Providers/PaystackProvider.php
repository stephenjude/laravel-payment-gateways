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

        return Cache::remember(
            $parameters['session_cache_key'],
            $parameters['expires'],
            function () use ($parameters) {
                $paystack = $this->initializeProvider([
                    'email' => Arr::get($parameters, 'email'),
                    'amount' => Arr::get($parameters, 'amount', 0) * 100,
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
                    closure: new SerializableClosure($parameters['closure']),
                );
            }
        );
    }

    public function confirmPayment(string $paymentReference, ?SerializableClosure $closure): PaymentDataObject|null
    {
        $payment = $this->verifyProvider($paymentReference);

        $payment = new PaymentDataObject(
            email: $payment['customer']['email'],
            meta: $payment['metadata'],
            amount: ($payment['amount'] / 100),
            currency: $payment['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            successful: $payment['status'] === 'success',
            date: Carbon::parse($payment['transaction_date'])->toDateTimeString(),
        );

        if ($closure) {
            $closure($payment);
        }

        return $payment;
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
