<?php

use Stephenjude\PaymentGateway\PaymentGateway;
use Stephenjude\PaymentGateway\Providers\FlutterwaveProvider;
use Stephenjude\PaymentGateway\Providers\PaypalProvider;
use Stephenjude\PaymentGateway\Providers\PaystackProvider;
use Stephenjude\PaymentGateway\Providers\StripeProvider;

it('can make paystack provider', function () {
    expect(PaymentGateway::make('paystack'))->toBeInstanceOf(PaystackProvider::class);
});

it('can make futterwave provider', function () {
    expect(PaymentGateway::make('flutterwave'))->toBeInstanceOf(FlutterwaveProvider::class);
});

it('can make stripe provider', function () {
    expect(PaymentGateway::make('stripe'))->toBeInstanceOf(StripeProvider::class);
});

it('can make paypal provider', function () {
    expect(PaymentGateway::make('paypal'))->toBeInstanceOf(PaypalProvider::class);
});
