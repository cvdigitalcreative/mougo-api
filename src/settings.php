<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'upload_directory' => __DIR__ . '/../assets/', // upload directory
        'upload_directory2' => __DIR__ . '/../assets-event/', // upload directory
        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'db' => [
            'host'=>'localhost',
            'dbname'=>'db_mougo',
            'user'=>'root',
            'password'=>'DigitalCreativeCrew1!@2',
            'driver' => 'mysql'
        ]
    ],
];
