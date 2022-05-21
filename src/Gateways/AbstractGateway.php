<?php

namespace Stephenjude\PaymentGateway\Gateways;

use Illuminate\Support\Facades\Http;
use Stephenjude\PaymentGateway\Contracts\GatewayInterface;

abstract class AbstractGateway implements GatewayInterface
{
    public string $baseUrl;

    public string $secretKey;

    public string $publicKey;

    public function http($method, $path, $param = []): \Illuminate\Http\Client\Response
    {
        $path = $this->baseUrl.$path;

        $http = Http::withToken($this->secretKey)
            ->contentType('application/json')
            ->acceptJson();

        return match (strtolower($method)) {
            'post' => $http->post($path, $param),
            'get' => $http->get($path, $param),
        };
    }
}
