<?php

namespace Stephenjude\PaymentGateway\DataObjects;

use Spatie\LaravelData\Data;

class SessionDataObject extends Data
{
    public function __construct(
        public string $email,
        public string $amount,
        public string $currency,
        public string $provider,
        public string $reference,
        public array|null $channels,
        public array|null $meta,
        public string|null $checkoutSecret,
        public string $checkoutUrl,
        public string $callbackUrl,
        public int $expires,
    ) {
    }
}
