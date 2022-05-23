<?php

namespace Stephenjude\PaymentGateway;

use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Stephenjude\PaymentGateway\Commands\PaymentGatewayCommand;
use Stephenjude\PaymentGateway\Http\Controllers\PaymentGatewayController;

class PaymentGatewayServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-payment-gateways')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasMigration('create_laravel-payment-gateways_table');
    }

    public function packageBooted()
    {
    }
}
