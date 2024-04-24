<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\DataObjects\TransactionData;

class Pay4meProvider extends AbstractProvider
{
    public string $provider = 'pay4me';

    public function initializeCheckout(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'P4M'.now()->timestamp;

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        /*
         * Convert and round decimals to the nearest integer because Paystack does not support decimal values.
         */
        $amount = Arr::get($parameters, 'amount') * 100;

        $pay4me = $this->request(
            method: 'POST',
            path: 'api/transactions/initialize',
            payload: [
                'email' => Arr::get($parameters, 'email'),
                'amount' => $amount,
                'currency' => Arr::get($parameters, 'currency'),
                'reference' => Arr::get($parameters, 'reference'),
                'channels' => $this->getChannels(),
                'metadata' => Arr::get($parameters, 'meta'),
                'callback_url' => $parameters['callback_url']
                    ?? route(config('payment-gateways.routes.callback.name'), [
                        'reference' => $parameters['reference'],
                        'provider' => $this->provider,
                    ]),
            ]
        );

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], fn () => new SessionData(
            provider: $this->provider,
            sessionReference: $parameters['reference'],
            paymentReference: null,
            checkoutSecret: null,
            checkoutUrl: $pay4me['data']['authorization_url'],
            expires: $parameters['expires'],
            closure: Arr::has($parameters, 'closure') ? new SerializableClosure($parameters['closure']) : null,
        ));
    }

    public function findTransaction(string $reference): TransactionData
    {
        $response = $this->request('GET', $this->baseUrl."api/transactions/verify/$reference");

        return $this->transactionDTO($response['data']);
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
        $payload = array_filter([
            'from' => $from,
            'to' => $to,
            'page' => $page,
            'customer' => $customer,
            'status' => $status,
            'amount' => $amount,
        ]);

        $response = $this->request('GET', 'api/transactions', $payload);

        return [
            'meta' => [
                'total' => Arr::get($response, 'meta.total'),
                'page' => Arr::get($response, 'meta.page'),
                'page_count' => Arr::get($response, 'meta.pageCount'),
            ],
            'data' => collect($response['data'])
                ->map(fn ($transaction) => $this->transactionDTO($transaction))
                ->toArray(),
        ];
    }

    public function transactionDTO(array $transaction): TransactionData
    {
        $date = Arr::get($transaction, 'paid_at') ?? Arr::get($transaction, 'created_at');

        return new TransactionData(
            email: $transaction['customer']['email'],
            meta: $transaction['metadata'],
            amount: ($transaction['amount'] / 100),
            currency: $transaction['currency'],
            reference: $transaction['reference'],
            provider: $this->provider,
            status: $transaction['status'],
            date: Carbon::parse($date)->toDateTimeString(),
        );
    }
}
