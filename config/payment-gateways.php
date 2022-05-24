<?php

return [

    /*
     * Company's support email is displayed to the user when they have completed their payment transactions.
     * When set to null the support email won't be displayed.
     */
    'support_email' => 'support@company.email',

    /*
     * All payment transactions are carried out on the checkout route and are verified on the callback route.
     */
    'routes' => [
        'checkout' => [
            'path' => 'payment/gateways/{provider}/checkout/{reference}',
            'name' => 'payment.gateway.checkout',
        ],
        'callback' => [
            'path' => 'payment/gateways/{provider}/callback/{reference}',
            'name' => 'payment.gateway.callback',
        ]
    ],

    /**
     * All check out session and payment references are cached and when the payment have been completed, it gets flushed out.
     */
    'cache' => [
        'session' => [
            'key' => '_gateway_session_reference_',
            'expires' => 3600
        ],
        'payment' => [
            'key' => '_gateway_payment_reference_',
            'expires' => 3600
        ],
    ],

    /*
     * This is a list of all supported payment gateway providers.
     */
    'providers' => [
        'paystack' => [
            'name' => 'paystack',
            'channels' => ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer'],
            'base_url' => 'https://api.paystack.co/',
            'public' => env('PAYSTACK_PUBLIC'),
            'secret' => env('PAYSTACK_SECRET'),
        ],
        'flutterwave' => [
            'name' => 'flutterwave',
            'channels' => ['card', 'banktransfer', 'ussd', 'credit', 'mpesa', 'qr'],
            'base_url' => 'https://api.flutterwave.com/v3/',
            'public' => env('FLUTTERWAVE_PUBLIC'),
            'secret' => env('FLUTTERWAVE_SECRET'),
        ],
        'stripe' => [
            'name' => 'stripe',
            'channels' => ['card', 'acss_debit', 'alipay', 'klarna', 'us_bank_account'],
            'base_url' => 'https://api.paystack.co/',
            'public' => env('STRIPE_PUBLIC'),
            'secret' => env('STRIPE_SECRET'),
        ],
        'paypal' => [
            'name' => 'paypal',
            'channels' => null,
            'base_url' => 'https://api.paystack.co/',
            'public' => env('PAYPAL_PUBLIC'),
            'secret' => env('PAYPAL_SECRET'),
        ],
    ],
];
