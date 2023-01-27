<?php

namespace Stephenjude\PaymentGateway\Exceptions;

use Throwable;

class InitializationException extends HttpException
{
    public function __construct($gatewayMessage = "", $code = 0, Throwable $previous = null)
    {
        $message = 'payment initialization failed';

        if (!empty($gatewayMessage)) {
            $message .= ": $gatewayMessage";
        }

        parent::__construct($message, $code, $previous);
    }
}
