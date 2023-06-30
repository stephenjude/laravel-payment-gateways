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

class MonnifyProvider extends AbstractProvider
{
    public string $provider = 'monnify';

    public function __construct()
    {
        parent::__construct();

        $this->secretKey = $this->getToken();
    }

    public function getToken(): string
    {
        return Http::acceptJson()
            ->withHeaders([
                'Authorization' => 'Basic '.base64_encode("$this->publicKey:$this->secretKey"),
            ])
            ->post($this->baseUrl.'api/v1/auth/login')
            ->json('responseBody.accessToken');
    }

    public function initializeCheckout(array $parameters = []): SessionData
    {
        $parameters['reference'] = 'MNFY_'.Str::random(12);

        $parameters['expires'] = config('payment-gateways.cache.session.expires');

        $parameters['session_cache_key'] = config('payment-gateways.cache.session.key').$parameters['reference'];

        $monnify = $this->request(
            method: 'POST',
            path: 'api/v1/merchant/transactions/init-transaction',
            payload: [
                'customerEmail' => $email = Arr::get($parameters, 'email'),
                'customerName' => Arr::get($parameters, 'meta.name', $email),
                'amount' => Arr::get($parameters, 'amount'),
                'currencyCode' => Arr::get($parameters, 'currency'),
                'contractCode' => config('payment-gateways.providers.monnify.contract_code'),
                'paymentReference' => Arr::get($parameters, 'reference'),
                'paymentMethods' => $this->getChannels(),
                'redirectUrl' => $parameters['callback_url']
                    ?? route(config('payment-gateways.routes.callback.name'), [
                        'reference' => $parameters['reference'],
                        'provider' => $this->provider,
                    ]),
            ]
        );

        return Cache::remember(
            key: $parameters['session_cache_key'],
            ttl: $parameters['expires'],
            callback: fn () => new SessionData(
                provider: $this->provider,
                sessionReference: $parameters['reference'],
                paymentReference: $monnify['responseBody']['transactionReference'],
                checkoutSecret: null,
                checkoutUrl: $monnify['responseBody']['checkoutUrl'],
                expires: $parameters['expires'],
                closure: $parameters['closure'] ? new SerializableClosure($parameters['closure']) : null,
            )
        );
    }

    public function findTransaction(string $reference): TransactionData
    {
        $response = $this->request('GET', "api/v1/transactions/$reference");

        return $this->transactionDTO($response['responseBody']);
    }

    public function listTransactions(
        ?string $from = null,
        ?string $to = null,
        ?string $page = null,
        ?string $status = null,
        ?string $reference = null,
        ?string $amount = null,
        ?string $customer = null,
    ): array {
        $payload = array_filter([
            'page' => $page,
            'paymentReference' => $reference,
            'amount' => $amount,
            'customerEmail' => $customer,
            'paymentStatus' => $status,
            'from' => $from,
            'to' => $to,
        ]);

        $response = $this->request('GET', 'api/v1/transactions/search', $payload);

        return [
            'meta' => [
                'total' => Arr::get($response, 'responseBody.pageable.pageSize'),
                'page' => Arr::get($response, 'responseBody.pageable.pageNumber'),
                'page_count' => Arr::get($response, 'responseBody.pageable.totalPages'),
            ],
            'data' => collect(Arr::get($response, 'responseBody.content'))
                ->map(fn ($transaction) => $this->transactionDTO($transaction))
                ->toArray(),
        ];
    }

    public function transactionDTO(array $transaction): TransactionData
    {
        return new TransactionData(
            email: $transaction['customerDTO']['email'],
            meta: $transaction['metaData'],
            amount: $transaction['amount'],
            currency: $transaction['currencyCode'],
            reference: $transaction['paymentReference'],
            provider: $this->provider,
            status: $transaction['paymentStatus'],
            date: Carbon::parse($transaction['completedOn'])->toDateTimeString(),
        );
    }
}
