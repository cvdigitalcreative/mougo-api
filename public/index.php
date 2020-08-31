<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
});

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Register routesCustomer
require __DIR__ . '/../src/customer/routesCustomer.php';

// Register routesDriver
require __DIR__ . '/../src/driver/routesDriver.php';

// Register routesDriver
require __DIR__ . '/../src/umum/routesUmum.php';

// Register routesAdmin
require __DIR__ . '/../src/admin/routesAdmin.php';

// Register routesOwner
require __DIR__ . '/../src/owner/routesOwner.php';

// Register routesMerchant
require __DIR__ . '/../src/merchant/routesMerchant.php';

// Register routesLayanan
require __DIR__ . '/../src/layanan/routesLayanan.php';

// Register routesBlog
require __DIR__ . '/../src/blog/routesBlog.php';

// Constant 
require __DIR__ . '/../src/constantText.php';


// Run app
$app->run();
