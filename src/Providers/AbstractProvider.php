<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Facades\Cache;
use Stephenjude\PaymentGateway\Contracts\ProviderInterface;

abstract class AbstractProvider implements ProviderInterface
{
    public string $provider;

    public array $channels;

    public function getInitializedSession(string $sessionReference): array|null
    {
        return Cache::get($sessionReference);
    }

    public function deinitializeSession(string $sessionReference): void
    {
        Cache::forget($sessionReference);
    }

    public function setPaymentReference(string $sessionReference, string $paymentReference): void
    {
        $key = config('payment-gateway.cache.payment.key').$sessionReference;

        $expires = config('payment-gateway.cache.payment.expries');

        Cache::remember($key, $expires, fn() => $paymentReference);
    }

    public function getPaymentReference(string $sessionReference): string|null
    {
        $key = config('payment-gateway.cache.payment.key').$sessionReference;

        return Cache::get($key);
    }
}
