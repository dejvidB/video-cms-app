<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Queue\VideoProcessorWorker;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

(new VideoProcessorWorker())->run();
