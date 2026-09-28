<?php

use App\Services\FakePaymentGateway;
use App\Services\ZarinpalPaymentGateway;

return [
    'web_app_url' => config('app.web_app_url'),

    'gateway' => env('PAYMENT_GATEWAY', 'fake'),

    'gateways' => [
        'fake' => FakePaymentGateway::class,
        'zarinpal' => ZarinpalPaymentGateway::class,
    ],

    'zarinpal' => [
        'environment' => env('ZARINPAL_ENV', 'sandbox'),

        'merchant_id' => env('ZARINPAL_MERCHANT_ID'),

        'base_url' => env(
            'ZARINPAL_BASE_URL',
            'https://sandbox.zarinpal.com'
        ),

        'request_url' => env(
            'ZARINPAL_REQUEST_URL',
            'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
        ),

        'verify_url' => env(
            'ZARINPAL_VERIFY_URL',
            'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
        ),

        'payment_url' => env(
            'ZARINPAL_PAYMENT_URL',
            'https://sandbox.zarinpal.com/pg/StartPay/'
        ),

        'callback_url' => env('ZARINPAL_CALLBACK_URL'),
    ],
];
