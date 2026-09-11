<?php

    
return [
    'paths' => ['api/*', 'oauth/*', 'sanctum/csrf-cookie', 'auth/login', 'logout'], // تأكد من تطابق المسارات مع ملف الراوتس

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // 'http://tenant1.localhost:5173/',
        'http://e-wallet.localhost:5173',


    ],

    'allowed_origins_patterns' => ['/^http:\/\/.*\.localhost(:\d+)?$/'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];