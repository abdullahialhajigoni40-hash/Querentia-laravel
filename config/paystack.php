<?php

return [
    'publicKey' => env('PAYSTACK_PUBLIC_KEY'),
    'secretKey' => env('PAYSTACK_SECRET_KEY'),
    'paymentUrl' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
    'merchantEmail' => env('MERCHANT_EMAIL'),
    
    // Custom configuration for our app
    'basic_price' => env('SUBSCRIPTION_BASIC_PRICE', 2900), // in kobo
    'pro_price' => env('SUBSCRIPTION_PRO_PRICE', 4900), // in kobo
    'callback_url' => env('PAYSTACK_CALLBACK_URL'),
    'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
];