<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config.php';
require_once dirname(__DIR__) . '/app/database.php';

$config = load_application_config(dirname(__DIR__));
$connection = database_connection($config['database']);

$expectedCounts = [
    'users' => 33,
    'books' => 100,
    'book_copies' => 300,
    'borrow_requests' => 17,
    'loans' => 14,
];

foreach ($expectedCounts as $table => $expectedCount) {
    $actualCount = (int) $connection->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();

    if ($actualCount !== $expectedCount) {
        throw new RuntimeException("Unexpected fixture count for {$table}.");
    }
}

echo "database_connection_check.php: PASS\n";
