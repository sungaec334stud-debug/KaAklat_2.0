<?php

declare(strict_types=1);

function load_application_config(string $projectRoot): array
{
    $configPath = $projectRoot . '/private/config.local.php';

    if (!is_file($configPath)) {
        throw new RuntimeException('Local configuration is missing. Copy the example configuration before running the application.');
    }

    $config = require $configPath;

    if (!is_array($config)) {
        throw new RuntimeException('Local configuration must return an array.');
    }

    foreach (['app_env', 'app_name', 'timezone', 'session_name', 'database'] as $key) {
        if (!array_key_exists($key, $config)) {
            throw new RuntimeException("Local configuration is missing '{$key}'.");
        }
    }

    if (!is_array($config['database'])) {
        throw new RuntimeException('Local database configuration must be an array.');
    }

    return $config;
}
