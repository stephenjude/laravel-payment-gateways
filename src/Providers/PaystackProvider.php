<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentTransactionData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;

class PaystackProvider extends AbstractProvider
{
    public string $provider = 'paystack';

    public function initializeTransaction(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'PTK_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        /*
        * Convert and round decimals to the nearest integer because Paystack does not support decimal values.
        */
        $amount = round(num: (Arr::get($parameters, 'amount') * 100), mode: PHP_ROUND_HALF_ODD);

        $paystack = $this->request(
            method: 'POST',
            path: 'transaction/initialize',
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
            checkoutUrl: $paystack['data']['authorization_url'],
            expires: $parameters['expires'],
            closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
        ));
    }

    public function verifyTransaction(string $reference): mixed
    {
        $transaction = $this->request('GET', "transaction/verify/$reference");

        return $transaction['data'];
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

        $response = $this->request('GET', 'transaction', $payload);

        return [
            'meta' => [
                'total' => Arr::get($response, 'meta.total'),
                'page' => Arr::get($response, 'meta.page'),
                'page_count' => Arr::get($response, 'meta.pageCount'),
            ],
            'data' => collect($response['data'])
                ->map(fn ($transaction) => $this->buildTransactionData($transaction))
                ->toArray(),
        ];
    }

    public function buildTransactionData(array $transaction): PaymentTransactionData
    {
        $date = Arr::get($transaction, 'transaction_date') ?? Arr::get($transaction, 'created_at');

        return new PaymentTransactionData(
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
