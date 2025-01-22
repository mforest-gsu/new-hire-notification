<?php

declare(strict_types=1);

use App\Controller\NotifyTrackerController;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__) . '/vendor/autoload.php';

(function (string $appDir) {
    (new Dotenv())->loadEnv($appDir . '/.env');

    (new NotifyTrackerController(
        (is_string($_ENV['LOG_DIR'] ?? null) ? $_ENV['LOG_DIR'] : $appDir . '/var/log'),
        (is_string($_ENV['PUBLIC_DIR'] ?? null) ? $_ENV['PUBLIC_DIR'] : $appDir . '/public')
    ))->process(is_string($_GET['t'] ?? null) ? $_GET['t'] : '');
})(dirname(__DIR__));
