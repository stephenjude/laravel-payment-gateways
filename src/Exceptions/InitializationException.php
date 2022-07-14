<?php

namespace Stephenjude\PaymentGateway\Exceptions;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

class InitializationException extends HttpException
{
    protected $message = "payment initialization failed";
}
