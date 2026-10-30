<?php

return [
    'currency' => env('STORE_CURRENCY', 'USD'),

    // Tax is inclusive of product prices; tax_cents is informational.
    'tax_rate' => (float) env('STORE_TAX_RATE', 0.20),
    'tax_inclusive' => (bool) env('STORE_TAX_INCLUSIVE', true),

    'order_prefix' => env('STORE_ORDER_PREFIX', 'RE'),

    'payment_methods' => [
        'cod' => 'Cash on delivery',
        'bank_transfer' => 'Bank transfer',
    ],
];
