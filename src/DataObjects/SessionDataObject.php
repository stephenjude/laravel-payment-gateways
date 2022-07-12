<?php

namespace Stephenjude\PaymentGateway\DataObjects;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\LaravelData\Data;

class SessionDataObject extends Data
{
    public function __construct(
        public string $provider,
        public string $sessionReference,
        public string|null $paymentReference = null,
        public string|null $checkoutSecret = null,
        public string $checkoutUrl,
        public int $expires,
        public ?SerializableClosure $closure,
    ) {
    }
}
