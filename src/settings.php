<?php
$_ENV['type'] = 'production';
$_ENV['web_development'] = 'https://mougo.classico.id';
$_ENV['web_production'] = 'https://www.mougo.co.id';

return [
    'settings' => [
        'displayErrorDetails' => ($_ENV['type'] == 'development'), // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'upload_directory' => __DIR__ . '/../assets/', // upload directory
        'upload_directory2' => __DIR__ . '/../assets-event/', // upload directory
        'upload_dir_foto_kk' => __DIR__ . '/../assets/foto/kk/', // upload directory
        'upload_dir_foto_ktp' => __DIR__ . '/../assets/foto/ktp/', // upload directory
        'upload_dir_foto_skck' => __DIR__ . '/../assets/foto/skck/', // upload directory
        'upload_dir_foto_stnk' => __DIR__ . '/../assets/foto/stnk/', // upload directory
        'upload_dir_foto_sim' => __DIR__ . '/../assets/foto/sim/', // upload directory
        'upload_dir_foto_diri' => __DIR__ . '/../assets/foto/diri/', // upload directory
        'upload_dir_foto_izin' => __DIR__ . '/../assets/foto/izin/', // upload directory
        'upload_dir_foto_rekening' => __DIR__ . '/../assets/foto/rekening/', // upload directory
        'upload_dir_foto_banner' => __DIR__ . '/../assets/foto/banner/', // upload directory
        'upload_dir_foto_layanan' => __DIR__ . '/../assets/foto/layanan/', // upload directory
        'upload_dir_foto_barang' => __DIR__ . '/../assets/foto/barang/', // upload directory
        'upload_dir_foto_blog' => __DIR__ . '/../assets/foto/blog/', // upload directory
        
        
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
            'user'=>'mougo',
            'password'=>'DigitalCreativeCrew1!@2',
            'driver' => 'mysql'
        ]
    ],
];
