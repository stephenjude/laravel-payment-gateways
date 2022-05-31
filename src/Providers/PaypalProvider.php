<?php

namespace Stephenjude\PaymentGateway\Providers;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Stephenjude\PaymentGateway\DataObjects\PaymentDataObject;
use Stephenjude\PaymentGateway\DataObjects\SessionDataObject;
use Stephenjude\PaymentGateway\Exceptions\InitializationException;
use Stephenjude\PaymentGateway\Exceptions\VerificationException;

class PaypalProvider extends AbstractProvider
{
    public string $provider = 'paypal';

    public function initializeSession(
        string $currency,
        float|int $amount,
        string $email,
        array $meta = []
    ): SessionDataObject {
        $intent = $this->initializeProvider([
            "amount" => ["total" => $amount],
            "payee" => ["email" => $email],
            "metadata" => $meta,
        ]);

        $reference = $intent['client_token'];

        $expires = config('payment-gateways.cache.session.expires');

        $sessionCacheKey = config('payment-gateways.cache.session.key').$reference;

        $routeParameters = ['reference' => $reference, 'provider' => $this->provider,];

        return Cache::remember($sessionCacheKey, $expires, fn () => new SessionDataObject(
            email: $email,
            amount: $amount,
            currency: $currency,
            provider: $this->provider,
            reference: $reference,
            channels: $this->getChannels(),
            meta: $meta,
            checkoutSecret: $intent['client_token'],
            checkoutUrl: URL::signedRoute(config('payment-gateways.routes.checkout.name'), $routeParameters, $expires),
            callbackUrl: route(config('payment-gateways.routes.callback.name'), $routeParameters),
            expires: $expires
        ));
    }

    public function verifyReference(string $paymentReference): PaymentDataObject|null
    {
        $payment = $this->verifyProvider($paymentReference);

        return new PaymentDataObject(
            email: $payment['payer']['email_address'],
            meta: $payment['metadata'],
            amount: $payment['gross_total_amount']['value'],
            currency: $payment['gross_total_amount']['currency'],
            reference: $paymentReference,
            provider: $this->provider,
            successful: $payment['status'] === 'COMPLETED',
            date: Carbon::parse($payment['create_time'])->toDateTimeString(),
        );
    }

    public function createAccessToken(): string
    {
        return Cache::remember('paypal_access_token', now()->addHours(6), function () {
            $response = Http::asForm()
                ->withHeaders(['Accept-Language' => 'en_US'])
                ->withBasicAuth($this->publicKey, $this->secretKey)
                ->post("$this->baseUrl/oauth2/token", ['grant_type' => 'client_credentials',]);

            $this->logResponseIfEnabledDebugMode($this->provider, $response);

            throw_if($response->failed(), new Exception($response->reason()));

            return $response->json('access_token');
        });
    }

    public function http(): PendingRequest
    {
        return Http::withToken($this->createAccessToken())
            ->contentType('application/json')
            ->withHeaders(['Accept-Language' => 'en_US'])
            ->asForm();
    }

    public function initializeProvider(array $params = []): mixed
    {
        $response = $this->http()->post("$this->baseUrl/identity/generate-token");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if($response->failed(), new InitializationException());

        return $response->json();
    }

    public function verifyProvider(string $paymentReference): mixed
    {
        $response = $this->http()->get("$this->baseUrl/checkout/orders/$paymentReference");

        $this->logResponseIfEnabledDebugMode($this->provider, $response);

        throw_if($response->failed(), new VerificationException());

        return $response->json();
    }
}
