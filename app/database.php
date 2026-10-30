<?php

declare(strict_types=1);

function database_dsn(array $database): string
{
    foreach (['host', 'port', 'database', 'username', 'password'] as $key) {
        if (!array_key_exists($key, $database) || !is_scalar($database[$key])) {
            throw new InvalidArgumentException("Database configuration is missing '{$key}'.");
        }
    }

    $host = (string) $database['host'];
    $port = filter_var($database['port'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
    $name = (string) $database['database'];

    if ($host === '' || $port === false || !preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
        throw new InvalidArgumentException('Database configuration contains an invalid host, port, or database name.');
    }

    return "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
}

function database_connection(array $database): PDO
{
    return new PDO(
        database_dsn($database),
        (string) $database['username'],
        (string) $database['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}

function database_is_available(array $database): bool
{
    try {
        $connection = database_connection($database);
        $connection->query('SELECT 1');

        return true;
    } catch (PDOException) {
        return false;
    }
}
