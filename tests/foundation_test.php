<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers.php';
require_once dirname(__DIR__) . '/app/database.php';

function expect_true(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function expect_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message);
    }
}

function expect_throws(callable $callback, string $exceptionClass, string $message): void
{
    try {
        $callback();
    } catch (Throwable $exception) {
        if ($exception instanceof $exceptionClass) {
            return;
        }

        throw new RuntimeException($message . ': unexpected ' . $exception::class);
    }

    throw new RuntimeException($message . ': no exception thrown');
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

session_name('kaaklat_test');
session_start();
$_SESSION = [];

expect_same('&lt;script&gt;&quot;&amp;&lt;/script&gt;', h('<script>"&</script>'), 'HTML must be escaped');

$token = csrf_token();
expect_true(strlen($token) === 64, 'CSRF token must be 32 random bytes encoded as hexadecimal');
expect_true(csrf_is_valid($token), 'Fresh CSRF token must be accepted');
expect_true(!csrf_is_valid('not-the-token'), 'Different CSRF token must be rejected');

flash_set('success', 'Saved safely.');
expect_same('Saved safely.', flash_pull('success'), 'Flash message must be returned once');
expect_same(null, flash_pull('success'), 'Flash message must be removed after reading');

expect_same('A useful reason', required_text('  A useful reason  ', 3, 100, 'Reason'), 'Required text must trim whitespace');
expect_throws(
    static fn (): string => required_text('  ', 3, 100, 'Reason'),
    InvalidArgumentException::class,
    'Blank required text must fail'
);

$databaseConfig = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'kaaklat_dev',
    'username' => 'root',
    'password' => '',
];

expect_same(
    'mysql:host=127.0.0.1;port=3306;dbname=kaaklat_dev;charset=utf8mb4',
    database_dsn($databaseConfig),
    'Database DSN must use utf8mb4 and explicit local host/port/database'
);
expect_throws(
    static fn (): string => database_dsn(['host' => '127.0.0.1']),
    InvalidArgumentException::class,
    'Incomplete database configuration must fail before connecting'
);

echo "foundation_test.php: PASS\n";
