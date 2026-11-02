<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/approved_cancellation.php';
require_once dirname(__DIR__).'/app/dashboard.php';
start_application_session();
function check_cancel(bool $ok,string $why): void { if(!$ok) throw new RuntimeException($why); }
function deny_cancel(callable $f): void { try {$f();} catch(InvalidArgumentException|DomainException) {return;} throw new RuntimeException('Expected refusal'); }
$db=database_connection(application_config()['database']);
$schema=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql'));
$schema=preg_replace('/SET SESSION sql_mode[^;]+;/','',$schema);
$schema=preg_replace('/^\s*CONSTRAINT fk_.*\n/m','',$schema);
$schema=preg_replace('/,\s*\)/',"\n)",$schema);
$schema=str_replace('CREATE TABLE ','CREATE TEMPORARY TABLE ',$schema);
foreach(explode(';',$schema) as $sql) if(trim($sql)!=='') $db->exec($sql);
require_once __DIR__.'/metadata_fixtures.php'; metadata_fixture_columns($db);
$db->exec("INSERT INTO users(user_id,username,display_name,role,account_status) VALUES(1,'cancel_owner','Owner','student','active'),(2,'cancel_other','Other','student','pending'),(3,'cancel_staff','Staff','librarian','active'),(4,'cancel_admin','Admin','administrator','active')");
for($i=1;$i<=9;$i++) {
$db->exec("INSERT INTO books(book_id,title,description) VALUES($i,'Fictional cancellation $i','Fixture')");
$db->exec("INSERT INTO book_copies(copy_id,book_id,copy_code) VALUES($i,$i,'CANCEL-$i')");
$db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,decision,approved_days,decided_at,decided_by) VALUES($i,1,$i,7,'Private original reason','approved',7,UTC_TIMESTAMP(),3)");
$db->exec("INSERT INTO loans(loan_id,request_id,copy_id) VALUES($i,$i,$i)");
}
$_SESSION['user_id']=1; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
deny_cancel(fn()=>cancel_approved_request($db,1,'bad'));
foreach([2,4] as $id) {$_SESSION['user_id']=$id; deny_cancel(fn()=>cancel_approved_request($db,1,$token));}
$_SESSION['user_id']=1; $_SESSION['session_version']=2; deny_cancel(fn()=>cancel_approved_request($db,1,$token)); $_SESSION['session_version']=1;
$countsBefore=dashboard_counts($db);
$original=$db->query('SELECT * FROM borrow_requests WHERE request_id=1')->fetch();
check_cancel(cancel_approved_request($db,1,$token)==='cancelled','Owner cancellation');
$first=$db->query('SELECT * FROM loans WHERE loan_id=1')->fetch();
check_cancel($first['cancellation_kind']==='student' && (int)$first['cancelled_by']===1,'Actor');
check_cancel(cancel_approved_request($db,1,$token)==='already_cancelled','Retry');
check_cancel($first===$db->query('SELECT * FROM loans WHERE loan_id=1')->fetch(),'Retry unchanged');
check_cancel($original===$db->query('SELECT * FROM borrow_requests WHERE request_id=1')->fetch(),'Approval preserved');
check_cancel((int)catalog_book($db,1)['available_copies']===1,'Availability released');
check_cancel(dashboard_counts($db)['awaiting']===$countsBefore['awaiting']-1,'Allowance/dashboard release');
$_SESSION['user_id']=3;
foreach(['',str_repeat('a',501),[]] as $note) deny_cancel(fn()=>cancel_approved_request($db,2,$token,$note));
$db->exec("UPDATE users SET account_status='suspended' WHERE user_id=1");
check_cancel(cancel_approved_request($db,2,$token,'Private cancellation explanation')==='cancelled','Suspended borrower librarian cancellation');
check_cancel((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_summary LIKE '%Private%'")->fetchColumn()===0,'No sensitive audit');
$db->exec('UPDATE books SET archived_at=UTC_TIMESTAMP() WHERE book_id=3');
$db->exec('UPDATE book_copies SET archived_at=UTC_TIMESTAMP() WHERE copy_id=3');
check_cancel(cancel_approved_request($db,3,$token,'Archive fixture')==='cancelled','Archived cancellation');
$db->exec('UPDATE borrow_requests SET requested_at=UTC_TIMESTAMP()-INTERVAL 4 DAY,decided_at=UTC_TIMESTAMP()-INTERVAL 3 DAY WHERE request_id=4');
check_cancel(cancel_approved_request($db,4,$token,'Expired fixture')==='expired','Expiry classification');
$db->exec('UPDATE loans SET checked_out_at=UTC_TIMESTAMP(),checked_out_by=3,due_date=DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)+INTERVAL 7 DAY WHERE loan_id=5');
deny_cancel(fn()=>cancel_approved_request($db,5,$token,'Collected fixture'));
$db->exec('CREATE UNIQUE INDEX cancel_audit_failure ON audit_events(event_code,entity_id)');
$db->exec("INSERT INTO audit_events(actor_user_id,event_code,entity_type,entity_id,event_summary) VALUES(3,'loan.cancelled','loan',6,'Fixture')");
try {cancel_approved_request($db,6,$token,'Rollback fixture'); throw new RuntimeException('Expected audit failure');} catch(PDOException) {}
check_cancel($db->query('SELECT cancelled_at FROM loans WHERE loan_id=6')->fetchColumn()===null,'Rollback');
$_SESSION['user_id']=1; $db->exec("UPDATE users SET account_status='pending' WHERE user_id=1");
check_cancel(cancel_approved_request($db,7,$token)==='cancelled','Pending owner allowed');
$own=array_column(own_borrow_requests($db,1),null,'request_id');
check_cancel($own[2]['cancellation_note']==='Private cancellation explanation','Own explanation');
$_SESSION['user_id']=2; check_cancel(own_borrow_requests($db,1)===[],'Ownership');
$_SESSION['user_id']=3;
$db->exec("UPDATE users SET account_status='active' WHERE user_id=1");
$db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason) VALUES(30,1,1,7,'Fictional pending cancellation')");
deny_cancel(fn()=>cancel_approved_request($db,30,$token,'Not approved'));
$db->exec("UPDATE borrow_requests SET decision='rejected',decided_at=UTC_TIMESTAMP(),decided_by=3,decision_note='Fixture' WHERE request_id=30");
deny_cancel(fn()=>cancel_approved_request($db,30,$token,'Rejected'));
$db->exec('UPDATE book_copies SET book_id=8 WHERE copy_id=9');
deny_cancel(fn()=>cancel_approved_request($db,9,$token,'Mismatched copy'));
$db->exec('UPDATE loans SET returned_at=UTC_TIMESTAMP(),returned_by=3 WHERE loan_id=5');
deny_cancel(fn()=>cancel_approved_request($db,5,$token,'Returned'));
$_SESSION['user_id']=1; $_SESSION['last_activity']=time()-1800;
deny_cancel(fn()=>cancel_approved_request($db,8,$token)); $_SESSION['last_activity']=time();
$db->exec("UPDATE users SET account_status='suspended' WHERE user_id=1"); deny_cancel(fn()=>cancel_approved_request($db,8,$token));
$db->exec("UPDATE users SET account_status='active' WHERE user_id=1");
$db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason) VALUES(31,1,1,7,'Fictional pending cancellation')");
cancel_borrow_request($db,31,$token); cancel_borrow_request($db,31,$token);
check_cancel($db->query('SELECT decision FROM borrow_requests WHERE request_id=31')->fetchColumn()==='cancelled','Pending remains functional');
check_cancel(count(own_borrow_requests($db,1,'awaiting_pickup'))===3,'Cancelled excluded from pickup tab');
require_once dirname(__DIR__).'/app/dashboard.php';
echo "Approved cancellation authorization, validation, preservation, availability, expiry, rollback and history PASS.\n";
