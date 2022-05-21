<?php

namespace Stephenjude\PaymentGateway\Contracts;

interface GatewayInterface
{
    public function initialize(array $params): mixed;

    public function verify(string $paymentReference): mixed;
}
