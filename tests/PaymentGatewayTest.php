<?php

use Stephenjude\PaymentGateway\PaymentGateway;
use Stephenjude\PaymentGateway\Providers\FlutterwaveProvider;
use Stephenjude\PaymentGateway\Providers\KlashaProvider;
use Stephenjude\PaymentGateway\Providers\Pay4MeProvider;
use Stephenjude\PaymentGateway\Providers\PaystackProvider;
use Stephenjude\PaymentGateway\Providers\StripeProvider;

it('can make paystack provider', function () {
    expect(PaymentGateway::make('paystack'))->toBeInstanceOf(PaystackProvider::class);
});

it('can make pay4me provider', function () {
    expect(PaymentGateway::make('pay4me'))->toBeInstanceOf(Pay4MeProvider::class);
});

it('can make futterwave provider', function () {
    expect(PaymentGateway::make('flutterwave'))->toBeInstanceOf(FlutterwaveProvider::class);
});

it('can make klasha provider', function () {
    expect(PaymentGateway::make('klasha'))->toBeInstanceOf(KlashaProvider::class);
});

it('can make stripe provider', function () {
    expect(PaymentGateway::make('stripe'))->toBeInstanceOf(StripeProvider::class);
});
