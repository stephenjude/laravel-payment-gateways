<?php

namespace Stephenjude\PaymentGateway\DataObjects;

use Spatie\LaravelData\Data;

class SessionDataObject extends Data
{
    public function __construct(
        public string $email,
        public ?array $meta,
        public string $amount,
        public string $currency,
        public ?array $channels,
        public string $provider,
        public string $reference,
        public string $checkoutUrl,
        public string $callbackUrl,
        public int $expires,
    ) {
    }
}
