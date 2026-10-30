<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$config = application_config();
$pdo = database_connection($config['database']);

// 1. Valid authentication for demo_admin
$user = authenticate_user($pdo, 'demo_admin', 'AdminPass1234!');
if ($user === null || $user['username'] !== 'demo_admin' || $user['role'] !== 'administrator') {
    throw new RuntimeException('Failed to authenticate demo_admin with valid password.');
}

// 2. Invalid password returns null
$failedUser = authenticate_user($pdo, 'demo_admin', 'WrongPassword123!');
if ($failedUser !== null) {
    throw new RuntimeException('Expected null for invalid password.');
}

// 3. Invalid username returns null
$invalidUser = authenticate_user($pdo, 'non_existent_user', 'AdminPass1234!');
if ($invalidUser !== null) {
    throw new RuntimeException('Expected null for non-existent username.');
}

echo "auth_integration_test.php: PASS\n";
