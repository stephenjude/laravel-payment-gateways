<?php

namespace Stephenjude\PaymentGateway\Contracts;

use Illuminate\Database\Eloquent\Model;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;

interface ProviderInterface
{
    public function initializeSession(string $currency, int|float $amount, string $email, array $meta = []): string;

    public function getInitializedSession(string $sessionReference): array|null;

    public function deinitializeSession(string $sessionReference): void;

    public function setPaymentReference(string $sessionReference, string $paymentReference): void;

    public function getPaymentReference(string $sessionReference): string|null;

    public function verifyPaymentReference(string $paymentReference): PaymentDataObject|null;
}
