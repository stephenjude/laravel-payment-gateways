# Laravel Payment Gateways (for APIs)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stephenjude/laravel-payment-gateways.svg?style=flat-square)](https://packagist.org/packages/stephenjude/laravel-payment-gateways)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/stephenjude/laravel-payment-gateways/run-tests?label=tests)](https://github.com/stephenjude/laravel-payment-gateways/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/stephenjude/laravel-payment-gateways/Check%20&%20fix%20styling?label=code%20style)](https://github.com/stephenjude/laravel-payment-gateways/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/stephenjude/laravel-payment-gateways.svg?style=flat-square)](https://packagist.org/packages/stephenjude/laravel-payment-gateways)

A simple Laravel implementation for all payment providers. This package supports 
Paystack, Flutterwave, Klasha, and Stripe.

## Installation

You can install the package via composer:

```bash
composer require stephenjude/laravel-payment-gateways
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="payment-gateways-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="payment-gateways-views"
```

## Usage
This package currently supports `paystack`, `flutterwave`, `klasha` and `stripe`.

### How to initialize a payment session

```php
use Stephenjude\PaymentGateway\PaymentGateway;
use Stephenjude\PaymentGateway\DataObjects\PaymentData;

$provider = PaymentGateway::make('paystack')
            ->setChannels(['bank_transfer','card'])
            ->initializePayment([
                'currency' => 'NGN', // required
                'amount' => 100, // required
                'email' => 'customer@email.com', // required
                'meta' => [ 'name' => 'Stephen Jude', 'phone' => '081xxxxxxxxx'],
                'closure' => function (PaymentData $payment){
                    /* 
                     * Payment verification happens immediately after the customer makes payment. 
                     * The payment data gotten from the verification will be injected into this closure.
                     */
                    logger('payment details', [
                       'currency' => $payment->currency, 
                       'amount' => $payment->amount, 
                       'status' => $payment->status,
                       'reference' => $payment->reference,   
                       'provider' => $payment->provider,   
                       'date' => $payment->date,                   
                    ]);
                },
            ]);

$provider->provider;
$provider->checkoutUrl;
$provider->expires;
```

### Paystack Setup
```
PAYSTACK_PUBLIC=
PAYSTACK_SECRET=
```
### Flutterwave Setup
```
FLUTTERWAVE_PUBLIC=
FLUTTERWAVE_SECRET=
```

### Klasha Setup
```
KLASHA_PUBLIC=
KLASHA_SECRET=
```

### Stripe Setup
```
STRIPE_PUBLIC=
STRIPE_SECRET=
```
## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [stephenjude](https://github.com/stephenjude)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
