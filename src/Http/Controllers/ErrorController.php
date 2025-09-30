<?php

namespace Stephenjude\PaymentGateway\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ErrorController extends Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function __invoke(Request $request)
    {
        return view('payment-gateways::error', [
            'status' => $request->get('status', 400),
            'title' => $request->get('title', '⚠️ Oops! Something went wrong.'),
            'message' => $request->get(
                'message',
                'Your payment couldn’t be verified or the session may have expired.'
            ),
        ]);
    }
}
