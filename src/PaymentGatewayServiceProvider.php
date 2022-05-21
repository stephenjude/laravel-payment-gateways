<?php

namespace Stephenjude\PaymentGateway;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Stephenjude\PaymentGateway\Commands\PaymentGatewayCommand;
use Stephenjude\PaymentGateway\Http\Controllers\PaymentGatewayController;

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
            ->hasMigration('create_laravel-payment-gateways_table');
    }

    public function packageBooted()
    {
        Route::macro('paymentGateways', function () {
            Route::get(config('payment-gateways.routes.checkout.path'), [PaymentGatewayController::class, 'index'])
                ->name(config('payment-gateways.routes.checkout.name'));

            Route::get(config('payment-gateways.routes.callback.path'), [PaymentGatewayController::class, 'store'])
                ->name(config('payment-gateways.routes.callback.name'));
        });

        if (config('support-bubble.mail_to')) {
            $this->registerMailNotificationEventHandler();
        }
    }
}
