<?php

namespace Stephenjude\PaymentGateway\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stephenjude\PaymentGateway\Contracts\ProviderInterface;

class CheckoutController extends Controller
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
                    config('payment-gateways.routes.error.name'),
                    ['message' => 'Your payment session has expired.']
                );
            }

            return view("payment-gateways::checkout.$sessionData->provider", [
                'sessionData' => $sessionData->toArray(),
            ]);
        } catch (Exception $exception) {
            logger($exception->getMessage(), $exception->getTrace());

            return redirect()->route(config('payment-gateways.routes.error.name'));
        }
    }
}
