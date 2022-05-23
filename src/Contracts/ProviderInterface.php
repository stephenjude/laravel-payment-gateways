<?php

namespace Stephenjude\PaymentGateway\Contracts;

use Illuminate\Database\Eloquent\Model;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;

interface ProviderInterface
{
    public function initializeSession(
        string $currency,
        int|float $amount,
        string $email,
        array $meta = []
    ): SessionDataObject;

    public function getInitializedSession(string $sessionReference): SessionDataObject|null;

    public function deinitializeSession(string $sessionReference): void;

    public function setChannels(array $channels): self;

    public function getChannels(): array|null;

    public function setReference(string $sessionReference, string $paymentReference): void;

    public function getReference(string $sessionReference): string|null;

    public function verifyReference(string $paymentReference): PaymentDataObject|null;
}
