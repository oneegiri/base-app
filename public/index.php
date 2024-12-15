<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;
use App\Config;
use App\Controllers\HomeController;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Initialize configuration
$config = Config::getInstance();

$router = new Router();
$router->registerController(HomeController::class);

echo $router->dispatch($method, $uri);