<?php

use Illuminate\Support\Facades\Route;
use Stephenjude\PaymentGateway\Http\Controllers\PaymentGatewayController;

Route::get(config('payment-gateways.routes.callback.path'), PaymentGatewayController::class)
    ->name(config('payment-gateways.routes.callback.name'));
