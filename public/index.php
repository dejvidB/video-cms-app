<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$router = new Router();
$router->handleRequest();
