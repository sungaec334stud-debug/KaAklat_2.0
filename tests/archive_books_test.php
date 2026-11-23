<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog_management.php';
require_once dirname(__DIR__) . '/app/archived_catalog.php';
ob_start(); start_application_session();
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
$pdo->exec('CREATE TEMPORARY TABLE audit_events (audit_id INT AUTO_INCREMENT PRIMARY KEY, actor_user_id INT, event_code VARCHAR(64), entity_type VARCHAR(32), entity_id INT, event_summary VARCHAR(255)) ENGINE=InnoDB');
function ab_check(bool $ok, string $label): void { if (!$ok) { throw new RuntimeException($label); } echo "PASS: $label\n"; }
function ab_reject(callable $operation, string $label): void {
    try { $operation(); } catch (InvalidArgumentException|DomainException|PDOException) { ab_check(true, $label); return; }
    throw new RuntimeException('Accepted invalid operation: ' . $label);
}
$_SESSION=['user_id'=>1,'session_version'=>1,'csrf_token'=>str_repeat('b',64)];$token=csrf_token();
$pdo->exec("INSERT INTO books(book_id,title,description,archived_at) VALUES(1,'Fictional 100% title','Preserved text','2026-11-01 01:00:00'),(2,'Active title','Active',NULL),(3,'No active copies','Archived','2026-11-01 01:00:00')");
$pdo->exec("INSERT INTO authors VALUES(1,'Fictional archive author')");$pdo->exec('INSERT INTO book_authors VALUES(1,1,1)');
$pdo->exec("INSERT INTO book_copies VALUES(1,1,'ARCHIVE-A',NULL),(2,1,'ARCHIVE-B','2026-10-01 00:00:00'),(3,3,'ARCHIVE-C','2026-10-01 00:00:00')");
$pdo->exec("INSERT INTO borrow_requests VALUES(1,1,'approved','2026-10-01 00:00:00')");$pdo->exec("INSERT INTO loans VALUES(1,1,1,'2026-10-01 00:00:00','2026-10-02 00:00:00',NULL)");
$listed=archived_catalog_books($pdo,'%',1);ab_check($listed['total']===1 && $listed['books'][0]['title']==='Fictional 100% title','Archived literal search');
ab_check(archived_catalog_books($pdo,'archive author',1)['total']===1,'Archived author search');
$book=archived_catalog_book($pdo,1);ab_check((int)$book['total_copies']===1 && (int)$book['archived_copies']===1,'Derived active/archived copy counts');
ab_check(catalog_book($pdo,1)===null && archived_catalog_book($pdo,2)===null,'Active and archived selections stay separate');
$restore=['action'=>'unarchive_book','book_id'=>1,'archive_version'=>$book['archived_at'],'archive_event'=>'0','restore_confirmation'=>'yes'];
ab_reject(fn()=>catalog_manage($pdo,$restore,'forged'),'Restore rejects CSRF');
ab_reject(fn()=>catalog_manage($pdo,array_replace($restore,['restore_confirmation'=>'']),$token),'Restore requires confirmation');
ab_reject(fn()=>catalog_manage($pdo,array_replace($restore,['archive_version'=>'2020-01-01 00:00:00']),$token),'Restore rejects stale archive version');
ab_reject(fn()=>catalog_manage($pdo,$restore+['available_copies'=>99],$token),'Restore rejects forged availability');
foreach([2,3] as $actor){$_SESSION['user_id']=$actor;ab_reject(fn()=>archived_catalog_books($pdo,'',1),'Unauthorized archived list denied');ab_reject(fn()=>archived_catalog_book($pdo,1),'Unauthorized archived detail denied');ab_reject(fn()=>catalog_manage($pdo,$restore,$token),'Unauthorized restore denied');}
$_SESSION['user_id']=1;$_SESSION['session_version']=0;ab_reject(fn()=>catalog_manage($pdo,$restore,$token),'Revoked session denied');$_SESSION=['user_id'=>1,'session_version'=>1,'csrf_token'=>$token];
$before=[];foreach(['book_copies','borrow_requests','loans','authors','book_authors']as$table){$before[$table]=$pdo->query('SELECT * FROM '.$table)->fetchAll();}
ab_check(catalog_manage($pdo,$restore,$token)===1,'Restore returns existing book ID');
ab_check((int)catalog_book($pdo,1)['available_copies']===1,'Restored availability derived from active copies');
foreach($before as$table=>$rows){ab_check($pdo->query('SELECT * FROM '.$table)->fetchAll()===$rows,'Restore preserves '.$table);}
ab_check((int)$pdo->query("SELECT COUNT(*) FROM audit_events WHERE event_code='catalog.unarchive_book'")->fetchColumn()===1,'Restore records one audit');
ab_reject(fn()=>catalog_manage($pdo,$restore,$token),'Repeated restore rejected safely');
ab_check((int)$pdo->query("SELECT COUNT(*) FROM audit_events WHERE event_code='catalog.unarchive_book'")->fetchColumn()===1,'Repeated restore adds no audit');
$pdo->exec("UPDATE books SET archived_at='2026-11-02 00:00:00' WHERE book_id=1");ab_reject(fn()=>catalog_manage($pdo,$restore,$token),'Old confirmation cannot restore a later archive');
$pdo->exec("UPDATE books SET archived_at='2026-11-01 01:00:00' WHERE book_id=1");
record_audit_event($pdo,1,'catalog.archive_book','books',1,'Fictional rearchive with same second timestamp');
ab_reject(fn()=>catalog_manage($pdo,$restore,$token),'Archive event rejects stale confirmation even within same timestamp second');
$pdo->exec('ALTER TABLE audit_events ADD UNIQUE(event_code)');
ab_reject(fn()=>catalog_manage($pdo,['action'=>'unarchive_book','book_id'=>3,'archive_version'=>'2026-11-01 01:00:00','archive_event'=>'0','restore_confirmation'=>'yes'],$token),'Audit failure rolls back restore');
ab_check(archived_catalog_book($pdo,3)!==null,'Failed audit preserves archived state');$pdo->exec('ALTER TABLE audit_events DROP INDEX event_code');
catalog_manage($pdo,['action'=>'unarchive_book','book_id'=>3,'archive_version'=>'2026-11-01 01:00:00','archive_event'=>'0','restore_confirmation'=>'yes'],$token);
ab_check((int)catalog_book($pdo,3)['available_copies']===0,'All individually archived copies remain unavailable');
for($i=4;$i<18;$i++){$pdo->exec("INSERT INTO books(book_id,title,description,archived_at) VALUES($i,'Archive pagination $i','Fixture','2026-11-01 00:00:00')");}
$page=archived_catalog_books($pdo,'',999);ab_check($page['page']===2 && $page['total']===15 && count($page['books'])===3,'Archived pagination clamps and counts');
$pdo->exec("UPDATE users SET account_status='suspended' WHERE user_id=1");ab_reject(fn()=>archived_catalog_books($pdo,'',1),'Suspended librarian denied');
echo "Archive integration checks passed using temporary tables only.\n";
