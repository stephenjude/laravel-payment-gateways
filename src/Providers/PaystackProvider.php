<?php

namespace Stephenjude\PaymentGateway\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;
use Stephenjude\PaymentGateway\Services\PaystackGateway;

class PaystackProvider extends AbstractProvider
{
    public string $provider = 'paystack';

    public array $channels = ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer'];

    public function __construct(private PaystackGateway $gateway)
    {
    }

    public function initializeSession(string $currency, float|int $amount, string $email, array $meta = []): string
    {
        $reference = 'PTK_'.Str::random(10);

        Cache::remember($reference, $this->expires, fn() => [
            'email' => $email,
            'meta' => $meta,
            'amount' => $amount * 100,
            'currency' => $currency,
            'reference' => $reference,
            'provider' => $this->provider,
            'channels' => $this->channels,
            'callback_url' => route(config('payment-gateways.routes.callback.name'), [
                'provider' => $this->provider,
                'reference' => $reference,
            ]),
        ]);

        return $reference;
    }

    public function verifyPaymentReference(string $paymentReference): PaymentDataObject|null
    {
        $payment = $this->gateway->verify($paymentReference);

        return new PaymentDataObject(
            amount: ($payment['amount'] / 100),
            currency: $payment['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            successful: $payment['status'] === 'success',
            date: Carbon::parse($payment['transaction_date'])->toDateTimeString(),
            email: $payment['customer']['email'],
            meta: $payment['metadata'] ?? null,
        );
    }
}
