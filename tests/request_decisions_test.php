<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/request_decisions.php';
start_application_session();
function decision_assert(bool $ok, string $label): void { if (!$ok) { throw new RuntimeException($label); } }
function decision_denied(callable $fn): void { try { $fn(); } catch (InvalidArgumentException|DomainException) { return; } throw new RuntimeException('Expected denial'); }
decision_assert(decision_terms('7', ' Explanation ') === [7, 'Explanation'], 'Terms');
foreach ([0,31,[],true,'1.5'] as $value) { decision_denied(fn()=>decision_terms($value,'')); }
decision_denied(fn()=>decision_terms(7,str_repeat('x',501)));
$pdo=database_connection(application_config()['database']);
// All application tables are shadowed by connection-local fictional fixtures.
$schema=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql'));
$schema=preg_replace('/SET SESSION sql_mode[^;]+;/','',$schema);
$schema=preg_replace('/^\s*CONSTRAINT fk_.*\n/m','',$schema);
$schema=preg_replace('/,\s*\)/',"\n)",$schema);
$schema=str_replace('CREATE TABLE ','CREATE TEMPORARY TABLE ',$schema);
foreach(explode(';',$schema) as $sql) { if(trim($sql)!=='') { $pdo->exec($sql); } }
require_once __DIR__.'/metadata_fixtures.php'; metadata_fixture_columns($pdo);
$pdo->exec("INSERT INTO users(user_id,username,display_name,role,account_status) VALUES(1,'fixture_student','Student','student','active'),(2,'fixture_second','Other','student','active'),(3,'fixture_lib','Librarian','librarian','active'),(4,'fixture_admin','Admin','administrator','active')");
for($i=1;$i<=15;$i++) {
    $pdo->exec("INSERT INTO books(book_id,title,description) VALUES($i,'Fictional decision $i','Fixture')");
    $pdo->exec("INSERT INTO book_copies(copy_id,book_id,copy_code) VALUES($i,$i,'DECISION-$i')");
    $pdo->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason) VALUES($i,1,$i,7,'Private fictional reason')");
}
$_SESSION['user_id']=3; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
decision_denied(fn()=>decide_borrow_request($pdo,1,'approve',7,'','bad'));
foreach([1,4] as $actor) { $_SESSION['user_id']=$actor; decision_denied(fn()=>decide_borrow_request($pdo,1,'approve',7,'',$token)); }
$_SESSION['user_id']=3;
decision_denied(fn()=>decide_borrow_request($pdo,1,'approve',8,'',$token));
decision_denied(fn()=>decide_borrow_request($pdo,1,'reject',null,'',$token));
decide_borrow_request($pdo,1,'approve',8,'A fictional revised duration.',$token);
$row=$pdo->query('SELECT * FROM borrow_requests WHERE request_id=1')->fetch();
decision_assert((int)$row['requested_days']===7 && (int)$row['approved_days']===8 && $row['request_reason']==='Private fictional reason','Original terms retained');
decision_assert((int)catalog_book($pdo,1)['available_copies']===0,'Approval availability');
decision_assert($pdo->query('SELECT checked_out_at FROM loans WHERE request_id=1')->fetchColumn()===null,'No pickup');
decision_denied(fn()=>decide_borrow_request($pdo,1,'approve',7,'',$token));
decide_borrow_request($pdo,2,'reject',null,'Fictional rejection explanation.',$token);
decision_assert((int)catalog_book($pdo,2)['available_copies']===1,'Rejection availability');
$pdo->exec("UPDATE users SET account_status='suspended' WHERE user_id=1");
decision_denied(fn()=>decide_borrow_request($pdo,3,'approve',7,'',$token));
$pdo->exec("UPDATE users SET account_status='active' WHERE user_id=1");
$pdo->exec('UPDATE books SET archived_at=UTC_TIMESTAMP() WHERE book_id=3');
decision_denied(fn()=>decide_borrow_request($pdo,3,'approve',7,'',$token));
$pdo->exec('UPDATE book_copies SET archived_at=UTC_TIMESTAMP() WHERE copy_id=4');
decision_denied(fn()=>decide_borrow_request($pdo,4,'approve',7,'',$token));
// Audit failure must undo both the decision and allocation.
$pdo->exec('CREATE UNIQUE INDEX decision_audit_failure ON audit_events(event_code,actor_user_id,entity_id)');
$pdo->exec("INSERT INTO audit_events(actor_user_id,event_code,entity_type,entity_id,event_summary) VALUES(3,'request.approved','borrow_request',5,'Fixture')");
try { decide_borrow_request($pdo,5,'approve',7,'',$token); throw new RuntimeException('Expected audit failure'); } catch(PDOException) {}
decision_assert($pdo->query('SELECT decision FROM borrow_requests WHERE request_id=5')->fetchColumn()==='pending','Decision rollback');
decision_assert((int)$pdo->query('SELECT COUNT(*) FROM loans WHERE request_id=5')->fetchColumn()===0,'Allocation rollback');
$_SESSION['session_version']=99;
decision_denied(fn()=>decide_borrow_request($pdo,6,'approve',7,'',$token));
$_SESSION['session_version']=1; $_SESSION['last_activity']=time()-1801;
decision_denied(fn()=>decide_borrow_request($pdo,6,'approve',7,'',$token));
$_SESSION['last_activity']=time();
// An expired allocation still owns the unique copy key until released atomically.
$pdo->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,requested_at,decision,approved_days,decided_at,decided_by) VALUES(100,2,6,7,'Fictional expired reason',UTC_TIMESTAMP()-INTERVAL 4 DAY,'approved',7,UTC_TIMESTAMP()-INTERVAL 3 DAY,3)");
$pdo->exec('INSERT INTO loans(request_id,copy_id) VALUES(100,6)');
$pdo->exec("INSERT INTO audit_events(actor_user_id,event_code,entity_type,entity_id,event_summary) VALUES(3,'request.approved','borrow_request',6,'Fixture rollback fault')");
try { decide_borrow_request($pdo,6,'approve',7,'',$token); throw new RuntimeException('Expected expiry audit failure'); } catch(PDOException) {}
decision_assert($pdo->query('SELECT cancellation_kind FROM loans WHERE request_id=100')->fetchColumn()===null,'Expiry release rollback');
decision_assert($pdo->query('SELECT decision FROM borrow_requests WHERE request_id=6')->fetchColumn()==='pending','Expiry decision rollback');
decision_assert((int)$pdo->query("SELECT COUNT(*) FROM audit_events WHERE event_code='loan.expired'")->fetchColumn()===0,'Expiry audit rollback');
$pdo->exec("UPDATE audit_events SET event_code='fixture.fault.finished' WHERE event_code='request.approved' AND entity_id=6");
decide_borrow_request($pdo,6,'approve',7,'',$token);
decision_assert($pdo->query('SELECT cancellation_kind FROM loans WHERE request_id=100')->fetchColumn()==='expired','Expiry cleanup');
decision_assert((int)$pdo->query("SELECT COUNT(*) FROM audit_events WHERE event_code='loan.expired'")->fetchColumn()===1,'Expiry audit');
// Overdue loan blocks all new approvals, then return frees eligibility.
$pdo->exec("UPDATE loans SET checked_out_at=UTC_TIMESTAMP()-INTERVAL 8 DAY,checked_out_by=3,due_date=DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)-INTERVAL 1 DAY WHERE request_id=1");
decision_denied(fn()=>decide_borrow_request($pdo,7,'approve',7,'',$token));
$pdo->exec('UPDATE loans SET returned_at=UTC_TIMESTAMP(),returned_by=3 WHERE request_id=1');
foreach([7,8,9,10] as $id) { decide_borrow_request($pdo,$id,'approve',7,'',$token); }
decision_denied(fn()=>decide_borrow_request($pdo,11,'approve',7,'',$token));
// Live duplicate title remains forbidden even with another available physical copy.
$pdo->exec("INSERT INTO book_copies(book_id,copy_code) VALUES(6,'DECISION-EXTRA')");
$pdo->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason) VALUES(101,1,6,7,'Fictional duplicate request')");
decision_denied(fn()=>decide_borrow_request($pdo,101,'approve',7,'',$token));
$_SESSION['user_id']=1;
cancel_borrow_request($pdo,12,$token);
$_SESSION['user_id']=3;
decision_denied(fn()=>decide_borrow_request($pdo,12,'reject',null,'Fictional rejection',$token));
decision_assert(count(list_review_requests($pdo,1,'Fictional'))>0,'Queue search');
decision_assert(list_review_requests($pdo,2,'No matching fixture')===[],'Empty queue');
decision_denied(fn()=>list_review_requests($pdo,0));
$_SESSION['user_id']=2; decision_denied(fn()=>list_review_requests($pdo,1));
decision_assert(count(own_borrow_requests($pdo,1))===1,'Own history only');
$pdo->exec("UPDATE users SET account_status='pending' WHERE user_id=2");
decision_assert(count(own_borrow_requests($pdo,1))===1,'Pending history allowed');
decision_assert((int)$pdo->query("SELECT COUNT(*) FROM audit_events WHERE event_summary LIKE '%Private fictional%' OR event_summary LIKE '%revised duration%'")->fetchColumn()===0,'Sensitive text omitted');
echo "Decision validation, authorization, original terms, availability, stale decisions, archive guards and atomic rollback PASS.\n";
