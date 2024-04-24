<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\DataObjects\TransactionData;

class FlutterwaveProvider extends AbstractProvider
{
    public string $provider = 'flutterwave';

    public function initializeCheckout(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'FLW_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        $flutterwave = $this->request(
            method: 'POST',
            path: 'v3/payments',
            payload: [
                'amount' => Arr::get($parameters, 'amount'),
                'currency' => Arr::get($parameters, 'currency'),
                'tx_ref' => Arr::get($parameters, 'reference'),
                'payment_options' => implode(', ', $this->getChannels()),
                'customer' => ['email' => Arr::get($parameters, 'email')],
                'meta' => Arr::get($parameters, 'meta'),
                'redirect_url' => Arr::get(
                    array: $parameters,
                    key: 'callback_url',
                    default: route(config('payment-gateways.routes.callback.name'), [
                        'reference' => $parameters['reference'],
                        'provider' => $this->provider,
                    ])
                ),
            ]
        );

        return Cache::remember(
            $parameters['session_cache_key'],
            $parameters['expires'],
            fn () => new SessionData(
                provider: $this->provider,
                sessionReference: $parameters['reference'],
                paymentReference: null,
                checkoutSecret: null,
                checkoutUrl: $flutterwave['data']['link'],
                expires: $parameters['expires'],
                closure: Arr::get($parameters, 'closure') ? new SerializableClosure($parameters['closure']) : null,
            )
        );
    }

    public function findTransaction(string $reference): TransactionData
    {
        $response = $this->request('GET', "v3/transactions/$reference/verify");

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
        $queryParameters = array_filter([
            'from' => $from,
            'to' => $to,
            'page' => $page,
            'customer_email' => $customer,
            'status' => $status,
            'tx_ref' => $reference,
        ]);

        $response = $this->request('GET', 'v3/transactions', $queryParameters);

        return [
            'meta' => [
                'total' => Arr::get($response, 'meta.page_info.total'),
                'page' => Arr::get($response, 'meta.page_info.current_page'),
                'page_count' => Arr::get($response, 'meta.page_info.total_pages'),
            ],
            'data' => collect($response['data'])
                ->map(fn ($transaction) => $this->transactionDTO($transaction))
                ->toArray(),
        ];
    }

    public function transactionDTO(array $transaction): TransactionData
    {
        return new TransactionData(
            email: $transaction['customer']['email'],
            meta: $transaction['meta'] ?? null,
            amount: $transaction['amount'],
            currency: $transaction['currency'],
            reference: $transaction['reference'],
            provider: $this->provider,
            status: $transaction['status'],
            date: Carbon::parse($transaction['created_at'])->toDateTimeString(),
        );
    }
}
