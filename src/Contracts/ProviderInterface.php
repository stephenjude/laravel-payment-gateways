<?php

namespace Stephenjude\PaymentGateway\Contracts;

use Laravel\SerializableClosure\SerializableClosure;
use Stephenjude\PaymentGateway\DataObjects\SessionData;
use Stephenjude\PaymentGateway\DataObjects\TransactionData;

interface ProviderInterface
{
    public function initializeCheckout(array $parameters = []): SessionData;

    public function getCheckout(string $sessionReference): SessionData|null;

    public function destroyCheckout(string $sessionReference): void;

    public function setChannels(array $channels): self;

    public function getChannels(): array|null;

    public function confirmTransaction(
        string $reference,
        SerializableClosure|null $closure,
    ): TransactionData|null;

    public function findTransaction(string $reference): TransactionData;

    public function listTransactions(
        ?string $from = null,
        ?string $to = null,
        ?string $page = null,
        ?string $status = null,
        ?string $reference = null,
        ?string $amount = null,
        ?string $customer = null, // this could be email or id
    ): array|null;

    public function transactionDTO(array $transaction): TransactionData;
}
