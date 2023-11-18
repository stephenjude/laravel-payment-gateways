<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\DataObjects\TransactionData;

class PawapayProvider extends AbstractProvider
{
    public string $provider = 'pawapay';

    public function initializeCheckout(array $parameters = []): SessionData
    {
        $parameters['reference'] = Str::uuid();

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        /*
        * Convert and round decimals to the nearest integer because Paystack does not support decimal values.
        */
        $amount = (int)ceil(Arr::get($parameters, 'amount'));

        $pawapay = $this->request(
            method: 'POST',
            path: 'v1/widget/sessions',
            payload: [
                'depositId' => Arr::get($parameters, 'reference'),
                'amount' => "$amount", // Pawapay accepts amount as string
                "country" => Arr::get($parameters, 'country'),
                'msisdn' => Arr::get($parameters, 'mobile_number'),
                "statementDescription" => Arr::get($parameters, 'meta.description'),
                "reason" => Arr::get($parameters, 'meta.reason'),
                'returnUrl' => $parameters['callback_url'] ?? route(config('payment-gateways.routes.callback.name'), [
                        'reference' => $parameters['reference'],
                        'provider' => $this->provider,
                    ]),
            ]
        );

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], fn() => new SessionData(
            provider: $this->provider,
            sessionReference: $parameters['reference'],
            paymentReference: null,
            checkoutSecret: null,
            checkoutUrl: $pawapay['redirectUrl'],
            expires: $parameters['expires'],
            closure: Arr::has($parameters, 'closure') ? new SerializableClosure($parameters['closure']) : null,
        ));
    }

    public function findTransaction(string $reference): TransactionData
    {
        $transaction = $this->request('GET', "deposits/$reference");

        return $this->transactionDTO($transaction[0]);
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
        throw new Exception("This provider [$this->provider] does not support list transactions");
    }

    public function transactionDTO(array $transaction): TransactionData
    {
        $date = Arr::get($transaction, 'respondedByPayer') ?? Arr::get($transaction, 'created');

        return new TransactionData(
            email: null,
            meta: [
                'type' => $transaction['payer']['type'],
                'address' => $transaction['payer']['address']['value'],
            ],
            amount: (int)$transaction['depositedAmount'],
            currency: $transaction['currency'],
            reference: $transaction['depositId'],
            provider: $this->provider,
            status: $transaction['status'],
            date: Carbon::parse($date)->toDateTimeString(),
        );
    }
}
