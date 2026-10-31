<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/pickups.php';
start_application_session();
function pickup_assert(bool $ok,string $label): void { if(!$ok) { throw new RuntimeException($label); } }
function pickup_denied(callable $fn): void { try { $fn(); } catch(InvalidArgumentException|DomainException) { return; } throw new RuntimeException('Expected denial'); }
pickup_assert(pickup_due_date('2026-11-01 15:59:59',1)==='2026-11-02','Before Manila midnight');
pickup_assert(pickup_due_date('2026-11-01 16:00:00',1)==='2026-11-03','After Manila midnight');
pickup_assert(pickup_due_date('2026-12-31 16:00:00',30)==='2027-01-31','Thirty full calendar days');
pickup_assert(!pickup_deadline_expired('2026-11-01 16:00:00','2026-11-03 15:59:59'),'Before deadline');
pickup_assert(pickup_deadline_expired('2026-11-01 16:00:00','2026-11-03 16:00:00'),'Exact deadline');
$db=database_connection(application_config()['database']);
$schema=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql'));
$schema=preg_replace('/SET SESSION sql_mode[^;]+;/','',$schema);
$schema=preg_replace('/^\s*CONSTRAINT fk_.*\n/m','',$schema);
$schema=preg_replace('/,\s*\)/',"\n)",$schema);
$schema=str_replace('CREATE TABLE ','CREATE TEMPORARY TABLE ',$schema);
foreach(explode(';',$schema) as $sql) { if(trim($sql)!=='') { $db->exec($sql); } }
require_once __DIR__.'/metadata_fixtures.php'; metadata_fixture_columns($db);
$db->exec("INSERT INTO users(user_id,username,display_name,role,account_status) VALUES(1,'pickup_student','Student','student','active'),(2,'pickup_other','Other','student','active'),(3,'pickup_staff','Staff','librarian','active'),(4,'pickup_admin','Admin','administrator','active')");
for($i=1;$i<=8;$i++) {
    $db->exec("INSERT INTO books(book_id,title,description) VALUES($i,'Fictional pickup $i','Fixture')");
    $db->exec("INSERT INTO book_copies(copy_id,book_id,copy_code) VALUES($i,$i,'PICKUP-$i')");
    $db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,decision,approved_days,decided_at,decided_by) VALUES($i,1,$i,7,'Fictional original reason','approved',7,UTC_TIMESTAMP(),3)");
    $db->exec("INSERT INTO loans(loan_id,request_id,copy_id) VALUES($i,$i,$i)");
}
// Five live allocations; three are already expired and do not consume allowance.
$db->exec('UPDATE borrow_requests SET requested_at=UTC_TIMESTAMP()-INTERVAL 4 DAY,decided_at=UTC_TIMESTAMP()-INTERVAL 3 DAY WHERE request_id>5');
$_SESSION['user_id']=3; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
pickup_denied(fn()=>record_pickup($db,1,'forged'));
foreach([1,4] as $id) { $_SESSION['user_id']=$id; pickup_denied(fn()=>record_pickup($db,1,$token)); }
$_SESSION['user_id']=3; $_SESSION['session_version']=2; pickup_denied(fn()=>record_pickup($db,1,$token));
$_SESSION['session_version']=1; $_SESSION['last_activity']=time()-1801; pickup_denied(fn()=>record_pickup($db,1,$token)); $_SESSION['last_activity']=time();
$before=catalog_book($db,1)['available_copies'];
pickup_assert(record_pickup($db,1,$token)==='collected','Valid fifth slot');
$first=$db->query('SELECT * FROM loans WHERE loan_id=1')->fetch();
pickup_assert($first['due_date']===pickup_due_date($first['checked_out_at'],7),'Actual pickup due date');
pickup_assert(record_pickup($db,1,$token)==='already_collected','Idempotent pickup');
pickup_assert($first===$db->query('SELECT * FROM loans WHERE loan_id=1')->fetch(),'Repeated checkout unchanged');
pickup_assert($before===catalog_book($db,1)['available_copies'],'Availability unchanged');
pickup_assert((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='loan.collected'")->fetchColumn()===1,'Single audit');
$db->exec("UPDATE users SET account_status='suspended' WHERE user_id=1"); pickup_denied(fn()=>record_pickup($db,2,$token)); $db->exec("UPDATE users SET account_status='active' WHERE user_id=1");
$db->exec('UPDATE books SET archived_at=UTC_TIMESTAMP() WHERE book_id=2'); pickup_denied(fn()=>record_pickup($db,2,$token)); $db->exec('UPDATE books SET archived_at=NULL WHERE book_id=2');
$db->exec('UPDATE book_copies SET archived_at=UTC_TIMESTAMP() WHERE copy_id=2'); pickup_denied(fn()=>record_pickup($db,2,$token)); $db->exec('UPDATE book_copies SET archived_at=NULL WHERE copy_id=2');
$db->exec('CREATE UNIQUE INDEX pickup_audit_failure ON audit_events(event_code,actor_user_id,entity_id)');
$db->exec("INSERT INTO audit_events(actor_user_id,event_code,entity_type,entity_id,event_summary) VALUES(3,'loan.collected','loan',2,'Fixture')");
try { record_pickup($db,2,$token); throw new RuntimeException('Audit failure expected'); } catch(PDOException) {}
pickup_assert($db->query('SELECT checked_out_at FROM loans WHERE loan_id=2')->fetchColumn()===null,'Checkout rollback');
pickup_assert(record_pickup($db,6,$token)==='expired','Expired cleanup');
pickup_assert((int)catalog_book($db,6)['available_copies']===1,'Expiry availability');
pickup_denied(fn()=>record_pickup($db,6,$token));
// An extra live allocation must block; expiry no longer consumes it.
$db->exec('UPDATE borrow_requests SET decided_at=UTC_TIMESTAMP() WHERE request_id=7'); pickup_denied(fn()=>record_pickup($db,3,$token));
$db->exec('UPDATE borrow_requests SET decided_at=UTC_TIMESTAMP()-INTERVAL 3 DAY WHERE request_id=7');
$db->exec("UPDATE loans SET checked_out_at=UTC_TIMESTAMP()-INTERVAL 8 DAY,due_date=DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)-INTERVAL 1 DAY WHERE loan_id=1"); pickup_denied(fn()=>record_pickup($db,3,$token));
$db->exec('UPDATE loans SET returned_at=UTC_TIMESTAMP(),returned_by=3 WHERE loan_id=1'); pickup_denied(fn()=>record_pickup($db,1,$token));
pickup_assert(count(list_pickups($db,1,'Fictional'))===6,'Queue excludes closed');
pickup_denied(fn()=>list_pickups($db,0));
$_SESSION['user_id']=2; pickup_assert(own_borrow_requests($db,1)===[],'Ownership'); pickup_denied(fn()=>list_pickups($db,1));
$_SESSION['user_id']=1; $history=own_borrow_requests($db,1); pickup_assert(count($history)===8,'Own history retained');
$own=array_column($history,null,'request_id');
pickup_assert($own[1]['checked_out_at']!==null && $own[1]['due_date']!==null,'Student pickup details');
pickup_assert((int)$own[1]['requested_days']===7 && (int)$own[1]['approved_days']===7 && $own[1]['request_reason']==='Fictional original reason','Original and approved terms preserved');
$_SESSION['user_id']=3;
$db->exec("UPDATE loans SET cancelled_at=UTC_TIMESTAMP(),cancellation_kind='librarian',cancelled_by=3,cancellation_note='Fixture cancellation' WHERE loan_id=7");
pickup_denied(fn()=>record_pickup($db,7,$token));
$db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,decision,decided_at,decided_by,decision_note) VALUES(50,2,1,7,'Fictional rejected request','rejected',UTC_TIMESTAMP(),3,'Fixture rejection')");
pickup_denied(fn()=>record_pickup($db,50,$token));
// Fixture-only uniqueness makes the required expiry audit fail after cancellation.
$db->exec('CREATE UNIQUE INDEX expiry_audit_failure ON audit_events(event_code,entity_id)');
$db->exec("INSERT INTO audit_events(actor_user_id,event_code,entity_type,entity_id,event_summary) VALUES(NULL,'loan.expired','loan',8,'Fixture')");
try { record_pickup($db,8,$token); throw new RuntimeException('Expiry audit failure expected'); } catch(PDOException) {}
pickup_assert($db->query('SELECT cancelled_at FROM loans WHERE loan_id=8')->fetchColumn()===null,'Expiry cleanup rollback');
$db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason) VALUES(51,1,3,7,'Fictional duplicate request')");
pickup_denied(fn()=>record_pickup($db,3,$token));
echo "Pickup date, authorization, fifth slot, expiry, archive, overdue, allowance, idempotency, ownership and rollback PASS.\n";
