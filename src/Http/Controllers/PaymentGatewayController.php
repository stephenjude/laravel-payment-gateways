<?php

namespace Stephenjude\PaymentGateway\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stephenjude\PaymentGateway\PaymentGateway;
use Symfony\Component\HttpFoundation\Response;

class PaymentGatewayController extends Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function index(Request $request, string $provider, string $reference)
    {
        if (! $request->hasValidSignature()) {
            abort(Response::HTTP_FORBIDDEN, 'Expired/Invalid payment!');
        }

        $paymentSession = PaymentGateway::make($provider)?->getInitializedPayment($reference);

        if (is_null($paymentSession)) {
            abort(Response::HTTP_FORBIDDEN, 'Invalid payment session!');
        }

        return view("payment-gateways::checkout.$provider", [
            'data' => $paymentSession,
        ]);
    }

    public function store(Request $request, string $provider, string $reference)
    {
        try {
            $paymentProvider = PaymentGateway::make($provider);

            $sessionData = $paymentProvider->getInitializedPayment($reference);

            /**
             * Session Reference becomes the Payment Reference if the payment session data does
             * not contain the reference for the payment OR if the provider doesn't return
             * any reference for the transactions via the callback url.
             */
            $paymentReference = $request->get('transaction_id')
                ?? $sessionData?->paymentReference
                ?? $sessionData->sessionReference;

            $paymentProvider->setReference($reference, $paymentReference);

            $payment = $paymentProvider->confirmPayment($paymentReference, $sessionData->closure);

            $paymentProvider->deinitializePayment($reference);

            return view('payment-gateways::status', ['successful' => $payment->successful,]);
        } catch (Exception $exception) {
            logger($exception->getMessage(), $exception->getTrace());

            abort(Response::HTTP_BAD_REQUEST, "Payment transaction error: ".$exception->getMessage());
        }
    }
}
