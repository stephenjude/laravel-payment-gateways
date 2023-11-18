<?php

return [

    /*
     * Company's support email is displayed to the user when they have completed their payment transactions.
     * When set to null the support email won't be displayed.
     */
    'support_email' => 'support@company.email',

    /*
     * Display messages for successful or failed payments.
     */
    'message' => [
        'success' => 'Your payment transaction was successful. Please close the tab to continue.',
        'failed' => 'Your payment transaction was not successful. Please close the tab to continue.',
        'pending' => 'Your payment transaction is being processed by our payment partner. Please stay on this page and refresh in 5 minutes.',
    ],

    /*
     * Debug mode set to true logs all the HTTP response to your application log file
     */
    'debug_mode' => true,

    /*
     * All payment transactions are verified on the callback route.
     */
    'routes' => [
        'callback' => [
            'path' => 'payment/gateways/{provider}/callback/{reference}',
            'name' => 'payment.gateway.callback',
        ],
        'checkout' => [
            'path' => 'payment/gateways/{provider}/checkout/{reference}',
            'name' => 'payment.gateway.checkout',
        ],
        'error' => [
            'path' => 'payment-gateway-error',
            'name' => 'payment.gateway.error',
        ],

        /*
         * Define your custom routes for successful and failed payments.
         */
        'custom' => [
            'success' => [
                'path' => null,
                'name' => null,
            ],
            'failed' => [
                'path' => null,
                'name' => null,
            ],
        ],
    ],

    /**
     * All check out session and payment references are cached and when the payment have been completed, it gets flushed out.
     */
    'cache' => [
        'session' => [
            'key' => '_gateway_session_reference_',
            'expires' => 42300, // 12 hours
        ],
        'payment' => [
            'key' => '_gateway_payment_reference_',
            'expires' => 42300, // 12 hours
        ],
    ],

    /*
     * This is a list of all supported payment gateway providers.
     */
    'providers' => [
        'pay4me' => [
            'name' => 'pay4me',
            'channels' => ['bank_transfer'],
            'base_url' => env('PAY4ME_API_URL', 'https://pay.pay4me.app/'),
            'public' => env('PAY4ME_PUBLIC'),
            'secret' => env('PAY4ME_SECRET'),
        ],
        'monnify' => [
            'name' => 'monnify',
            'channels' => ['CARD', 'ACCOUNT_TRANSFER'],
            'base_url' => env('MONNIFY_API_URL', 'https://api.monnify.com/'),
            'public' => env('MONNIFY_PUBLIC'),
            'secret' => env('MONNIFY_SECRET'),
            'contract_code' => env('MONNIFY_CONTRACT_CODE'),
        ],
        'pawapay' => [
            'name' => 'pawapay',
            'channels' => null,
            'base_url' => env('PAWAPAY_API_URL', 'https://api.pawapay.cloud/'),
            'secret' => env('PAWAPAY_SECRET'),
        ],
        'seerbit' => [
            'name' => 'seerbit',
            'channels' => ['card', 'account', 'transfer', 'ussd'],
            'base_url' => env('SEERBIT_API_URL', 'https://seerbitapi.com/'),
            'public' => env('SEERBIT_PUBLIC'),
            'secret' => env('SEERBIT_SECRET'),
        ],
        'paystack' => [
            'name' => 'paystack',
            'channels' => ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer'],
            'base_url' => env('PAYSTACK_API_URL', 'https://api.paystack.co/'),
            'public' => env('PAYSTACK_PUBLIC'),
            'secret' => env('PAYSTACK_SECRET'),
        ],
        'flutterwave' => [
            'name' => 'flutterwave',
            'channels' => ['card', 'banktransfer', 'ussd', 'credit', 'mpesa', 'qr'],
            'base_url' => env('FLUTTERWAVE_API_URL', 'https://api.flutterwave.com/'),
            'public' => env('FLUTTERWAVE_PUBLIC'),
            'secret' => env('FLUTTERWAVE_SECRET'),
        ],
        'stripe' => [
            'name' => 'stripe',
            'channels' => ['card', 'acss_debit', 'us_bank_account'],
            'base_url' => env('STRIPE_API_URL', 'https://api.stripe.com/'),
            'public' => env('STRIPE_PUBLIC'),
            'secret' => env('STRIPE_SECRET'),
        ],
        'klasha' => [
            'name' => 'klasha',
            'channels' => null,
            'base_url' => env('KLASHA_API_URL', 'https://gate.klasapps.com/'),
            'public' => env('KLASHA_PUBLIC'),
            'secret' => env('KLASHA_SECRET'),
        ],
    ],
];
