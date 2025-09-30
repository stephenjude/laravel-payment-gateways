<?php

namespace Stephenjude\PaymentGateway;

use Stephenjude\PaymentGateway\Contracts\ProviderInterface;
use Stephenjude\PaymentGateway\Enums\Provider;

class PaymentGateway
{
    public function __call(string $provider, array $arguments)
    {
        return static::make($provider);
    }

    /**
     * Static method for backward compatibility
     * Uses the configured factory class directly
     */
    public static function make(string $provider): ProviderInterface
    {
        return Provider::integration($provider);
    }
}
