<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\DataObjects\TransactionData;

class SeerbitProvider extends AbstractProvider
{
    public string $provider = 'seerbit';

    public function __construct()
    {
        parent::__construct();

        $this->secretKey = $this->getToken();
    }

    private function getToken()
    {
        $payload = [
            'key' => config('payment-gateways.providers.seerbit.secret').'.'.$this->publicKey,
        ];

        return Http::acceptJson()
            ->post($this->baseUrl.'api/v2/encrypt/keys', $payload)
            ->json('data.EncryptedSecKey.encryptedKey');
    }

    public function initializeCheckout(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'SEBT_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        $seerbit = $this->request(
            method: 'POST',
            path: 'api/v2/payments',
            payload: [
                'publicKey' => $this->publicKey,
                'email' => Arr::get($parameters, 'email'),
                'amount' => Arr::get($parameters, 'amount'),
                'currency' => Arr::get($parameters, 'currency'),
                'country' => Arr::get($parameters, 'country_code', 'NG'),
                'paymentReference' => Arr::get($parameters, 'reference'),
                'callbackUrl' => $parameters['callback_url']
                    ?? route(config('payment-gateways.routes.callback.name'), [
                        'reference' => $parameters['reference'],
                        'provider' => $this->provider,
                    ]),
            ],
        );

        return Cache::remember(
            key: $parameters['session_cache_key'],
            ttl: $parameters['expires'],
            callback: fn() => new SessionData(
                provider: $this->provider,
                sessionReference: $parameters['reference'],
                paymentReference: null,
                checkoutSecret: null,
                checkoutUrl: Arr::get($seerbit, 'data.payments.redirectLink'),
                expires: $parameters['expires'],
                closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
            )
        );
    }

    public function findTransaction(string $reference): TransactionData
    {
        $transaction = $this->request('GET', "api/v2/payments/query/$reference");

        if ($transaction['error']) {
            throw new \Exception($transaction['message']);
        }

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
            email: $transaction['customers']['customerEmail'],
            meta: [
                'sourceIP' => $transaction['payments']['sourceIP'],
                'deviceType' => $transaction['payments']['deviceType'],
            ],
            amount: $transaction['payments']['amount'],
            currency: $transaction['payments']['currency'],
            reference: $transaction['reference'],
            provider: $this->provider,
            status: $transaction['payments']['gatewayMessage'],
            date: Carbon::parse($transaction['payments']['transactionProcessedTime'])->toDateTimeString(),
        );
    }
}
