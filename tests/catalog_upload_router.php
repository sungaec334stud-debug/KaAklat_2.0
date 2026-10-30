<?php
// Dedicated loopback test router; never deploy or place under public/.
declare(strict_types=1);
if (PHP_SAPI !== 'cli-server' || ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1' || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(404); exit; }
ob_start();
require __DIR__ . '/catalog_management_integration_test.php';
ob_end_clean();
$pdo->exec("INSERT INTO books (book_id,title,description) VALUES (9001,'Upload fixture','Fictional fixture')");
header('Content-Type: application/json');
$old = null;
try {
    $input = ['action' => 'cover', 'book_id' => 9001];
    catalog_manage($pdo, $input, $token, $_FILES['cover'] ?? null);
    $old = $pdo->query('SELECT cover_path FROM books WHERE book_id=9001')->fetchColumn();
    cm_assert((bool) preg_match('/^upload-[a-f0-9]{32}\.jpg$/', $old));
    $folder = dirname(__DIR__) . '/private/covers/';
    cm_assert(getimagesize($folder . $old)[2] === IMAGETYPE_JPEG);
    $before = glob($folder . 'upload-*.jpg');
    // Fixture UNIQUE audit code fails this replacement after staging and updating.
    cm_reject(fn () => catalog_manage($pdo, $input, $token, $_FILES['cover'] ?? null));
    cm_assert($pdo->query('SELECT cover_path FROM books WHERE book_id=9001')->fetchColumn() === $old);
    cm_assert(is_file($folder . $old) && glob($folder . 'upload-*.jpg') === $before);
    echo json_encode(['result' => 'PASS', 'checks' => 'multipart upload, random JPEG output, replacement rollback, old cover preserved, new file cleaned']);
} catch (InvalidArgumentException $exception) {
    http_response_code(400); echo json_encode(['result' => 'rejected']);
} finally {
    if ($old !== null && preg_match('/^upload-[a-f0-9]{32}\.jpg$/', $old)) { unlink(dirname(__DIR__) . '/private/covers/' . $old); }
}
