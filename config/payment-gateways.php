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
            'public' => env('PAYSTACK_PUBLIC'),
            'secret' => env('PAYSTACK_SECRET'),
        ],
    ],
];
