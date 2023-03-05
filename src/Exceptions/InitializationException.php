<?php

namespace Stephenjude\PaymentGateway\Exceptions;

class InitializationException extends HttpException
{
    protected $message = 'payment initialization failed';
}
