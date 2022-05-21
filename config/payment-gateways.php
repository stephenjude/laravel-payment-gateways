<?php

return [
    'routes' => [
        'checkout' => [
            'path' => 'payment-gateways/{provider}/checkout/{reference}',
            'name' => 'payment.gateway.checkout',
        ],
        'callback' => [
            'path' => 'payment-gateways/{provider}/callback/{reference}',
            'name' => 'payment.gateway.callback',
        ]
    ],

    'cache' => [
        'session' => [
            'key' => '_gateway_session_reference_',
            'expries' => 3600
        ],
        'payment' => [
            'key' => '_gateway_payment_reference_',
            'expries' => 3600
        ],
    ],

    'providers' => [
        'paystack' => \Stephenjude\PaymentGateway\Providers\PaystackProvider::class,
    ],

    'paystack' => [
        'public' => env('PAYSTACK_PUBLIC'),
        'secret' => env('PAYSTACK_SECRET'),
    ],
];
