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

class CompletePaymentController extends Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function __invoke(Request $request, string $provider, string $reference)
    {
        try {
            $paymentProvider = PaymentGateway::make($provider);

            $sessionData = $paymentProvider->getInitializedPayment($reference);

            abort_if(is_null($sessionData), Response::HTTP_BAD_REQUEST, 'Payment session has expired');

            /**
             * Session Reference becomes the Payment Reference if the payment session data does
             * not contain the reference for the payment OR if the provider doesn't return
             * any reference for the transactions via the callback url.
             */
            $paymentReference = $request->get('transaction_id')
                ?? $sessionData?->paymentReference
                ?? $sessionData?->sessionReference;

            $payment = $paymentProvider->confirmPayment($paymentReference, $sessionData->closure);

            return view('payment-gateways::status', [
                'payment' => $payment,
                'successful' => $payment->isSuccessful(),
            ]);
        } catch (Exception $exception) {
            logger($exception->getMessage(), $exception->getTrace());

            abort(Response::HTTP_BAD_REQUEST, "Payment Error: ".$exception->getMessage());
        }
    }
}
