<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/dashboard.php';
start_application_session();
$db=database_connection(application_config()['database']);
$schema=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql'));
$schema=preg_replace('/SET SESSION sql_mode[^;]+;/','',$schema);
$schema=preg_replace('/^\s*CONSTRAINT fk_.*\n/m','',$schema);
$schema=preg_replace('/,\s*\)/',"\n)",$schema);
$schema=str_replace('CREATE TABLE ','CREATE TEMPORARY TABLE ',$schema);
foreach(explode(';',$schema) as $sql) { if(trim($sql)!=='') { $db->exec($sql); } }
$db->exec("INSERT INTO users(user_id,username,display_name,role,account_status) VALUES(1,'dash_one','One','student','active'),(2,'dash_two','Two','student','pending'),(3,'dash_staff','Staff','librarian','active'),(4,'dash_admin','Admin','administrator','active')");
$db->exec("INSERT INTO books(book_id,title,description) VALUES(1,'Fictional dashboard title','Fixture')");
for($i=1;$i<=5;$i++) {
    $db->exec("INSERT INTO book_copies(copy_id,book_id,copy_code) VALUES($i,1,'DASH-$i')");
    $db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,decision,approved_days,requested_at,decided_at,decided_by) VALUES($i,1,1,7,'Fictional count fixture','approved',7,UTC_TIMESTAMP()-INTERVAL 20 DAY,UTC_TIMESTAMP(),3)");
    $db->exec("INSERT INTO loans(loan_id,request_id,copy_id) VALUES($i,$i,$i)");
}
$db->exec('UPDATE borrow_requests SET decided_at=UTC_TIMESTAMP()-INTERVAL 48 HOUR WHERE request_id=2');
$db->exec('UPDATE loans SET checked_out_at=UTC_TIMESTAMP()-INTERVAL 8 DAY,checked_out_by=3,due_date=DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)-INTERVAL 1 DAY WHERE loan_id=3');
$db->exec('UPDATE loans SET checked_out_at=UTC_TIMESTAMP(),checked_out_by=3,due_date=DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)+INTERVAL 7 DAY,returned_at=UTC_TIMESTAMP(),returned_by=3 WHERE loan_id=4');
$db->exec("UPDATE loans SET cancelled_at=UTC_TIMESTAMP(),cancellation_kind='expired' WHERE loan_id=5");
$db->exec("INSERT INTO borrow_requests(student_id,book_id,requested_days,request_reason) VALUES(1,1,7,'Fictional pending count'),(2,1,7,'Other pending count')");
function count_check(bool $ok,string $message): void { if(!$ok) { throw new RuntimeException($message); } }
$_SESSION['user_id']=1; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $_SESSION['role']='librarian';
count_check(dashboard_counts($db)===['pending'=>1,'awaiting'=>1,'active'=>1,'overdue'=>1],'Student scope and expiry');
$_SESSION['user_id']=2;
count_check(dashboard_counts($db)===['pending'=>1,'awaiting'=>0,'active'=>0,'overdue'=>0],'Pending student own scope');
$_SESSION['user_id']=3;
count_check(dashboard_counts($db)===['pending'=>2,'awaiting'=>1,'active'=>1,'overdue'=>1],'Librarian global scope');
foreach([4,1] as $id) {
    $_SESSION['user_id']=$id;
    if($id===1) { $db->exec("UPDATE users SET account_status='suspended' WHERE user_id=1"); }
    try { dashboard_counts($db); throw new RuntimeException('Access should be denied'); } catch(DomainException) {}
}
echo "Dashboard role/ownership, pending access, expired/closed allocation and overdue counts PASS.\n";
