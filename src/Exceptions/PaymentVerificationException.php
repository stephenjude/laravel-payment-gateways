<?php

namespace Stephenjude\PaymentGateway\Exceptions;

use Exception;

class PaymentVerificationException extends Exception
{
    protected $message = "payment verification failed";
}
