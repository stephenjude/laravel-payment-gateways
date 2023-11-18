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

    public static function make(string $proivder): ProviderInterface
    {
        return Provider::integration($proivder);
    }
}
