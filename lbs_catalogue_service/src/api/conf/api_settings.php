<?php
return [
    'settings' => [
        'displayErrorDetails' => true,
        'db' => __DIR__ . '/config.ini',
        "cors" =>[
            "methods" => 'GET,POST,PUT,DELETE,OPTIONS',
            "headers" => 'Origin,Authorization,Content-Type,Accept',
            "maxAge" => 3600
        ],
    ],
];