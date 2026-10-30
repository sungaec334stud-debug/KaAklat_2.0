<?php

declare(strict_types=1);

const PROJECT_ROOT = __DIR__ . '/..';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

$appConfig = load_application_config(PROJECT_ROOT);
date_default_timezone_set((string) $appConfig['timezone']);

if ($appConfig['app_env'] === 'production') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

function application_config(): array
{
    global $appConfig;

    return $appConfig;
}

function start_application_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $config = application_config();
    $isProduction = $config['app_env'] === 'production';

    session_name((string) $config['session_name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isProduction,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}
