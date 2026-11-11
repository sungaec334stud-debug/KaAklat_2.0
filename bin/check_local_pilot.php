<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/app/config.php';

try {
    $config = load_application_config(dirname(__DIR__));
    if (($config['app_env'] ?? null) !== 'local' || ($config['database']['database'] ?? null) !== 'kaaklat_pilot') {
        fwrite(STDERR, "Startup stopped: this launcher requires local kaaklat_pilot configuration.\n");
        exit(1);
    }
    echo "Local kaaklat_pilot configuration verified.\n";
} catch (Throwable) {
    fwrite(STDERR, "Startup stopped: local configuration could not be verified. No server started.\n");
    exit(1);
}
