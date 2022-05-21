<?php

namespace Stephenjude\PaymentGateway;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Stephenjude\PaymentGateway\Commands\PaymentGatewayCommand;

class PaymentGatewayServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-payment-gateways')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-payment-gateways_table')
            ->hasCommand(PaymentGatewayCommand::class);
    }
}
