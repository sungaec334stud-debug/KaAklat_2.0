<?php

// Local benchmark router. Do not upload to a web host.
declare(strict_types=1);

$database = getenv('KAAKLAT_PERFORMANCE_DB') ?: '';
$run = getenv('KAAKLAT_PERFORMANCE_RUN') ?: '';
if (PHP_SAPI !== 'cli-server' || ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1'
    || !preg_match('/^kaaklat_perf_[a-f0-9]{12}$/D', $database)
    || !preg_match('/^[a-f0-9]{12}$/D', $run)) {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/app/bootstrap.php';
$appConfig['database']['database'] = $database;
$appConfig['session_name'] = 'kaaklat_performance_' . $run;
header('X-Kaaklat-Performance-Run: ' . $run);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!in_array($path, ['/login.php', '/catalog.php', '/health.php'], true)) {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/public/' . basename($path);
