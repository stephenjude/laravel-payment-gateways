<?php

namespace Stephenjude\PaymentGateway\Exceptions;

use Exception;

class PaymentInitializationException extends Exception
{
    protected $message = "payment initialization failed";
}
