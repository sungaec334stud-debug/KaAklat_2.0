<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/auth.php';

function auth_expect_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message);
    }
}

function auth_expect_throws(callable $callback, string $message): void
{
    try {
        $callback();
    } catch (InvalidArgumentException) {
        return;
    }

    throw new RuntimeException($message);
}

auth_expect_same('student_01', normalize_username(' Student_01 '), 'Username must normalize to lowercase');
auth_expect_throws(static fn (): string => normalize_username('two words'), 'Spaces must not be accepted in usernames');
auth_expect_throws(static fn (): string => normalize_username('ab'), 'Short usernames must not be accepted');
auth_expect_same('correct horse battery', validate_new_password('correct horse battery'), 'Valid password must be returned unchanged');
auth_expect_throws(static fn (): string => validate_new_password('short'), 'Short password must not be accepted');
auth_expect_throws(static fn (): string => validate_new_password('12345678901'), 'Password with 11 characters must be rejected');
auth_expect_same('123456789012', validate_new_password('123456789012'), 'Password with 12 characters must be accepted');
$password72 = str_repeat('a', 72);
auth_expect_same($password72, validate_new_password($password72), 'Password with 72 characters must be accepted');
auth_expect_throws(static fn (): string => validate_new_password(str_repeat('a', 73)), 'Password with 73 characters must be rejected');

echo "auth_unit_test.php: PASS\n";

