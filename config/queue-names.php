<?php

return [
    // self
    'getCertificates' => env('QUEUE_GET_CERTIFICATES', 'QUEUE_GET_CERTIFICATES'),

    // use
    'notification' => env('QUEUE_NOTIFICATION', 'QUEUE_NOTIFICATION'),
    // use queue
    'orderPaid' => env('QUEUE_ORDER_PAID', 'QUEUE_ORDER_PAID'),
    'reorder' => env('QUEUE_REORDER', 'QUEUE_REORDER'),
    'probe' => 'widget2_back_probe'
];

