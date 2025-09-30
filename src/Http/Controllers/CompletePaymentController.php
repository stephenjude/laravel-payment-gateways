<?php

namespace Stephenjude\PaymentGateway\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stephenjude\PaymentGateway\Contracts\ProviderInterface;
use Stephenjude\PaymentGateway\PaymentGateway;

class CompletePaymentController extends Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function __invoke(Request $request, string $provider, string $reference)
    {
        try {
            $gatewayFactory = config('payment-gateways.factory');

            /** @var ProviderInterface $gatewayFactory */
            $paymentProvider = $gatewayFactory::make($provider);

            $sessionData = $paymentProvider->getCheckout($reference);

            if (is_null($sessionData)) {
                return redirect()->route(
                    route: config('payment-gateways.routes.error.name'),
                    parameters: ['message' => 'Your payment session has expired.']
                );
            }

            /**
             * Session Reference becomes the Payment Reference if the payment session data does
             * not contain the reference for the payment OR if the provider doesn't return
             * any reference for the transactions via the callback url.
             */
            $paymentReference = $request->get('transaction_id')
                ?? $sessionData->paymentReference
                ?? $sessionData->sessionReference;

            $payment = $paymentProvider->confirmTransaction($paymentReference, $sessionData->closure);

            if ($customSuccessRoute = config('payment-gateways.routes.custom.success.name')) {
                return redirect()->route(
                    route: $customSuccessRoute,
                    parameters: ['reference' => $paymentReference]
                );
            }

            return view('payment-gateways::status', [
                'payment' => $payment,
                'successful' => $payment->isSuccessful(),
            ]);
        } catch (Exception $exception) {
            logger($exception->getMessage(), $exception->getTrace());

            if ($customFailedRoute = config('payment-gateways.routes.custom.failed.name')) {
                return redirect()->route($customFailedRoute, [
                    'reference' => $paymentReference,
                    'message' => $exception->getMessage(),
                ]);
            }

            return redirect()->route(config('payment-gateways.routes.error.name'));
        }
    }
}
