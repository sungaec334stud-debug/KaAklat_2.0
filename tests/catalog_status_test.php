<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog.php';
require_once dirname(__DIR__) . '/views/catalog_status.php';

function catalog_status_check(bool $ok, string $label): void
{
    if (!$ok) { throw new RuntimeException($label); }
}

$db = database_connection(application_config()['database']);
// Connection-local temporary tables; no demo records are changed.
$db->exec('CREATE TEMPORARY TABLE users(user_id INT PRIMARY KEY,username VARCHAR(30),display_name VARCHAR(50),role VARCHAR(20),account_status VARCHAR(20),session_version INT)');
$db->exec("INSERT INTO users VALUES(1,'one','Fictional One','student','active',1),(2,'two','Fictional Two','student','active',1),(3,'lib','Fictional Librarian','librarian','active',1),(4,'admin','Fictional Admin','administrator','active',1)");
$db->exec('CREATE TEMPORARY TABLE borrow_requests(request_id INT PRIMARY KEY,book_id INT,student_id INT,decision VARCHAR(20),decided_at DATETIME)');
$db->exec('CREATE TEMPORARY TABLE loans(request_id INT,checked_out_at DATETIME,due_date DATE,returned_at DATETIME,cancelled_at DATETIME)');
foreach ([1=>'pending',2=>'approved',3=>'approved',4=>'approved',5=>'approved',6=>'approved',7=>'approved',8=>'approved',9=>'rejected',10=>'cancelled',11=>'pending',12=>'approved'] as $id=>$decision) {
    $db->prepare('INSERT INTO borrow_requests VALUES(?,?,?,?,UTC_TIMESTAMP())')->execute([$id,10+$id,$id>=11?2:1,$decision]);
}
$db->exec("INSERT INTO loans VALUES(2,NULL,NULL,NULL,NULL),(3,UTC_TIMESTAMP(),DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)+INTERVAL 1 DAY,NULL,NULL),(4,UTC_TIMESTAMP(),DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)-INTERVAL 1 DAY,NULL,NULL),(5,UTC_TIMESTAMP(),DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR),NULL,NULL),(6,UTC_TIMESTAMP(),CURRENT_DATE(),UTC_TIMESTAMP(),NULL),(7,NULL,NULL,NULL,UTC_TIMESTAMP()),(8,NULL,NULL,NULL,NULL),(12,UTC_TIMESTAMP(),DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)-INTERVAL 1 DAY,NULL,NULL)");
$db->exec('UPDATE borrow_requests SET decided_at=UTC_TIMESTAMP()-INTERVAL 48 HOUR WHERE request_id=8');
$_SESSION = ['user_id'=>1,'session_version'=>1,'last_activity'=>time()];
$ids = range(11,22);
catalog_status_check(catalog_own_request_states($db,$ids)===[11=>'pending',12=>'awaiting pickup',13=>'active',14=>'overdue',15=>'active'], 'Own live states; today is active; closed and exact-expiry excluded');
catalog_status_check(catalog_own_request_states($db,[11])===[11=>'pending'], 'Only visible book IDs queried');
catalog_status_check(catalog_own_request_states($db,[])===[], 'Empty page');
$_SESSION['role']='administrator';
catalog_status_check(catalog_own_request_states($db,[11])===[11=>'pending'], 'Forged session role does not change database identity');
$_SESSION['user_id']=2;
catalog_status_check(catalog_own_request_states($db,$ids)===[21=>'pending',22=>'overdue'], 'Another student gets only own records');
foreach ([3,4] as $userId) {
    $_SESSION['user_id']=$userId;
    catalog_status_check(catalog_own_request_states($db,$ids)===[], 'Staff never receive student badges');
}
$_SESSION=['user_id'=>1,'session_version'=>1,'last_activity'=>time()];
$db->exec("UPDATE users SET account_status='pending' WHERE user_id=1");
catalog_status_check(catalog_own_request_states($db,[11])===[11=>'pending'], 'Pending student can see own record');
$db->exec("UPDATE users SET account_status='suspended' WHERE user_id=1");
catalog_status_check(catalog_own_request_states($db,$ids)===[], 'Suspended identity fails closed');
$_SESSION=['user_id'=>2,'session_version'=>99,'last_activity'=>time()];
catalog_status_check(catalog_own_request_states($db,$ids)===[], 'Stale session fails closed');
foreach (['pending','awaiting pickup','active','overdue'] as $state) {
    catalog_status_check(str_contains(catalog_request_status_html($state), 'Your '), 'Readable personal label');
}
catalog_status_check(catalog_request_status_html(null)==='' && catalog_request_status_html('<script>')==='', 'No badge for absent or unknown state');
echo "PASS: Catalog own-status lifecycle, ownership, current identity and safe label checks.\n";
