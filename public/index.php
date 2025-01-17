<?php

declare(strict_types=1);

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return fn (array $context): Kernel => new Kernel(
    is_string($context['APP_ENV'] ?? null) ? $context['APP_ENV'] : 'dev',
    is_scalar($context['APP_DEBUG'] ?? null) ? (bool) $context['APP_DEBUG'] : true
);
