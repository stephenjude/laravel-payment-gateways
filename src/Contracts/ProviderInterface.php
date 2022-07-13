<?php

namespace Stephenjude\PaymentGateway\Contracts;

use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;

interface ProviderInterface
{
    public function initializePayment(array $parameters = []): SessionDataObject;

    public function getInitializedPayment(string $sessionReference): SessionDataObject|null;

    public function deinitializePayment(string $sessionReference): void;

    public function setChannels(array $channels): self;

    public function getChannels(): array|null;

    public function confirmPayment(
        string $paymentReference,
        SerializableClosure|null $closure,
    ): PaymentDataObject|null;
}
