<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Facades\Cache;
use Stephenjude\PaymentGateway\Contracts\ProviderInterface;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;

abstract class AbstractProvider implements ProviderInterface
{
    public string $provider;

    public array $channels;

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
}
