<?php
 declare(strict_types=1);
 require_once dirname(__DIR__).'/app/bootstrap.php';
 require_once dirname(__DIR__).'/app/returns.php';
 start_application_session();
 function return_assert(bool $ok,string $label): void { if(!$ok) { throw new RuntimeException($label); } }
 function return_denied(callable $fn): void { try { $fn(); } catch(InvalidArgumentException|DomainException) { return; } throw new RuntimeException('Expected denial'); }
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

$db->exec('UPDATE loans SET checked_out_at=UTC_TIMESTAMP()-INTERVAL 8 DAY,checked_out_by=3,due_date=DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)-INTERVAL 1 DAY WHERE loan_id<=5');
$_SESSION['user_id']=3; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
return_denied(fn()=>record_return($db,1,'bad'));
foreach([1,4] as $id) { $_SESSION['user_id']=$id; return_denied(fn()=>record_return($db,1,$token)); }
$_SESSION['user_id']=3; $_SESSION['session_version']=2; return_denied(fn()=>record_return($db,1,$token)); $_SESSION['session_version']=1;
$db->exec("UPDATE users SET account_status='suspended' WHERE user_id=3"); return_denied(fn()=>record_return($db,1,$token)); $db->exec("UPDATE users SET account_status='active' WHERE user_id=3");
$_SESSION['last_activity']=time()-1801; return_denied(fn()=>record_return($db,1,$token)); $_SESSION['last_activity']=time();
$before=$db->query('SELECT * FROM loans WHERE loan_id=1')->fetch();
return_assert(record_return($db,1,$token)==='returned','Return overdue');
$after=$db->query('SELECT * FROM loans WHERE loan_id=1')->fetch();
return_assert($after['checked_out_at']===$before['checked_out_at'] && $after['due_date']===$before['due_date'],'Original dates');
return_assert(record_return($db,1,$token)==='already_returned','Repeated return');
return_assert($after===$db->query('SELECT * FROM loans WHERE loan_id=1')->fetch(),'Idempotent dates');
return_assert((int)catalog_book($db,1)['available_copies']===1,'Released availability');
return_assert((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='loan.returned'")->fetchColumn()===1,'One audit');
$db->exec("UPDATE users SET account_status='suspended' WHERE user_id=1");
$db->exec('UPDATE books SET archived_at=UTC_TIMESTAMP() WHERE book_id=2');
$db->exec('UPDATE book_copies SET archived_at=UTC_TIMESTAMP() WHERE copy_id=2');
return_assert(record_return($db,2,$token)==='returned','Inactive borrower archived copy allowed');
return_denied(fn()=>record_return($db,6,$token));
$db->exec("UPDATE loans SET cancelled_at=UTC_TIMESTAMP(),cancellation_kind='expired' WHERE loan_id=7"); return_denied(fn()=>record_return($db,7,$token));
return_denied(fn()=>record_return($db,999,$token));
$db->exec('CREATE UNIQUE INDEX return_audit_failure ON audit_events(event_code,entity_id)');
$db->exec("INSERT INTO audit_events(actor_user_id,event_code,entity_type,entity_id,event_summary) VALUES(3,'loan.returned','loan',3,'Fixture')");
try { record_return($db,3,$token); throw new RuntimeException('Audit failure expected'); } catch(PDOException) {}
return_assert($db->query('SELECT returned_at FROM loans WHERE loan_id=3')->fetchColumn()===null,'Return rollback');
return_assert((int)catalog_book($db,3)['available_copies']===0,'Rollback retains allocation');
return_assert(count(list_returns($db,1))===3,'Outstanding excludes closed/uncollected');
return_assert((int)list_returns($db,1)[0]['overdue']===1,'Overdue flag');
return_assert(list_returns($db,1,'no such fictional title')===[],'Search empty');
return_assert(count(list_returns($db,1,'PICKUP-3'))===1,'Copy code search');
return_denied(fn()=>list_returns($db,0));
$_SESSION['user_id']=4; return_denied(fn()=>list_returns($db,1));
$db->exec("UPDATE users SET account_status='active' WHERE user_id=1");
$_SESSION['user_id']=1; $history=array_column(own_borrow_requests($db,1),null,'request_id');
return_assert($history[1]['returned_at']!==null && $history[1]['lifecycle']==='returned','Student visible return');
return_assert(count(own_borrow_requests($db,1,'returned'))===2,'Returned tab excludes outstanding loans');
return_assert((int)$history[1]['approved_days']===7 && (int)$history[1]['requested_days']===7,'Terms preserved');
$_SESSION['user_id']=2; return_assert(own_borrow_requests($db,1)===[],'Other student history isolated'); return_assert(own_borrow_requests($db,1,'returned')===[],'Returned tab ownership');
$_SESSION['user_id']=3;
$db->exec("UPDATE users SET account_status='archived' WHERE user_id=1");
return_assert(record_return($db,4,$token)==='returned','Archived borrower permitted');
$db->exec("UPDATE users SET account_status='pending' WHERE user_id=1");
return_assert(record_return($db,5,$token)==='returned','Pending borrower permitted');
$db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,decision,decided_at,decided_by,decision_note) VALUES(99,2,1,7,'Fictional rejected reason','rejected',UTC_TIMESTAMP(),3,'Fixture rejection')");
return_denied(fn()=>record_return($db,99,$token));
return_assert((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_summary LIKE '%Fictional original reason%'")->fetchColumn()===0,'No sensitive reasons in audit');
require_once dirname(__DIR__).'/app/dashboard.php';
$db->exec("UPDATE users SET account_status='active' WHERE user_id=1");
$_SESSION['user_id']=1;
return_assert(dashboard_counts($db)['active']===1,'Returned loans leave dashboard count');
return_assert(catalog_book($db,2)===null,'Archived title remains hidden after return');
return_assert($db->query('SELECT active_copy_id FROM loans WHERE loan_id=2')->fetchColumn()===null,'Archived return releases allocation');
$_SESSION['user_id']=3;
for($i=100;$i<122;$i++) {
    $db->exec("INSERT INTO book_copies(copy_id,book_id,copy_code) VALUES($i,1,'RETURN-PAGE-$i')");
    $db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,decision,approved_days,decided_at,decided_by) VALUES($i,2,1,7,'Fictional pagination fixture','approved',7,UTC_TIMESTAMP(),3)");
    $db->exec("INSERT INTO loans(request_id,copy_id,checked_out_at,checked_out_by,due_date) VALUES($i,$i,UTC_TIMESTAMP(),3,DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)+INTERVAL 7 DAY)");
}
return_assert(count(list_returns($db,1,'RETURN-PAGE-'))===21,'Bounded page fetch ahead');
return_assert(count(list_returns($db,2,'RETURN-PAGE-'))===2,'Filtered second page');
return_assert(list_returns($db,3,'RETURN-PAGE-')===[],'Filtered end of pages');
echo "Return authorization, eligibility, idempotency, history, availability, queue and rollback PASS.\n";
