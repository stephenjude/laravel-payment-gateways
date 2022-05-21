<?php

namespace Stephenjude\PaymentGateway\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Stephenjude\PaymentGateway\Providers\AbstractProvider;
use Symfony\Component\HttpFoundation\Response;

class PaymentGatewayController extends Controller
{
    public function index(Request $request, string $provider, string $reference)
    {
        if (!$request->hasValidSignature()) {
            abort(Response::HTTP_BAD_REQUEST, 'Expired/Invalid payment URL!');
        }

        $provider = app(config("payment-gateways.$provider"));

        $data = $provider->getInitializedSession($reference);

        if (is_null($data)) {
            abort(Response::HTTP_BAD_REQUEST, 'Invalid payment session!');
        }

        return view("payment-gateways::checkout.$provider", compact('data'));
    }

    public function store(Request $request, string $provider, string $reference)
    {
        /**
         * Session reference becomes the payment reference
         * if provider doesn't supply any reference
         * for the transactions.
         */
        $paymentReference = $provider === config('payment-gateways.providers.flutterwave')
            ? $request->input('id') ?? $request->input('transaction_id')
            : $reference;

        $provider = app(config("payment-gateways.providers.$provider"));

        $provider->deinitializeSession($reference);

        $provider->setPaymentReference($reference, $paymentReference);

        $payment = $provider->verifyPaymentReference($paymentReference);

        return view('payment-gateways::status', ['successful' => $payment->successful,]);
    }
}
