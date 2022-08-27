<?php

namespace Stephenjude\PaymentGateway\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Stephenjude\PaymentGateway\PaymentGatewayServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Stephenjude\\PaymentGateway\\Database\\Factories\\'.class_basename(
                $modelName
            ).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            PaymentGatewayServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        config()->set('payment-gateways.providers.paystack.public', 'testing');
        config()->set('payment-gateways.providers.paystack.secret', 'testing');

        config()->set('payment-gateways.providers.flutterwave.public', 'testing');
        config()->set('payment-gateways.providers.flutterwave.secret', 'testing');

        config()->set('payment-gateways.providers.klasha.public', 'testing');
        config()->set('payment-gateways.providers.klasha.secret', 'testing');

        config()->set('payment-gateways.providers.stripe.public', 'testing');
        config()->set('payment-gateways.providers.stripe.secret', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-payment-gateways_table.php.stub';
        $migration->up();
        */
    }
}
