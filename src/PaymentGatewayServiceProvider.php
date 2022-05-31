<?php

namespace Stephenjude\PaymentGateway;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PaymentGatewayServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-payment-gateways')
            ->hasConfigFile('payment-gateways')
            ->hasViews()
            ->hasRoute('web');
    }
}
