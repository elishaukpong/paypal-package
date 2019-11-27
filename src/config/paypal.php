<?php

return [
    'client_id'=>'AYA3SObQ8powoKKRgtPH5KQLVutLDiCuYBqCe-JzTHilEWhlM2xnkl05ank2bJ8qkrbLjb1RfjHPs201',
    'secret'=>'EKZP16bGmbkLqJ5JqL4uvg5N6Og_Y0nj1DfXOAJDYwJIjFmosdPy-3Q8pUHUdY9AKefcyGBqEn-dqrSE',

    'cancel_url' => env('PAYPAL_CANCEL_URL', '/'),
    'callback_url' => env('PAYPAL_CALLBACK_URL', '/')
];
