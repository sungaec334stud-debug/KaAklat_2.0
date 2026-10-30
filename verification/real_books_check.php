<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
$pdo = database_connection(application_config()['database']);
$samples = require dirname(__DIR__) . '/docs/sample_books.php';
foreach ($samples as $index => [$source, $title]) {
    $statement = $pdo->prepare('SELECT title, cover_path FROM books WHERE book_id = ? AND archived_at IS NULL');
    $statement->execute([$index + 1]);
    $row = $statement->fetch();
    if (!$row || $row['title'] !== $title || !getimagesize(dirname(__DIR__) . '/private/covers/' . $row['cover_path'])) { throw new RuntimeException('Sample validation failed'); }
}
foreach (['books' => 'archived_at IS NULL', 'book_copies' => '1=1', 'borrow_requests' => '1=1', 'loans' => '1=1'] as $table => $where) {
    echo $table . ': ' . $pdo->query('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $where)->fetchColumn() . "\n";
}
echo "All 20 sample titles and decoded covers verified.\n";
