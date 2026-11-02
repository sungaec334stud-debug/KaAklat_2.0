<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/recovery.php';
require_once dirname(__DIR__).'/app/audit.php';
require_once dirname(__DIR__).'/app/student_accounts.php';
start_application_session();
function access_check(bool $ok,string $why):void {if(!$ok) throw new RuntimeException($why);}
function access_denied(callable $f):void {try{$f();}catch(InvalidArgumentException|DomainException){return;} throw new RuntimeException('Expected denial');}
$db=database_connection(application_config()['database']);
$sql=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql')); $sql=preg_replace('/SET SESSION sql_mode[^;]+;/','',$sql); $sql=preg_replace('/^\s*CONSTRAINT fk_.*\n/m','',$sql); $sql=preg_replace('/,\s*\)/',"\n)",$sql); $sql=str_replace('CREATE TABLE ','CREATE TEMPORARY TABLE ',$sql);
foreach(explode(';',$sql) as $part) if(trim($part)!=='') $db->exec($part);
$hash=password_hash('Fictional original password',PASSWORD_DEFAULT);
$s=$db->prepare('INSERT INTO users(user_id,username,display_name,role,account_status,password_hash) VALUES(?,?,?,?,?,?)');
foreach([[1,'access_admin','Admin','administrator','active'],[2,'access_lib','Lib','librarian','active'],[3,'access_student','Student','student','pending'],[4,'access_suspended','Suspended','student','suspended']] as [$id,$name,$display,$role,$status]) $s->execute([$id,$name,$display,$role,$status,$hash]);
$_SESSION=['user_id'=>1,'session_version'=>1,'last_activity'=>time()]; $token=csrf_token();
change_student_status($db,3,'activate',$token); access_check($db->query('SELECT account_status FROM users WHERE user_id=3')->fetchColumn()==='active','Admin student activation');
access_denied(fn()=>issue_recovery($db,'access_student','bad',true));
access_denied(fn()=>issue_recovery($db,'access_student',$token,false));
$secret=issue_recovery($db,'access_student',$token,true);
$row=$db->query('SELECT * FROM password_resets WHERE user_id=3')->fetch(); access_check($row['token_hash']===hash('sha256',hex2bin($secret),true),'Only digest stored');
$replacement=issue_recovery($db,'access_student',$token,true); access_denied(fn()=>redeem_recovery($db,$secret,'Fictional new password',$token));
redeem_recovery($db,$replacement,'Fictional new password',$token);
access_check(password_verify('Fictional new password',$db->query('SELECT password_hash FROM users WHERE user_id=3')->fetchColumn()),'New password');
access_check((int)$db->query('SELECT session_version FROM users WHERE user_id=3')->fetchColumn()===3,'Sessions revoked');
$token=csrf_token(); access_denied(fn()=>redeem_recovery($db,$replacement,'Fictional replay password',$token));
$_SESSION=['user_id'=>2,'session_version'=>1,'last_activity'=>time()]; $token=csrf_token();
access_denied(fn()=>issue_recovery($db,'access_admin',$token,true));
$suspendedSecret=issue_recovery($db,'access_suspended',$token,true);
redeem_recovery($db,$suspendedSecret,'Fictional suspended password',$token);
access_check($db->query('SELECT account_status FROM users WHERE user_id=4')->fetchColumn()==='suspended','Status preserved');
$_SESSION=['user_id'=>1,'session_version'=>1,'last_activity'=>time()]; $token=csrf_token();
$expired=issue_recovery($db,'access_student',$token,true);
$db->exec('UPDATE password_resets SET issued_at=UTC_TIMESTAMP()-INTERVAL 31 MINUTE,expires_at=UTC_TIMESTAMP()-INTERVAL 1 MINUTE WHERE user_id=3');
access_denied(fn()=>redeem_recovery($db,$expired,'Fictional expiry password',$token));
$valid=issue_recovery($db,'access_student',$token,true);
$db->exec("ALTER TABLE audit_events ADD failure_key BIGINT GENERATED ALWAYS AS (CASE WHEN event_code='account.reset_redeemed' THEN entity_id ELSE NULL END) STORED, ADD UNIQUE INDEX access_audit_failure(failure_key)");
$before=$db->query('SELECT * FROM users WHERE user_id=3')->fetch();
try {redeem_recovery($db,$valid,'Fictional rollback password',$token); throw new RuntimeException('Audit failure expected');} catch(PDOException) {}
access_check($before===$db->query('SELECT * FROM users WHERE user_id=3')->fetch() && (int)$db->query('SELECT COUNT(*) FROM password_resets WHERE user_id=3')->fetchColumn()===1,'Redemption rollback');
$events=list_audit_events($db,1,'',''); access_check(count($events)>0 && !array_key_exists('event_summary',$events[0]),'Audit safe metadata');
$_SESSION=['user_id'=>2,'session_version'=>1,'last_activity'=>time()]; access_denied(fn()=>list_audit_events($db,1,'',''));
$_SESSION=['user_id'=>3,'session_version'=>3,'last_activity'=>time()]; $token=csrf_token(); access_denied(fn()=>issue_recovery($db,'access_student',$token,true));
$_SESSION=['user_id'=>1,'session_version'=>2,'last_activity'=>time()]; access_denied(fn()=>issue_recovery($db,'access_student',$token,true));
$_SESSION=['user_id'=>1,'session_version'=>1,'last_activity'=>time()]; $token=csrf_token();
$db->exec("ALTER TABLE audit_events ADD issue_failure_key BIGINT GENERATED ALWAYS AS (CASE WHEN event_code='account.reset_issued' AND entity_id=4 THEN entity_id ELSE NULL END) STORED, ADD UNIQUE INDEX issue_audit_failure(issue_failure_key)");
$before=$db->query('SELECT * FROM password_resets WHERE user_id=4')->fetch();
try {issue_recovery($db,'access_suspended',$token,true); throw new RuntimeException('Issue audit failure expected');} catch(PDOException) {}
access_check($before===$db->query('SELECT * FROM password_resets WHERE user_id=4')->fetch(),'Issuance rollback');
for($i=0;$i<28;$i++) record_audit_event($db,1,'test.pagination','users',1,'Fictional metadata');
access_check(count(list_audit_events($db,1,'test.pagination','access_admin'))===26 && count(list_audit_events($db,2,'test.pagination','access_admin'))===3,'Bounded audit pagination/filter');
access_denied(fn()=>list_audit_events($db,0,'',''));
access_denied(fn()=>redeem_recovery($db,[], 'Fictional invalid password',$token));echo "Admin student access, recovery role/CSRF/identity/hash/expiry/replay/status/rollback and audit privacy PASS.\n";
