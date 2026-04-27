<?php

return [
    'order_status' => [
        'draft'     => 'Draft',
        'pending'   => 'Pending',
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'canceled'  => 'Canceled',
    ],

    'payment_method' => [
        'cash'     => 'Cash',
        'card'     => 'Card',
        'transfer' => 'Bank transfer',
        'mixed'    => 'Mixed',
        'crypto'   => 'Cryptocurrency',
    ],

    'payment_status' => [
        'pending'  => 'Pending',
        'paid'     => 'Paid',
        'partial'  => 'Partial payment',
        'refunded' => 'Refunded',
    ],
];
