<?php

return [
    'client_id'=>'',
    'secret'=>'',

    'cancel_url' => env('PAYPAL_CANCEL_URL', '/'),
    'callback_url' => env('PAYPAL_CALLBACK_URL', '/')
];
