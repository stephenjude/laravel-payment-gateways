<?php

namespace Stephenjude\PaymentGateway\Exceptions;

class VerificationException extends HttpException
{
    protected $message = 'payment verification failed';
}
