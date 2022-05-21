<?php

namespace Stephenjude\PaymentGateway\Commands;

use Illuminate\Console\Command;

class PaymentGatewayCommand extends Command
{
    public $signature = 'laravel-payment-gateways';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
