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
        return view("payment-gateways::error", [
            'status' => $request->get('status', 400),
            'title' => $request->get('title', 'We have a little problem.'),
            'message' => $request->get(
                'message',
                'Something completely went wrong. The issue could be that your payment was not successfully verified or your payment session has expired.'
            ),
        ]);
    }
}
