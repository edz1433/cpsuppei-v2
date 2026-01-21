<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary' => env('WKHTMLTOPDF_BINARY'),
        'timeout' => null,
        'options' => [
            'enable-local-file-access' => true,
            'lowquality' => true,
            'no-stop-slow-scripts' => true,
            'disable-smart-shrinking' => true,
        ],
        'env' => [],
    ],

];
