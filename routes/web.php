<?php

use Illuminate\Support\Facades\Route;
use Stephenjude\PaymentGateway\Http\Controllers\CompletePaymentController;

Route::get(config('payment-gateways.routes.callback.path'), CompletePaymentController::class)
    ->name(config('payment-gateways.routes.callback.name'));
