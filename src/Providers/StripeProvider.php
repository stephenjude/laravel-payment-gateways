<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\DataObjects\TransactionData;

class StripeProvider extends AbstractProvider
{
    public string $provider = 'stripe';

    public function initializeCheckout(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'STP_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        $parameters['callback_url'] ??= route(config('payment-gateways.routes.callback.name'), [
            'reference' => $parameters['reference'],
            'provider' => $this->provider,
        ]);

        $stripe = $this->request(
            method: 'POST',
            path: 'v1/checkout/sessions',
            payload: $this->prepareInitializationData($parameters)
        );

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], fn () => new SessionData(
            provider: $this->provider,
            sessionReference: $parameters['session_cache_key'],
            paymentReference: $stripe['id'],
            checkoutSecret: null,
            checkoutUrl: $stripe['url'],
            expires: $parameters['expires'],
            closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
        ));
    }

    public function findTransaction(string $reference): TransactionData
    {
        $response = $this->request('GET', "v1/checkout/sessions/$reference");

        $paymentIntent = $response['payment_intent'];

        $transaction = $this->request('POST', "v1/payment_intents/$paymentIntent");

        $transaction['reference'] = $reference;

        return $this->transactionDTO($transaction);
    }

    private function prepareInitializationData(array $parameters): array
    {
        return [
            'line_items' => [
                [
                    'price_data' => [
                        'unit_amount' => (Arr::get($parameters, 'amount') * 100),
                        'currency' => strtolower(Arr::get($parameters, 'currency')),
                        'product_data' => [
                            'name' => $parameters['reference'],
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'customer_email' => Arr::get($parameters, 'email'),
            'payment_method_types' => $this->getChannels(),
            'metadata' => Arr::get($parameters, 'meta'),
            'mode' => 'payment',
            'success_url' => $parameters['callback_url'],
            'cancel_url' => $parameters['callback_url'],
        ];
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
            'customer' => $customer,
            'limit' => 100,
            'created' => [
                'gte' => $from,
                'lte' => $to,
            ],
        ]);

        $response = $this->request('GET', 'v1/charges', $payload);

        return [
            'meta' => [
                'total' => count($response['data']),
                'page' => null,
                'page_count' => null,
            ],
            'data' => collect($response['data'])
                ->map(fn ($transaction) => $this->transactionDTO($transaction))
                ->toArray(),
        ];
    }

    public function transactionDTO(array $transaction): TransactionData
    {
        $email = Arr::get($transaction, 'billing_details.email')
            ?? Arr::get($transaction, 'charges.data.0.billing_details.email');

        $reference = Arr::get($transaction, 'reference')
            ?? Arr::get($transaction, 'payment_intent')
            ?? Arr::get($transaction, 'id');

        $date = Arr::get($transaction, 'created');

        return new TransactionData(
            email: $email,
            meta: $transaction['metadata'],
            amount: ($transaction['amount'] / 100),
            currency: $transaction['currency'],
            reference: $reference,
            provider: $this->provider,
            status: $transaction['status'],
            date: Date::createFromTimestamp($date),
        );
    }
}
