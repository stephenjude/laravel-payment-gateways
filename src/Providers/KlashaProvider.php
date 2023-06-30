<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\DataObjects\TransactionData;

class KlashaProvider extends AbstractProvider
{
    public string $provider = 'klasha';

    public function initializeTransaction(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'KSA_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        $sessionData = [
            'provider' => $this->provider,
            'sessionReference' => $parameters['reference'],
            'paymentReference' => $parameters['reference'],
            'checkoutSecret' => null,
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

        return Cache::remember(
            key: $parameters['session_cache_key'],
            ttl: $parameters['expires'],
            callback: fn() => new SessionData(...$sessionData)
        );
    }

    public function findTransaction(string $reference): TransactionData
    {
        $transaction = $this->request(
            method: 'POST',
            path: 'nucleus/tnx/merchant/status',
            payload: ['tnxRef' => $reference]
        );

        $transaction['data']['reference'] = $reference;

        return $this->transactionDTO($transaction['data']);
    }

    public function listTransactions(
        ?string $from = null,
        ?string $to = null,
        ?string $page = null,
        ?string $status = null,
        ?string $reference = null,
        ?string $amount = null,
        ?string $customer = null,
    ): array|null {
        throw new \Exception("This provider [$this->provider] does not support list transactions");
    }

    public function transactionDTO(array $transaction): TransactionData
    {
        return new TransactionData(
            email: $transaction['customer']['email'],
            meta: $transaction['customer'],
            amount: $transaction['sourceAmount'],
            currency: $transaction['sourceCurrency'],
            reference: $transaction['reference'],
            provider: $this->provider,
            status: $transaction['status'],
            date: Carbon::now()->toDateTimeString(),
        );
    }
}
