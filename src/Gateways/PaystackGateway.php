<?php

namespace Stephenjude\PaymentGateway\Gateways;

use Illuminate\Support\Facades\Http;
use Stephenjude\PaymentGateway\Exceptions\PaymentInitializationException;
use Stephenjude\PaymentGateway\Exceptions\PaymentVerificationException;

class PaystackGateway extends AbstractGateway
{
    public function __construct()
    {
        $this->baseUrl = 'https://api.paystack.co/';
        $this->secretKey = config('payment-gateways.providers.paystack.secret');
        $this->publicKey = config('payment-gateways.providers.paystack.public');
    }

    public function initialize(array $params): mixed
    {
        $response = $this->http('post', 'transaction/initialize', $params);

        throw_if($response->failed(), new PaymentInitializationException());

        return $response->json('data');
    }

    public function verify(string $reference): mixed
    {
        $response = $this->http('get', 'transaction/verify/'.$reference);

        throw_if($response->failed(), new PaymentVerificationException());

        return $response->json('data');
    }
}
