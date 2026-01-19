<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary'  => '"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe"',
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
            'lowquality' => true,
        ],
        'env' => [],
    ],

];
