# Laravel Payment Gateways (for APIs)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stephenjude/laravel-payment-gateways.svg?style=flat-square)](https://packagist.org/packages/stephenjude/laravel-payment-gateways)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/stephenjude/laravel-payment-gateways/run-tests?label=tests)](https://github.com/stephenjude/laravel-payment-gateways/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/stephenjude/laravel-payment-gateways/Check%20&%20fix%20styling?label=code%20style)](https://github.com/stephenjude/laravel-payment-gateways/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/stephenjude/laravel-payment-gateways.svg?style=flat-square)](https://packagist.org/packages/stephenjude/laravel-payment-gateways)

A simple Laravel API implementation for all payment providers like Paystack, Flutterwave, & Paypal etc.

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

### Initialize Payment Session

```php
use Stephenjude\PaymentGateway\PaymentGateway;

$provider = PaymentGateway::make('paystack');

$session = $provider->initializeSession($currency, $amount, $email, $meta);

$session->checkoutUrl // Returns checkout link.

$session->sessionReference // Returns session reference.
```
### Verify Completed Payment
```php
$paymentReference = $provider->getReference($session->reference);

$payment = $provider->verifyReference($paymentReference)

$payment->successful 

$payment->amount

$payment->currency
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
