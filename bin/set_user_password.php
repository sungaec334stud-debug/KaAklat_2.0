<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden: CLI execution only.\n";
    exit(1);
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

$username = $argv[1] ?? null;
$password = $argv[2] ?? null;

if ($username === null || $password === null) {
    echo "Usage: php bin/set_user_password.php <username> <new-password>\n";
    echo "Note: Password must be between 12 and 72 characters.\n";
    exit(1);
}

try {
    $config = application_config();
    $connection = database_connection($config['database']);
    set_user_password($connection, (string) $username, (string) $password);
    echo "SUCCESS: Password updated for '{$username}'. You may now sign in.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Error: " . $exception->getMessage() . "\n");
    exit(1);
}
