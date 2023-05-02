<?php

namespace Stephenjude\PaymentGateway\DataObjects;

use Spatie\LaravelData\Data;

class PaymentData extends Data
{
    public function __construct(
        public string $email,
        public array|null $meta,
        public string $amount,
        public string $currency,
        public string $reference,
        public string $provider,
        public string $status,
        public string|null $date,
    ) {
    }

    public function isSuccessful(): bool
    {
        // Paystack: success; Flutterwave: successful; Stripe: succeeded;
        return match (strtolower($this->status)) {
            'success', 'succeeded', 'successful','paid' => true,
            default => false
        };
    }

    public function isProcessing(): bool
    {
        // Stripe: processing;
        return match (strtolower($this->status)) {
            'processing', 'pending' => true,
            default => false
        };
    }

    public function failed(): bool
    {
        // Paystack: failed; Flutterwave: failed; Stripe: failed;
        return match (strtolower($this->status)) {
            'failed','expired' => true,
            default => false
        };
    }
}
