<?php

namespace Stephenjude\PaymentGateway\DataObjects;

use Laravel\SerializableClosure\SerializableClosure;
use Spatie\LaravelData\Data;

class SessionData extends Data
{
    public function __construct(
        public string $provider,
        public string $sessionReference,
        public string|null $paymentReference,
        public string|null $checkoutSecret,
        public string $checkoutUrl,
        public int $expires,
        public ?SerializableClosure $closure,
        public array $extra = [],
    ) {
    }
}
