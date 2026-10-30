<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
require_once dirname(__DIR__) . '/app/bootstrap.php';
$config = application_config();
if ($config['app_env'] !== 'local' || $config['database']['database'] !== 'kaaklat_dev') { throw new RuntimeException('Local kaaklat_dev only.'); }
$pdo = database_connection($config['database']);
$backup = [];
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
    if (!preg_match('/^[a-z_]+$/D', $table)) { throw new RuntimeException('Unexpected table'); }
    $backup[$table] = ['ddl' => $pdo->query('SHOW CREATE TABLE `' . $table . '`')->fetch(PDO::FETCH_NUM)[1], 'rows' => $pdo->query('SELECT * FROM `' . $table . '`')->fetchAll()];
}
$backupFile = dirname(__DIR__) . '/private/before-book-metadata-' . date('Ymd-His') . '.json';
if (file_put_contents($backupFile, json_encode($backup, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)) === false) { throw new RuntimeException('Backup failed'); }
// Deliberately fails on a repeated/partial migration; inspect rather than guessing.
$sql = file_get_contents(dirname(__DIR__) . '/migrations/001_book_categories_year.sql');
foreach (explode(';', $sql) as $statement) { if (trim($statement) !== '') { $pdo->exec($statement); } }
$metadata = [
    [1813,'Romance'],[1818,'Gothic Fiction'],[1865,'Fantasy'],[1892,'Mystery'],[1897,'Gothic Fiction'],
    [1851,'Adventure'],[1859,'Historical Fiction'],[1891,'Gothic Fiction'],[1847,'Classic Fiction'],[1847,'Gothic Fiction'],
    [1876,'Adventure'],[1884,'Adventure'],[1883,'Adventure'],[1861,'Classic Fiction'],[1868,'Coming of Age'],
    [1911,"Children's Fiction"],[1908,'Coming of Age'],[1900,'Fantasy'],[1895,'Science Fiction'],[1911,'Fantasy'],
];
$samples = require dirname(__DIR__) . '/docs/sample_books.php';
$pdo->beginTransaction();
try {
    foreach ($metadata as $index => [$year, $category]) {
        $pdo->prepare('INSERT INTO categories (category_name) VALUES (?) ON DUPLICATE KEY UPDATE category_id=LAST_INSERT_ID(category_id)')->execute([$category]);
        $categoryId = (int) $pdo->lastInsertId();
        $pdo->prepare('UPDATE books SET category_id=?, publication_year=? WHERE book_id=? AND title=?')->execute([$categoryId, $year, $index + 1, $samples[$index][1]]);
    }
    record_audit_event($pdo, null, 'catalog.metadata_migrated', 'books', null, 'Added optional category and first-publication year; populated matching classic samples.');
    $pdo->commit();
} catch (Throwable $exception) { if ($pdo->inTransaction()) { $pdo->rollBack(); } throw $exception; }
echo "Migration 001 applied; private full-table snapshot saved; matching sample metadata populated.\n";
