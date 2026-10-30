<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$config = application_config();

if ($config['app_env'] !== 'local') {
    http_response_code(404);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

if (!database_is_available($config['database'])) {
    http_response_code(503);
    echo json_encode(['status' => 'unavailable']);
    exit;
}

echo json_encode(['status' => 'ready']);
