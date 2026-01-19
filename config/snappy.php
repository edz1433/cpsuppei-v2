<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary' => env(
            'WKHTMLTOPDF_PRODUCTION',               // Linux / production path from .env
            '"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe"' // fallback for local Windows
        ),
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
            'lowquality' => true,
        ],
        'env' => [],
    ],

];
