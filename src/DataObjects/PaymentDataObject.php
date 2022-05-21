<?php

namespace Stephenjude\PaymentGateway\DataObjects;

use Spatie\LaravelData\Data;

class PaymentDataObject extends Data
{
    public function __construct(
        public string $amount,
        public string $currency,
        public string $reference,
        public string $provider,
        public bool $successful,
        public string $date,
        public string $email,
        public ?array $meta,
    ) {
    }
}
