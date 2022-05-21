<?php

namespace Stephenjude\PaymentGateway\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Stephenjude\PaymentGateway\PaymentGateway
 */
class PaymentGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-payment-gateways';
    }
}
