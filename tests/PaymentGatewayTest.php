<?php

use Stephenjude\PaymentGateway\PaymentGateway;
use Stephenjude\PaymentGateway\Providers\PaystackProvider;

it('can make paystack provider', function () {
    expect(PaymentGateway::make('paystack'))->toBeInstanceOf(PaystackProvider::class);
});
