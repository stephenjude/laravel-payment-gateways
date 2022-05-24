<?php

namespace Stephenjude\PaymentGateway\DataObjects;

use Spatie\LaravelData\Data;

class PaymentDataObject extends Data
{
    public function __construct(
        public string $email,
        public array|null $meta,
        public string $amount,
        public string $currency,
        public string $reference,
        public string $provider,
        public bool $successful,
        public string|null $date,
    ) {
    }
}
