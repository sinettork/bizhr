<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$cachedConfig = $projectRoot.'/bootstrap/cache/config.php';

if (is_file($cachedConfig) && ! unlink($cachedConfig)) {
    throw new RuntimeException('Unable to remove cached Laravel configuration before tests.');
}

$testingEnvironment = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'true',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
];

foreach ($testingEnvironment as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

require $projectRoot.'/vendor/autoload.php';
