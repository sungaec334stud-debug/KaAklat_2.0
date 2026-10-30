<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog.php';
$pdo = database_connection(application_config()['database']);
$count = $pdo->query('SELECT COUNT(*) FROM books b JOIN categories c ON c.category_id=b.category_id WHERE b.book_id BETWEEN 1 AND 20 AND b.publication_year IS NOT NULL')->fetchColumn();
if ((int) $count !== 20) { throw new RuntimeException('Expected metadata for 20 samples'); }
$match = catalog_books($pdo, '', 1, ['author'=>'Austen','year'=>1813,'letter'=>'P','title'=>'Pride']);
if (count($match['books']) !== 1) { throw new RuntimeException('Combined sample filter failed'); }
$constraints = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='books'")->fetchAll(PDO::FETCH_COLUMN);
foreach (['fk_books_category','ck_books_year'] as $name) { if (!in_array($name,$constraints,true)) { throw new RuntimeException('Missing metadata constraint'); } }
echo "20 sample metadata rows, combined filter and schema constraints verified.\n";
