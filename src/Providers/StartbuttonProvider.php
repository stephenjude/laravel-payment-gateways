<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\DataObjects\TransactionData;

class StartbuttonProvider extends AbstractProvider
{
    public string $provider = 'startbutton';

    public function initializeCheckout(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'SBTN_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        /*
        * Convert and round decimals to the nearest integer because Paystack does not support decimal values.
        */
        $amount = round(num: (Arr::get($parameters, 'amount') * 100), mode: PHP_ROUND_HALF_ODD);

        $this->secretKey = $this->publicKey;

        $startbutton = $this->request(
            method: 'POST',
            path: 'transaction/initialize',
            payload: array_filter([
                'partner' => Arr::get($parameters, 'partner'),
                'email' => Arr::get($parameters, 'email'),
                'amount' => $amount,
                'currency' => Arr::get($parameters, 'currency'),
                'reference' => Arr::get($parameters, 'reference'),
                'paymentMethods' => $this->getChannels(),
                'metadata' => Arr::get($parameters, 'meta'),
                'redirectUrl' => $parameters['callback_url']
                    ?? route(config('payment-gateways.routes.callback.name'), [
                        'reference' => $parameters['reference'],
                        'provider' => $this->provider,
                    ]),
            ])
        );

        return Cache::remember($parameters['session_cache_key'], $parameters['expires'], fn () => new SessionData(
            provider: $this->provider,
            sessionReference: $parameters['reference'],
            paymentReference: null,
            checkoutSecret: null,
            checkoutUrl: $startbutton['data'],
            expires: $parameters['expires'],
            closure: Arr::get($parameters, 'closure') ? new SerializableClosure($parameters['closure']) : null,
        ));
    }

    public function findTransaction(string $reference): TransactionData
    {
        $transaction = $this->request('GET', "transaction/status/$reference");

        return $this->transactionDTO($transaction['data']['transaction']);
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
        return new TransactionData(
            email: $transaction['customerEmail'],
            meta: null,
            amount: ($transaction['amount'] / 100),
            currency: $transaction['currency'],
            reference: $transaction['userTransactionReference'],
            provider: $this->provider,
            status: $transaction['status'],
            date: Carbon::parse($transaction['createdAt'])->toDateTimeString(),
        );
    }
}
