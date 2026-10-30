<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog_management.php';
start_application_session();
$pdo = database_connection(application_config()['database']);
// Connection-local InnoDB fixtures. No persistent table is altered or populated.
$pdo->exec("CREATE TEMPORARY TABLE users (user_id INT PRIMARY KEY, role VARCHAR(20), account_status VARCHAR(20), session_version INT, username VARCHAR(32), display_name VARCHAR(100)) ENGINE=InnoDB");
$pdo->exec("INSERT INTO users VALUES (1,'librarian','active',1,'fixture_staff','Fictional librarian'),(2,'student','active',1,'fixture_student','Fictional student'),(3,'administrator','active',1,'fixture_admin','Fictional admin')");
$pdo->exec('CREATE TEMPORARY TABLE books (book_id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255), description TEXT, cover_path VARCHAR(255), archived_at DATETIME NULL) ENGINE=InnoDB');
require_once __DIR__ . '/metadata_fixtures.php';
metadata_fixture_columns($pdo);
$pdo->exec('CREATE TEMPORARY TABLE authors (author_id INT AUTO_INCREMENT PRIMARY KEY, author_name VARCHAR(100)) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE book_authors (book_id INT, author_id INT, credit_order INT, UNIQUE(book_id, credit_order)) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE book_copies (copy_id INT AUTO_INCREMENT PRIMARY KEY, book_id INT, copy_code VARCHAR(32) UNIQUE, archived_at DATETIME NULL) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE borrow_requests (request_id INT PRIMARY KEY, book_id INT, decision VARCHAR(20), decided_at DATETIME) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE loans (loan_id INT PRIMARY KEY, request_id INT, copy_id INT, checked_out_at DATETIME NULL, returned_at DATETIME NULL, cancelled_at DATETIME NULL) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE audit_events (audit_id INT AUTO_INCREMENT PRIMARY KEY, actor_user_id INT, event_code VARCHAR(64) UNIQUE, entity_type VARCHAR(32), entity_id INT, event_summary VARCHAR(255)) ENGINE=InnoDB');
function cm_assert(bool $condition): void { if (!$condition) { throw new RuntimeException('Integration assertion failed'); } }
function cm_reject(callable $operation): void {
    try { $operation(); } catch (InvalidArgumentException|DomainException|PDOException) { return; }
    throw new RuntimeException('Unauthorized/invalid mutation accepted');
}
$_SESSION = ['user_id' => 1, 'session_version' => 1, 'csrf_token' => str_repeat('b',64)];
$token = $_SESSION['csrf_token'];
$input = ['action' => 'create', 'title' => 'Fictional integration book', 'description' => 'Fictional description', 'authors' => "Author One\nAuthor Two", 'quantity' => '3', 'category_name'=>'Test Category', 'publication_year'=>'2001'];
cm_reject(fn () => catalog_manage($pdo, $input, 'forged'));
foreach ([2,3] as $actor) { $_SESSION['user_id'] = $actor; cm_reject(fn () => catalog_manage($pdo, $input, $token)); }
$_SESSION['user_id'] = 1;
$_SESSION['session_version'] = 0; cm_reject(fn () => catalog_manage($pdo, $input, $token));
$_SESSION = ['user_id' => 1, 'session_version' => 1, 'csrf_token' => $token];
cm_reject(fn () => catalog_manage($pdo, $input + ['role' => 'administrator'], $token));
$id = catalog_manage($pdo, $input, $token);
cm_assert((int) catalog_book($pdo, $id)['publication_year'] === 2001 && catalog_book($pdo, $id)['category_name'] === 'Test Category');
cm_assert((int) $pdo->query('SELECT COUNT(*) FROM book_copies')->fetchColumn() === 3);
cm_assert((int) $pdo->query('SELECT COUNT(DISTINCT copy_code) FROM book_copies')->fetchColumn() === 3);
cm_assert((int) $pdo->query('SELECT COUNT(*) FROM book_authors')->fetchColumn() === 2);
catalog_manage($pdo, array_replace($input, ['action' => 'edit', 'book_id' => $id, 'title' => 'Edited fictional title']), $token);
catalog_manage($pdo, ['action' => 'add_copies', 'book_id' => $id, 'quantity' => '2'], $token);
cm_assert((int) catalog_book($pdo, $id)['available_copies'] === 5);
// Duplicate fixture audit event causes failure AFTER authors, book and copies were inserted.
$counts = [];
foreach (['books','authors','book_authors','book_copies','audit_events'] as $table) { $counts[$table] = $pdo->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn(); }
cm_reject(fn () => catalog_manage($pdo, $input, $token));
foreach ($counts as $table => $count) { cm_assert($pdo->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn() === $count); }
$pdo->exec("INSERT INTO borrow_requests VALUES (1,$id,'pending',UTC_TIMESTAMP())");
cm_reject(fn () => catalog_manage($pdo, ['action' => 'archive_book','book_id' => $id], $token));
$pdo->exec("UPDATE borrow_requests SET decision='approved'");
$pdo->exec('INSERT INTO loans VALUES (1,1,1,NULL,NULL,NULL)');
cm_reject(fn () => catalog_manage($pdo, ['action' => 'archive_book','book_id' => $id], $token));
cm_reject(fn () => catalog_manage($pdo, ['action' => 'archive_copy','book_id' => $id,'copy_id' => 1], $token));
cm_reject(fn () => catalog_manage($pdo, ['action' => 'archive_copy','book_id' => $id,'copy_id' => 999], $token));
catalog_manage($pdo, ['action' => 'archive_copy','book_id' => $id,'copy_id' => 2], $token);
cm_assert((int) catalog_book($pdo, $id)['total_copies'] === 4);
$pdo->exec('UPDATE loans SET cancelled_at=UTC_TIMESTAMP()');
catalog_manage($pdo, ['action' => 'archive_book','book_id' => $id], $token);
cm_assert(catalog_book($pdo, $id) === null);
cm_assert((int) $pdo->query('SELECT COUNT(*) FROM loans')->fetchColumn() === 1);
cm_reject(fn () => catalog_manage($pdo, ['action'=>'add_copies','book_id'=>$id,'quantity'=>1], $token));
echo "Catalog management transaction/authorization/archive tests passed (temporary tables only).\n";
