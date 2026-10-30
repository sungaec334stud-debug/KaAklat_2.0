<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$config = application_config();
$testPassword = getenv('KAAKLAT_TEST_PASSWORD');
if (!is_string($testPassword) || $testPassword === '') {
    throw new RuntimeException('Set KAAKLAT_TEST_PASSWORD locally before running this existing-account test.');
}
$pdo = database_connection($config['database']);

// 1. Valid authentication for demo_admin
$user = authenticate_user($pdo, 'demo_admin', $testPassword);
if ($user === null || $user['username'] !== 'demo_admin' || $user['role'] !== 'administrator') {
    throw new RuntimeException('Failed to authenticate demo_admin with valid password.');
}

// 2. Invalid password returns null
$failedUser = authenticate_user($pdo, 'demo_admin', 'WrongPassword123!');
if ($failedUser !== null) {
    throw new RuntimeException('Expected null for invalid password.');
}

// 3. Invalid username returns null
$invalidUser = authenticate_user($pdo, 'non_existent_user', $testPassword);
if ($invalidUser !== null) {
    throw new RuntimeException('Expected null for non-existent username.');
}

echo "auth_integration_test.php: PASS\n";
