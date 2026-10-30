<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/borrow_requests.php';
start_application_session();
function request_assert(bool $ok): void { if (!$ok) { throw new RuntimeException('Request assertion failed.'); } }
function request_denied(callable $action): void {
    try { $action(); } catch (InvalidArgumentException|DomainException $e) { return; }
    throw new RuntimeException('Expected request rejection.');
}
request_assert(request_terms('30', '  Fictional study reason  ') === [30, 'Fictional study reason']);
foreach ([0, 31, '1.5', [], '1 OR 1'] as $days) { request_denied(fn() => request_terms($days, 'Fictional study reason')); }
foreach (['short', [], str_repeat('x',501)] as $reason) { request_denied(fn() => request_terms(1, $reason)); }
$pdo = database_connection(application_config()['database']);
// Shadow every application table. Foreign keys are omitted only in these connection-local fixtures.
$schema = file_get_contents(dirname(__DIR__) . '/schema.sql');
$schema = preg_replace('/^--.*$/m', '', $schema);
$schema = preg_replace('/SET SESSION sql_mode[^;]+;/', '', $schema);
$schema = preg_replace('/^\s*CONSTRAINT fk_.*\n/m', '', $schema);
$schema = preg_replace('/,\s*\)/', "\n)", $schema);
$schema = str_replace('CREATE TABLE ', 'CREATE TEMPORARY TABLE ', $schema);
foreach (explode(';', $schema) as $sql) { if (trim($sql) !== '') { $pdo->exec($sql); } }
require_once __DIR__ . '/metadata_fixtures.php';
metadata_fixture_columns($pdo);
$pdo->exec("INSERT INTO users (user_id,username,display_name,role,account_status) VALUES (1,'fixture_one','One','student','active'),(2,'fixture_two','Two','student','active'),(3,'fixture_lib','Staff','librarian','active'),(4,'fixture_admin','Admin','administrator','active')");
for ($i=1;$i<=8;$i++) {
    $pdo->exec("INSERT INTO books (book_id,title,description) VALUES ($i,'Fictional book $i','Fictional description')");
    $pdo->exec("INSERT INTO book_copies (book_id,copy_code) VALUES ($i,'TEST-$i')");
}
$_SESSION['user_id']=1; $_SESSION['session_version']=1; $_SESSION['last_activity']=time();
$token=csrf_token();
request_denied(fn()=>create_borrow_request($pdo,1,7,'Fictional study reason','forged'));
foreach ([3,4] as $actor) { $_SESSION['user_id']=$actor; request_denied(fn()=>create_borrow_request($pdo,1,7,'Fictional study reason',$token)); }
$_SESSION['user_id']=1;
foreach (['pending','suspended'] as $status) {
    $pdo->exec("UPDATE users SET account_status='$status' WHERE user_id=1");
    request_denied(fn()=>create_borrow_request($pdo,1,7,'Fictional study reason',$token));
}
$pdo->exec("UPDATE users SET account_status='active' WHERE user_id=1");
$before=catalog_book($pdo,1)['available_copies'];
$id=create_borrow_request($pdo,1,7,'Fictional study reason',$token);
request_assert(catalog_book($pdo,1)['available_copies']===$before);
request_denied(fn()=>create_borrow_request($pdo,1,7,'Fictional study reason',$token));
$_SESSION['user_id']=2;
request_assert(own_borrow_requests($pdo,1)===[]);
request_denied(fn()=>cancel_borrow_request($pdo,$id,$token));
$_SESSION['user_id']=1;
cancel_borrow_request($pdo,$id,$token);
cancel_borrow_request($pdo,$id,$token); // repeat is harmless
request_assert(catalog_book($pdo,1)['available_copies']===$before);
request_assert((int)$pdo->query("SELECT COUNT(*) FROM audit_events WHERE event_code='request.cancelled'")->fetchColumn()===1);
for($i=1;$i<=5;$i++) { create_borrow_request($pdo,$i,1,'Fictional study reason',$token); }
request_denied(fn()=>create_borrow_request($pdo,6,1,'Fictional study reason',$token));
$pdo->exec('UPDATE books SET archived_at=UTC_TIMESTAMP() WHERE book_id=8');
$_SESSION['user_id']=2;
request_denied(fn()=>create_borrow_request($pdo,8,1,'Fictional study reason',$token));
$pdo->exec('UPDATE book_copies SET archived_at=UTC_TIMESTAMP() WHERE book_id=7');
request_denied(fn()=>create_borrow_request($pdo,7,1,'Fictional study reason',$token));
// Duplicate audit code forces failure after mutation, using a connection-local unique index.
$pdo->exec('CREATE UNIQUE INDEX test_audit_failure ON audit_events (event_code, actor_user_id, entity_id)');
$pdo->exec("INSERT INTO audit_events(actor_user_id,event_code,entity_type,entity_id,event_summary) VALUES(2,'request.created','borrow_request',100,'Fixture')");
$pdo->exec('ALTER TABLE borrow_requests AUTO_INCREMENT=100');
try { create_borrow_request($pdo,6,1,'Fictional private reason',$token); throw new RuntimeException('Expected audit failure'); } catch(PDOException $e) {}
request_assert((int)$pdo->query('SELECT COUNT(*) FROM borrow_requests WHERE student_id=2')->fetchColumn()===0);
// Seed only fictional allocation history in the temporary tables.
$pdo->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,decision,approved_days,decided_at,decided_by)
    VALUES(200,2,6,7,'Fictional loan fixture','approved',7,UTC_TIMESTAMP(),3)");
