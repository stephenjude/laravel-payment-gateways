<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentTransactionData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;

class FlutterwaveProvider extends AbstractProvider
{
    public string $provider = 'flutterwave';

    public function initializeTransaction(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'FLW_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        $flutterwave = $this->request(
            method: 'GET',
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

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], fn() => new SessionData(
            provider: $this->provider,
            sessionReference: $parameters['reference'],
            paymentReference: null,
            checkoutSecret: null,
            checkoutUrl: $flutterwave['data']['link'],
            expires: $parameters['expires'],
            closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
        )
        );
    }

    public function initializeProvider(array $parameters): mixed
    {
        $response = $this->request('GET', 'v3/payments', $parameters);

        return $response['data'];
    }

    public function verifyTransaction(string $reference): array
    {
        $response = $this->request('GET', "v3/transactions/$reference/verify");

        return $response['data'];
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
                "total" => Arr::get($response, 'meta.page_info.total'),
                "page" => Arr::get($response, 'meta.page_info.current_page'),
                "page_count" => Arr::get($response, 'meta.page_info.total_pages'),
            ],
            'data' => collect($response['data'])
                ->map(fn($transaction) => $this->buildTransactionData($transaction))
                ->toArray(),
        ];
    }

    public function buildTransactionData(array $data): PaymentTransactionData
    {
        return new PaymentTransactionData(
            email: $data['customer']['email'],
            meta: $data['meta'] ?? null,
            amount: $data['amount'],
            currency: $data['currency'],
            reference: $data['reference'],
            provider: $this->provider,
            status: $data['status'],
            date: Carbon::parse($data['created_at'])->toDateTimeString(),
        );
    }
}
