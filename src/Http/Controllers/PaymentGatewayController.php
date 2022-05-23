<?php

namespace Stephenjude\PaymentGateway\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stephenjude\PaymentGateway\PaymentGateway;
use Symfony\Component\HttpFoundation\Response;

class PaymentGatewayController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(Request $request, string $provider, string $reference)
    {
        if (!$request->hasValidSignature()) {
            abort(Response::HTTP_FORBIDDEN, 'Expired/Invalid payment!');
        }

        $paymentSession = PaymentGateway::make($provider)?->getInitializedSession($reference);

        if (is_null($paymentSession)) {
            abort(Response::HTTP_FORBIDDEN, 'Invalid payment session!');
        }

        return view("payment-gateways::checkout.$provider", [
            'data' => $paymentSession
        ]);
    }

    public function store(Request $request, string $provider, string $reference)
    {
        /**
         * Session $reference becomes the payment reference if the provider doesn't
         * supply any reference for the transactions.
         */
        $paymentReference = $provider === config('payment-gateways.providers.flutterwave')
            ? $request->input('id') ?? $request->input('transaction_id')
            : $reference;


        $paymentProvider = PaymentGateway::make($provider);

        $paymentProvider->deinitializeSession($reference);

        $paymentProvider->setReference($reference, $paymentReference);

        $payment = $paymentProvider->verifyReference($paymentReference);

        return view('payment-gateways::status', ['successful' => $payment->successful,]);
    }
}
