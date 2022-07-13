<?php

namespace Stephenjude\PaymentGateway\DataObjects;

use Spatie\LaravelData\Data;

class PaymentDataObject extends Data
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
        return match ($this->status) {
            'success', 'succeeded', 'successful' => true,
            default => false
        };
    }

    public function isProcessing(): bool
    {
        // Stripe: processing;
        return match ($this->status) {
            'processing' => true,
            default => false
        };
    }

    public function failed(): bool
    {
        // Paystack: failed; Flutterwave: failed; Stripe: failed;
        return match ($this->status) {
            'failed' => true,
            default => false
        };
    }
}
