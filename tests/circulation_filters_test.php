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
require_once dirname(__DIR__).'/app/circulation_filters.php';
require_once dirname(__DIR__).'/app/pickups.php';
$db->exec("INSERT INTO users(user_id,username,display_name,role,account_status) VALUES(1,'filter_student','Fictional Student','student','active'),(3,'filter_librarian','Fictional Librarian','librarian','active'),(4,'filter_admin','Fictional Admin','administrator','active')");
$_SESSION=['user_id'=>3,'session_version'=>1,'last_activity'=>time()];
$checks=0;function filter_check(bool $ok,string $label):void{global $checks;if(!$ok)throw new RuntimeException($label);$checks++;echo "PASS: $label\n";}
function filter_denied(callable $fn,string $label):void{try{$fn();}catch(InvalidArgumentException|DomainException){filter_check(true,$label);return;}throw new RuntimeException('Expected denial: '.$label);}
for($i=1;$i<=34;$i++){
 $db->exec("INSERT INTO books(book_id,title,description) VALUES($i,'Queue $i','Fictional filter fixture')");$db->exec("INSERT INTO book_copies(copy_id,book_id,copy_code) VALUES($i,$i,'QUEUE-$i')");
 if($i<=25){$days=($i%5)+1;$db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,requested_at) VALUES($i,1,$i,$days,'Fictional pending request',UTC_TIMESTAMP()-INTERVAL $i MINUTE)");}
 else{$db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,requested_at,decision,approved_days,decided_at,decided_by) VALUES($i,1,$i,30,'Fictional approved request',UTC_TIMESTAMP()-INTERVAL 20 DAY,'approved',30,UTC_TIMESTAMP()-INTERVAL 11 DAY,3)");}
}
foreach([26=>49,27=>48,28=>47,29=>0,30=>49]as$id=>$hours){$db->exec("UPDATE borrow_requests SET decided_at=UTC_TIMESTAMP()-INTERVAL $hours HOUR WHERE request_id=$id");$db->exec("INSERT INTO loans(loan_id,request_id,copy_id) VALUES($id,$id,$id)");}
$db->exec("UPDATE loans SET cancelled_at=UTC_TIMESTAMP(),cancellation_kind='expired' WHERE loan_id=30");
foreach([31=>-1,32=>0,33=>2,34=>1]as$id=>$offset){$db->exec("INSERT INTO loans(loan_id,request_id,copy_id,checked_out_at,checked_out_by,due_date) VALUES($id,$id,$id,UTC_TIMESTAMP()-INTERVAL 10 DAY,3,DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)+INTERVAL $offset DAY)");}
$db->exec('UPDATE loans SET returned_at=UTC_TIMESTAMP(),returned_by=3 WHERE loan_id=34');
foreach(['review','pickups','returns']as$kind){$options=circulation_list_options($kind,[]);filter_check($options['status']==='all','Default status '.$kind);filter_denied(fn()=>circulation_list_options($kind,['sort'=>'bad; DROP']), 'Forged sort rejected '.$kind);filter_denied(fn()=>circulation_list_options($kind,['sort'=>[]]),'Array sort rejected '.$kind);}
$rows=list_review_requests($db,1,'',$page,circulation_list_options('review',[]));filter_check((int)$rows[0]['request_id']===25&&$page['total']===25,'Default oldest order and total');
$rows=list_review_requests($db,1,'',$page,['sort'=>'newest']);filter_check((int)$rows[0]['request_id']===1,'Newest order');
foreach(['shortest'=>1,'longest'=>5]as$sort=>$days){$rows=list_review_requests($db,1,'',$page,['sort'=>$sort]);filter_check((int)$rows[0]['requested_days']===$days,'Requested length order '.$sort);}
$rows=list_review_requests($db,2,'',$page,['sort'=>'newest']);filter_check(count($rows)===5&&$page['page']===2,'Sorted pagination');
$rows=list_review_requests($db,999,'Queue 1',$page,['sort'=>'newest']);filter_check($page['page']===1&&$page['total']===11,'Combined search and page clamp');
foreach(['all'=>4,'ready'=>2,'expired'=>2]as$status=>$count){$rows=list_pickups($db,1,'',$page,['status'=>$status]);filter_check(count($rows)===$count&&$page['total']===$count,'Pickup status '.$status);}
$rows=list_pickups($db,1,'',$page,['status'=>'ready','sort'=>'latest']);filter_check((int)$rows[0]['request_id']===29,'Pickup latest deadline order');
$rows=list_pickups($db,1,'Queue 28',$page,['status'=>'expired']);filter_check($rows===[]&&$page['total']===0,'Pickup search/status combination');
foreach(['all'=>3,'overdue'=>1,'due_today'=>1,'active'=>2]as$status=>$count){$rows=list_returns($db,1,'',$page,['status'=>$status]);filter_check(count($rows)===$count&&$page['total']===$count,'Return status '.$status);}
$rows=list_returns($db,1,'',$page,['status'=>'all','sort'=>'latest']);filter_check((int)$rows[0]['request_id']===33,'Latest due order');
$rows=list_returns($db,1,'QUEUE-32',$page,['status'=>'active']);filter_check(count($rows)===1&&(bool)$rows[0]['due_today'],'Copy search and due-today flag');
filter_denied(fn()=>list_returns($db,1,'',$page,['status'=>['overdue']]),'Malformed service status rejected');
filter_denied(fn()=>list_pickups($db,1,'',$page,['status'=>'cancelled']),'Closed pickup history filter rejected');
foreach([1,4]as$actor){$_SESSION['user_id']=$actor;filter_denied(fn()=>list_review_requests($db,1,'',$page,['sort'=>'newest']),'Unauthorized review denied');filter_denied(fn()=>list_pickups($db,1,'',$page,['status'=>'expired']),'Unauthorized pickups denied');filter_denied(fn()=>list_returns($db,1,'',$page,['status'=>'overdue']),'Unauthorized returns denied');}
echo "$checks circulation filter checks passed using temporary tables only.\n";
