<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
require_once dirname(__DIR__) . '/app/bootstrap.php';
if (application_config()['app_env'] !== 'local' || application_config()['database']['database'] !== 'kaaklat_dev') {
    throw new RuntimeException('This operator script is restricted to local kaaklat_dev.');
}
$samples = require dirname(__DIR__) . '/docs/sample_books.php';
$pdo = database_connection(application_config()['database']);
$folder = dirname(__DIR__) . '/private';
$backup = [];
foreach (['books', 'authors', 'book_authors', 'book_copies'] as $table) { $backup[$table] = $pdo->query('SELECT * FROM ' . $table)->fetchAll(); }
$backupPath = $folder . '/catalog-before-real-books-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.json';
if (file_put_contents($backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)) === false) { throw new RuntimeException('Backup failed'); }
$pdo->beginTransaction();
try {
    foreach ($samples as $index => [$sourceId, $title, $author, $description]) {
        $id = $index + 1;
        $statement = $pdo->prepare('SELECT title FROM books WHERE book_id = ? FOR UPDATE');
        $statement->execute([$id]);
        $old = $statement->fetchColumn();
        $original = null;
        foreach ($backup['books'] as $saved) { if ((int) $saved['book_id'] === $id) { $original = $saved; break; } }
        if ($old === false || ($old !== $title && !str_starts_with($old, 'The Lantern Valley') && !str_contains((string) ($original['description'] ?? ''), 'Fictional demo publication.'))) {
            throw new RuntimeException("Book $id is not an expected sample; update refused.");
        }
        $cover = 'gutenberg-' . $sourceId . '.jpg';
        if (!is_file($folder . '/covers/' . $cover)) { throw new RuntimeException('Prepare all covers first'); }
        $lookup = $pdo->prepare('SELECT author_id FROM authors WHERE author_name = ? ORDER BY author_id LIMIT 1');
        $lookup->execute([$author]);
        $authorId = $lookup->fetchColumn();
        if ($authorId === false) {
            $pdo->prepare('INSERT INTO authors (author_name) VALUES (?)')->execute([$author]);
            $authorId = (int) $pdo->lastInsertId();
        }
        $pdo->prepare('DELETE FROM book_authors WHERE book_id = ?')->execute([$id]);
        $pdo->prepare('INSERT INTO book_authors (book_id, author_id, credit_order) VALUES (?, ?, 1)')->execute([$id, $authorId]);
        $pdo->prepare('UPDATE books SET title = ?, description = ?, cover_path = ?, archived_at = NULL WHERE book_id = ?')->execute([$title, $description, $cover, $id]);
    }
    // Only obsolete fictional fixtures without pending requests or unresolved loans may be hidden.
    $pdo->exec("UPDATE books b SET archived_at = UTC_TIMESTAMP() WHERE b.book_id > 20 AND b.archived_at IS NULL
        AND b.title LIKE 'The Lantern Valley Notebooks %'
        AND NOT EXISTS (SELECT 1 FROM borrow_requests r WHERE r.book_id = b.book_id AND r.decision = 'pending')
        AND NOT EXISTS (SELECT 1 FROM book_copies c JOIN loans l ON l.copy_id = c.copy_id WHERE c.book_id = b.book_id AND l.returned_at IS NULL AND l.cancelled_at IS NULL)");
    record_audit_event($pdo, null, 'catalog.samples_replaced', 'books', null, 'Installed 20 real classic titles; source references in docs/sample_books.php. Fictional circulation history retained.');
    $pdo->commit();
    echo "Installed 20 real titles and covers. Private pre-update catalog backup created.\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    throw $exception;
}
