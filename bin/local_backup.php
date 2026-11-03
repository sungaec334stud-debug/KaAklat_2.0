<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/app/config.php';
require_once __DIR__ . '/backup_support.php';

if (count($argv) !== 3 || $argv[2] !== '--writes-paused') {
    fwrite(STDERR, "Usage: php bin/local_backup.php UNIQUE_FOLDER --writes-paused\nStop the local website server and other writers first. This flag acknowledges your manual write pause; it does not enforce maintenance mode.\n");
    exit(1);
}
try {
    $root = dirname(__DIR__);
    $config = load_application_config($root);
    if ($config['app_env'] !== 'local') { throw new RuntimeException('This helper is restricted to local development.'); }
    $result = create_local_backup($config['database'], $root, $argv[1], 'C:/xampp/mysql/bin/mysqldump.exe');
    echo "Backup files verified: " . $result['directory'] . "\nDatabase, covers, private configuration and working source included. Protect access and copy this bundle to separate protected storage. Restoration is not yet verified.\n";
} catch (Throwable) {
    fwrite(STDERR, "Backup failed. Do not rely on a partial bundle. Check paths, permissions, database availability and any private client.stderr.log; choose a NEW folder for retry. No credentials are printed.\n");
    exit(1);
}
