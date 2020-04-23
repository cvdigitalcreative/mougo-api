<?php
$_ENV['type'] = 'development';
$_ENV['web_development'] = 'http://45.114.118.64:72';
$_ENV['web_production'] = 'http://45.114.118.64:73';

return [
    'settings' => [
        'displayErrorDetails' => false, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'upload_directory' => __DIR__ . '/../assets/', // upload directory
        'upload_directory2' => __DIR__ . '/../assets-event/', // upload directory
        'upload_dir_foto_kk' => __DIR__ . '/../assets/foto/kk/', // upload directory
        'upload_dir_foto_ktp' => __DIR__ . '/../assets/foto/ktp/', // upload directory
        'upload_dir_foto_skck' => __DIR__ . '/../assets/foto/skck/', // upload directory
        'upload_dir_foto_stnk' => __DIR__ . '/../assets/foto/stnk/', // upload directory
        'upload_dir_foto_sim' => __DIR__ . '/../assets/foto/sim/', // upload directory
        'upload_dir_foto_diri' => __DIR__ . '/../assets/foto/diri/', // upload directory
        
        
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
            'dbname'=>'db_mougo_production',
            'user'=>'root',
            'password'=>'DigitalCreativeCrew1!@2',
            'driver' => 'mysql'
        ]
    ],
];
