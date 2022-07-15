<?php

namespace Stephenjude\PaymentGateway\Contracts;

use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;

interface ProviderInterface
{
    public function initializePayment(array $parameters = []): SessionData;

    public function getInitializedPayment(string $sessionReference): SessionData|null;

    public function deinitializePayment(string $sessionReference): void;

    public function setChannels(array $channels): self;

    public function getChannels(): array|null;

    public function confirmPayment(
        string $paymentReference,
        SerializableClosure|null $closure,
    ): PaymentData|null;
}
