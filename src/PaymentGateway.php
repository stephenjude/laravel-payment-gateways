<?php

namespace Stephenjude\PaymentGateway;

use ReflectionClass;
use Stephenjude\PaymentGateway\Providers\AbstractProvider;
use Stephenjude\PaymentGateway\Providers\PaystackProvider;

/**
 * @method PaystackProvider paystack()
 */
class PaymentGateway
{
    public function __call(string $provider, array $arguments)
    {
        return static::make($provider);
    }

    public static function make(string $proivder): AbstractProvider
    {
        $class = "\\Stephenjude\\PaymentGateway\\Providers\\".ucwords($proivder)."Provider";

        if (class_exists($class) && !(new ReflectionClass($class))->isAbstract()) {
            return new $class();
        }

        throw new BadMethodCallException("Undefined provider [$proivder] called.");
    }
}
