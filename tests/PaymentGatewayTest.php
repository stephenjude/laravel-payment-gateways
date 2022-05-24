<?php

use Stephenjude\PaymentGateway\PaymentGateway;
use Stephenjude\PaymentGateway\Providers\FlutterwaveProvider;
use Stephenjude\PaymentGateway\Providers\PaystackProvider;

it('can make paystack provider', function () {
    expect(PaymentGateway::make('paystack'))->toBeInstanceOf(PaystackProvider::class);
});

it('can make futterwave provider', function () {
    expect(PaymentGateway::make('flutterwave'))->toBeInstanceOf(FlutterwaveProvider::class);
});
