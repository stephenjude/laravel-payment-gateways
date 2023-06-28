<?php

namespace Stephenjude\PaymentGateway\Contracts;

use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\PaymentTransactionData;
use Stephenjude\PaymentGateway\DataObjects\SessionData;

interface ProviderInterface
{
    public function initializeTransaction(array $parameters = []): SessionData;

    public function getInitializedPayment(string $sessionReference): SessionData|null;

    public function deinitializePayment(string $sessionReference): void;

    public function setChannels(array $channels): self;

    public function getChannels(): array|null;

    public function confirmTransaction(
        string $reference,
        SerializableClosure|null $closure,
    ): PaymentTransactionData|null;

    public function listTransactions(
        ?string $from = null,
        ?string $to = null,
        ?string $page = null,
        ?string $status = null,
        ?string $reference = null,
        ?string $amount = null,
        ?string $customer = null, // this could be email or id
    ): array|null;

    public function buildTransactionData(array $data): PaymentTransactionData;
}
