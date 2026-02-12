<?php

    
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'auth/login', 'logout'], // تأكد من تطابق المسارات مع ملف الراوتس

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:8000', 
        'http://tenant1.localhost:8000', 
        'http://127.0.0.1:8000',
        'http://tenant1.localhost:5173'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];