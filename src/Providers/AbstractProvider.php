<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Stephenjude\PaymentGateway\Contracts\ProviderInterface;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;

abstract class AbstractProvider implements ProviderInterface
{
    public string $baseUrl;

    public string $secretKey;

    public string $publicKey;

    public string $provider;

    public array $channels;

    public function __construct()
    {
        $this->baseUrl = config("payment-gateways.providers.$this->provider.base_url");
        $this->secretKey = config("payment-gateways.providers.$this->provider.secret");
        $this->publicKey = config("payment-gateways.providers.$this->provider.public");
    }

    public function setChannels(array $channels): self
    {
        $this->channels = $channels;

        return $this;
    }

    public function getChannels(): array|null
    {
        return $this->channels ?? config("payment-gateways.providers.$this->provider.channels");
    }

    public function getInitializedSession(string $sessionReference): SessionDataObject|null
    {
        $sessionCacheKey = config('payment-gateways.cache.session.key').$sessionReference;

        return Cache::get($sessionCacheKey);
    }

    public function deinitializeSession(string $sessionReference): void
    {
        $sessionCacheKey = config('payment-gateways.cache.session.key').$sessionReference;

        Cache::forget($sessionCacheKey);
    }

    public function setReference(string $sessionReference, string $paymentReference): void
    {
        $key = config('payment-gateway.cache.payment.key').$sessionReference;

        $expires = config('payment-gateway.cache.payment.expries');

        Cache::remember($key, $expires, fn() => $paymentReference);
    }

    public function getReference(string $sessionReference): string|null
    {
        $key = config('payment-gateway.cache.payment.key').$sessionReference;

        return Cache::get($key);
    }

    public function http(): PendingRequest
    {
        return Http::withToken($this->secretKey)->acceptJson();
    }

    public abstract function initializeProvider(array $params): mixed;

    public abstract function verifyProvider(string $paymentReference): mixed;
}
