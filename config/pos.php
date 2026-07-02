<?php

return [
    'default_location_id' => env('POS_DEFAULT_LOCATION_ID', 1),
    'payment_gateways' => [
        ['value' => 'cash', 'label' => 'Tunai'],
        ['value' => 'qris', 'label' => 'QRIS'],
        ['value' => 'bank_transfer', 'label' => 'Transfer Bank'],
        ['value' => 'debit', 'label' => 'Debit Card'],
    ],
    'loyalty_tiers' => ['BRONZE', 'SILVER', 'GOLD', 'PLATINUM'],
];
