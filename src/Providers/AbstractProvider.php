<?php

namespace Stephenjude\PaymentGateway\Providers;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\Contracts\ProviderInterface;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\DataObjects\TransactionData;

abstract class AbstractProvider implements ProviderInterface
{
    public string $baseUrl;

    public string $secretKey;

    public string $publicKey;

    public string $provider;

    public array|null $channels;

    public function __construct()
    {
        $this->baseUrl = config("payment-gateways.providers.$this->provider.base_url");
        $this->secretKey = config("payment-gateways.providers.$this->provider.secret");
        $this->publicKey = config("payment-gateways.providers.$this->provider.public");
    }

    public function setChannels(array|null $channels): self
    {
        $this->channels = $channels;

        return $this;
    }

    public function getChannels(): array|null
    {
        return $this->channels ?? config("payment-gateways.providers.$this->provider.channels");
    }

    public function request($method, $path, $payload = []): array
    {
        $path = $this->baseUrl.$path;

        $http = Http::withToken($this->secretKey)
            ->withOptions(['force_ip_resolve' => 'v4'])
            ->contentType('application/json')
            ->acceptJson();

        $response = match (strtolower($method)) {
            'post' => $http->post($path, $payload),
            default => $http->get($path, $payload),
        };

        $this->logResponse($this->provider, $response);

        if ($response->failed()) {
            throw new Exception($response->reason());
        }

        return $response->json();
    }

    public function getCheckout(string $sessionReference): SessionData|null
    {
        $sessionCacheKey = config('payment-gateways.cache.session.key').$sessionReference;

        return Cache::get($sessionCacheKey);
    }

    public function destroyCheckout(string $sessionReference): void
    {
        $sessionCacheKey = config('payment-gateways.cache.session.key').$sessionReference;

        Cache::forget($sessionCacheKey);
    }

    public function setReference(string $sessionReference, string $paymentReference): void
    {
        $key = config('payment-gateway.cache.payment.key').$sessionReference;

        $expires = config('payment-gateway.cache.payment.expries');

        Cache::remember($key, $expires, fn () => $paymentReference);
    }

    public function getReference(string $sessionReference): string|null
    {
        $key = config('payment-gateway.cache.payment.key').$sessionReference;

        return Cache::get($key);
    }

    public function confirmTransaction(string $reference, ?SerializableClosure $closure): TransactionData|null
    {
        $transaction = $this->findTransaction($reference);

        if ($closure = $closure?->getClosure()) {
            $closure($transaction);
        }

        return $transaction;
    }

    protected function logResponse(string $provider, Response $response): void
    {
        if (! config('payment-gateways.debug_mode')) {
            return;
        }

        logger("$provider Response: ", [
            'STATUS' => $response->status(),
            'REASON' => $response->reason(),
            'JSON' => $response->json(),
            'ERROR' => $response->reason(),
            'PSR' => $response->toPsrResponse(),
        ]);
    }
}
