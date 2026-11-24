<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require_once dirname(__DIR__).'/app/bootstrap.php';
$fixture=json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR);
if(!preg_match('/^kaaklat_acceptance_[a-f0-9]{12}$/D',$fixture['database']))throw new RuntimeException('Fictional fixture required');
$config=application_config()['database'];$config['database']=$fixture['database'];$db=database_connection($config);
for($n=0;$n<31;$n++){
 $id=14000+$n;$db->prepare("INSERT INTO books(book_id,title,description,created_at) VALUES(?,?,?,'2026-01-01 00:00:00')")->execute([$id,'Queue Browser '.sprintf('%02d',$n),'Fictional browser queue fixture']);
 $db->exec("INSERT INTO book_copies(book_id,copy_code,created_at) VALUES($id,'QUEUEBROWSER-$id','2026-01-01 00:00:00')");$copy=(int)$db->lastInsertId();
 if($n<24){$days=($n%6)+1;$db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,requested_at) VALUES($id,1,$id,$days,'Fictional browser filter request',UTC_TIMESTAMP()-INTERVAL $n MINUTE)");}
 else{
  $db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,requested_at,decision,approved_days,decided_at,decided_by) VALUES($id,1,$id,30,'Fictional browser filter allocation',UTC_TIMESTAMP()-INTERVAL 20 DAY,'approved',30,UTC_TIMESTAMP()-INTERVAL 11 DAY,3)");
  if($n<28){$hours=[24=>49,25=>48,26=>47,27=>0][$n];$db->exec("UPDATE borrow_requests SET decided_at=UTC_TIMESTAMP()-INTERVAL $hours HOUR WHERE request_id=$id");$db->exec("INSERT INTO loans(request_id,copy_id) VALUES($id,$copy)");}
  else{$offset=[28=>-1,29=>0,30=>2][$n];$db->exec("INSERT INTO loans(request_id,copy_id,checked_out_at,checked_out_by,due_date) VALUES($id,$copy,UTC_TIMESTAMP()-INTERVAL 10 DAY,3,DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)+INTERVAL $offset DAY)");}
 }
}
echo "Prepared fictional queue sorting, expiry and due-date fixtures.\n";
