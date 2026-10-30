<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog.php';
start_application_session();
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
try {
    $pdo = database_connection(application_config()['database']);
    if (!catalog_access_allowed(current_user($pdo))) { http_response_code(403); exit; }
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $book = $id === false ? null : catalog_book($pdo, $id);
    $name = $book['cover_path'] ?? '';
    if (!preg_match('/^(?:gutenberg-[0-9]+|upload-[a-f0-9]{32})\.jpg$/D', $name)) { http_response_code(404); exit; }
    $path = dirname(__DIR__) . '/private/covers/' . $name;
    if (!is_file($path)) { http_response_code(404); exit; }
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . filesize($path));
    readfile($path);
} catch (Throwable) { http_response_code(503); }