$pdo->exec("INSERT INTO loans(request_id,copy_id,checked_out_at,checked_out_by,due_date)
    VALUES(200,6,UTC_TIMESTAMP()-INTERVAL 10 DAY,3,DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)-INTERVAL 1 DAY)");
request_denied(fn()=>create_borrow_request($pdo,1,7,'Fictional study reason',$token));
request_denied(fn()=>cancel_borrow_request($pdo,200,$token));
$pdo->exec('UPDATE loans SET returned_at=UTC_TIMESTAMP(),returned_by=3 WHERE request_id=200');
$pdo->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,decision,approved_days,decided_at,decided_by)
    VALUES(201,2,6,7,'Fictional hold fixture','approved',7,UTC_TIMESTAMP(),3)");
$pdo->exec('INSERT INTO loans(request_id,copy_id) VALUES(201,6)');
request_denied(fn()=>create_borrow_request($pdo,6,7,'Fictional study reason',$token));
$pdo->exec('UPDATE borrow_requests SET decided_at=UTC_TIMESTAMP()-INTERVAL 48 HOUR WHERE request_id=201');
$fresh=create_borrow_request($pdo,6,7,'Fictional study reason',$token);
request_assert($fresh>201); // expired hold neither blocks title nor logical availability
request_assert((int)catalog_book($pdo,6)['available_copies']===1);
cancel_borrow_request($pdo,$fresh,$token);
$_SESSION['session_version']=0;
request_denied(fn()=>create_borrow_request($pdo,1,7,'Fictional study reason',$token));
$_SESSION['session_version']=1;
// Five live allocations consume the allowance, including uncollected approvals.
for($i=1;$i<=5;$i++) {
    $rid=300+$i;
    $pdo->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,decision,approved_days,decided_at,decided_by) VALUES($rid,2,$i,7,'Fictional cap fixture','approved',7,UTC_TIMESTAMP(),3)");
    $pdo->exec("INSERT INTO loans(request_id,copy_id) VALUES($rid,$i)");
}
request_denied(fn()=>create_borrow_request($pdo,6,7,'Fictional study reason',$token));
request_assert(!str_contains(implode(' ', $pdo->query('SELECT event_summary FROM audit_events')->fetchAll(PDO::FETCH_COLUMN)), 'Fictional private reason'));
echo "Borrow request validation, ownership, limits, cancellation, availability and rollback passed (temporary tables).\n";
