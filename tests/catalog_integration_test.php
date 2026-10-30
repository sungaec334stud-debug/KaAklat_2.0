<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog.php';
require_once __DIR__ . '/catalog_test.php';
$pdo = database_connection(application_config()['database']);
// All writes below target temporary tables created on this connection only.
$pdo->exec('CREATE TEMPORARY TABLE books (book_id INT PRIMARY KEY, title VARCHAR(255), description TEXT, archived_at DATETIME NULL, cover_path VARCHAR(255) NULL) ENGINE=InnoDB');
require_once __DIR__ . '/metadata_fixtures.php';
metadata_fixture_columns($pdo);
$pdo->exec('CREATE TEMPORARY TABLE authors (author_id INT, author_name VARCHAR(100)) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE book_authors (book_id INT, author_id INT, credit_order INT) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE book_copies (copy_id INT, book_id INT, archived_at DATETIME NULL) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE borrow_requests (request_id INT, decided_at DATETIME) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE loans (copy_id INT, request_id INT, checked_out_at DATETIME NULL, returned_at DATETIME NULL, cancelled_at DATETIME NULL) ENGINE=InnoDB');
for ($id = 1; $id <= 15; $id++) {
    $pdo->prepare('INSERT INTO books (book_id, title, description, archived_at) VALUES (?, ?, ?, NULL)')->execute([$id, 'Fictional title ' . sprintf('%02d', $id), '<script>fictional description</script>']);
}
$pdo->exec("INSERT INTO books (book_id, title, description, archived_at) VALUES (99, 'Archived fixture', 'Hidden', UTC_TIMESTAMP())");
$pdo->exec("INSERT INTO authors VALUES (1, 'Fictional O\'Brien'), (2, 'Author 100%')");
$pdo->exec('INSERT INTO book_authors VALUES (1,1,1), (1,2,2)');
for ($id = 1; $id <= 7; $id++) { $pdo->exec("INSERT INTO book_copies VALUES ($id, 1, NULL)"); }
$pdo->exec('INSERT INTO book_copies VALUES (8,1,UTC_TIMESTAMP())');
$pdo->exec('INSERT INTO borrow_requests VALUES (1,UTC_TIMESTAMP()), (2,UTC_TIMESTAMP() - INTERVAL 49 HOUR), (3,UTC_TIMESTAMP() - INTERVAL 48 HOUR)');
$pdo->exec('INSERT INTO loans VALUES (1,1,UTC_TIMESTAMP(),NULL,NULL), (2,1,NULL,NULL,NULL), (3,2,NULL,NULL,NULL), (4,1,UTC_TIMESTAMP(),UTC_TIMESTAMP(),NULL), (5,1,NULL,NULL,UTC_TIMESTAMP()), (6,3,NULL,NULL,NULL)');
$book = catalog_book($pdo, 1);
catalog_assert((int) $book['total_copies'] === 7 && (int) $book['available_copies'] === 5);
catalog_assert(catalog_book($pdo, 99) === null && catalog_book($pdo, 987) === null);
catalog_assert(count(catalog_books($pdo, '', 1)['books']) === 12);
catalog_assert(catalog_books($pdo, '', 1)['has_next']);
catalog_assert(count(catalog_books($pdo, '', 2)['books']) === 3);
catalog_assert(catalog_books($pdo, '', 100)['books'] === []);
catalog_assert(count(catalog_books($pdo, "O'Brien", 1)['books']) === 1);
catalog_assert(count(catalog_books($pdo, '100%', 1)['books']) === 1);
catalog_assert(catalog_books($pdo, "' OR 1=1 --", 1)['books'] === []);
catalog_assert(catalog_books($pdo, '_', 1)['books'] === []);
catalog_assert(str_contains(h($book['description']), '&lt;script&gt;'));
echo "Catalog query/availability tests passed (temporary fixtures only).\n";
$pdo->exec("INSERT INTO categories (category_name) VALUES ('Fantasy')");
$pdo->exec('UPDATE books SET category_id=1, publication_year=1813 WHERE book_id IN (1,2)');
catalog_assert(count(catalog_books($pdo, '', 1, ['category'=>1,'year'=>1813,'letter'=>'F'])['books']) === 2);
catalog_assert(count(catalog_books($pdo, '', 1, ['author'=>"O'Brien",'title'=>'01','year'=>1813])['books']) === 1);
catalog_assert(catalog_books($pdo, '', 1, ['category'=>999])['books'] === []);
catalog_assert(catalog_books($pdo, '', 1, ['year'=>1814])['books'] === []);
echo "Combined category/year/title/letter/author SQL filters passed.\n";
