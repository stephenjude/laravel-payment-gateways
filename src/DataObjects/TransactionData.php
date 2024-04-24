<?php

namespace Stephenjude\PaymentGateway\DataObjects;

use Spatie\LaravelData\Data;

class TransactionData extends Data
{
    public function __construct(
        public ?string $email,
        public mixed $meta,
        public int|float $amount,
        public string $currency,
        public string $reference,
        public string $provider,
        public string $status,
        public string|null $date,
    ) {
    }

    public function isSuccessful(): bool
    {
        // Paystack: success; Flutterwave: successful; Stripe: succeeded; Startbutton: verified;
        /**
         * @description Paystack: success;
         * Flutterwave: successful;
         * Stripe: succeeded;
         * Startbutton: verified;
         */
        return match (strtolower($this->status)) {
            'success', 'succeeded', 'successful', 'paid', 'approved', 'completed', 'verified' => true,
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
        /**
         * @description Paystack: failed; Flutterwave: failed; Stripe: failed;
         */
        return match (strtolower($this->status)) {
            'failed', 'expired' => true,
            default => false
        };
    }
}
