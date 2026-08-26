<?php
return [
    'info' => [
        'title' => 'Accounting System API',
        'description' => 'Generated API documentation for the Accounting System backend.',
        'version' => '1.0.0',
    ],
    'routes' => [
        'include' => [
            'api/*',
        ],
    ],
    'output' => [
        'format' => 'html', // generate Swagger UI style HTML
        'target' => base_path('public/docs'),
    ],
    // other default options can be left as they are
];
?>
