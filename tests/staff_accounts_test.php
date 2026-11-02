<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/staff_accounts.php';
start_application_session();
function staff_check(bool $ok,string $label):void { if(!$ok) throw new RuntimeException($label); }
function staff_refused(callable $fn):void { try {$fn();} catch(InvalidArgumentException|DomainException) {return;} throw new RuntimeException('Expected refusal'); }
$db=database_connection(application_config()['database']);
$sql=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql'));
$sql=preg_replace('/SET SESSION sql_mode[^;]+;/','',$sql); $sql=preg_replace('/^\s*CONSTRAINT fk_.*\n/m','',$sql); $sql=preg_replace('/,\s*\)/',"\n)",$sql); $sql=str_replace('CREATE TABLE ','CREATE TEMPORARY TABLE ',$sql);
foreach(explode(';',$sql) as $part) if(trim($part)!=='') $db->exec($part);
$hash=password_hash('Fictional fixture password',PASSWORD_DEFAULT);
$s=$db->prepare('INSERT INTO users(user_id,username,display_name,password_hash,role,account_status) VALUES(?,?,?,?,?,?)');
foreach([[1,'admin_one','Admin','administrator'],[2,'staff_one','Staff','librarian'],[3,'student_one','Student','student']] as [$id,$name,$label,$role]) $s->execute([$id,$name,$label,$hash,$role,'active']);
$_SESSION['user_id']=1; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
staff_refused(fn()=>staff_update($db,1,'librarian','active',1,$token));
staff_refused(fn()=>staff_create($db,'new_staff','Name','librarian','bad'));
foreach([2,3] as $id) {$_SESSION['user_id']=$id; staff_refused(fn()=>staff_create($db,'new_staff','Name','librarian',$token));} $_SESSION['user_id']=1;
$_SESSION['session_version']=2; staff_refused(fn()=>staff_list($db,1,'')); $_SESSION['user_id']=1; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
$id=staff_create($db,' NEW_STAFF ','<Fixture>','administrator',$token);
$row=$db->query('SELECT * FROM users WHERE user_id='.$id)->fetch(); staff_check($row['username']==='new_staff' && $row['password_hash']===null,'Disabled canonical staff');
staff_refused(fn()=>staff_create($db,'new_staff','Duplicate','librarian',$token));
staff_refused(fn()=>staff_create($db,'invalid!','Name','librarian',$token));
staff_refused(fn()=>staff_create($db,'test_student','Name','student',$token));
staff_refused(fn()=>staff_update($db,1,'librarian','active',1,$token));
staff_refused(fn()=>staff_update($db,3,'librarian','active',1,$token));
staff_check(staff_update($db,2,'librarian','suspended',1,$token)==='changed','Suspend');
staff_check(staff_update($db,2,'librarian','suspended',1,$token)==='unchanged','Idempotent retry');
staff_refused(fn()=>staff_update($db,2,'administrator','active',1,$token));
staff_check((int)$db->query('SELECT session_version FROM users WHERE user_id=2')->fetchColumn()===2,'Revocation');
staff_check(count(staff_list($db,1,'new_staff'))===1,'Search');
$db->exec('CREATE UNIQUE INDEX staff_audit_failure ON audit_events(event_code,entity_id)');
$db->exec("INSERT INTO audit_events(actor_user_id,event_code,entity_type,entity_id,event_summary) VALUES(1,'staff.updated','users',$id,'Fixture')");
try {staff_update($db,$id,'librarian','active',1,$token); throw new RuntimeException('Expected audit failure');} catch(PDOException) {}
staff_check($db->query('SELECT role FROM users WHERE user_id='.$id)->fetchColumn()==='administrator','Rollback');
staff_refused(fn()=>bootstrap_administrator($db,'bootstrap_admin','Fixture','Fictional bootstrap password'));
$db->exec("ALTER TABLE audit_events DROP INDEX staff_audit_failure");
$db->exec("INSERT INTO password_resets(user_id,token_hash,issued_by,expires_at) VALUES(2,UNHEX(REPEAT('ab',32)),1,UTC_TIMESTAMP()+INTERVAL 30 MINUTE)");
staff_update($db,2,'librarian','active',2,$token);
staff_check((int)$db->query('SELECT COUNT(*) FROM password_resets WHERE user_id=2')->fetchColumn()===0,'Reset revoked');
$_SESSION['last_activity']=time()-1800; staff_refused(fn()=>staff_list($db,1,'')); $_SESSION['last_activity']=time();
$db->exec("UPDATE users SET account_status='suspended' WHERE user_id=1"); staff_refused(fn()=>staff_create($db,'denied_staff','Name','librarian',$token));
$_SESSION['user_id']=1; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
staff_refused(fn()=>bootstrap_administrator($db,'staff_one','Duplicate','Fictional bootstrap secret'));
staff_refused(fn()=>bootstrap_administrator($db,'new_boot','Name','short'));
$newAdmin=bootstrap_administrator($db,'new_boot','New Admin','Fictional bootstrap secret');
$db->exec("UPDATE users SET account_status='active' WHERE user_id=1");
for($i=0;$i<22;$i++) {staff_create($db,'page_staff_'.$i,'Page fixture','librarian',$token);}
staff_check(count(staff_list($db,1,'page_staff_'))===21 && count(staff_list($db,2,'page_staff_'))===2,'Bounded pagination');
staff_check(staff_update($db,1,'librarian','active',1,$token)==='changed' && !isset($_SESSION['user_id']),'Safe self demotion logs out');echo "Staff policy, session, validation, last usable admin, idempotency, search and rollback PASS.\n";
