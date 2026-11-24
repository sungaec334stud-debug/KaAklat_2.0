<?php
// Extend the existing loopback scratch-only harness with dedicated queue records/accounts.
$queueUser=12000;$queueName='queue_http_librarian';
$db->prepare('INSERT INTO users(user_id,username,display_name,password_hash,role,account_status) VALUES(?,?,?,?,?,?)')->execute([$queueUser,$queueName,'Fictional queue librarian',$hash,'librarian','active']);
for($n=0;$n<32;$n++){
 $qid=12000+$n;$title='Queue HTTP '.sprintf('%02d',$n);
 $db->prepare("INSERT INTO books(book_id,title,description,created_at) VALUES(?,?,?,'2026-01-01 00:00:00')")->execute([$qid,$title,'Fictional circulation filter fixture']);
 $db->exec("INSERT INTO book_copies(book_id,copy_code,created_at) VALUES($qid,'QUEUEHTTP-$qid','2026-01-01 00:00:00')");$copy=(int)$db->lastInsertId();
 if($n<24){$days=($n%6)+1;$db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,requested_at) VALUES($qid,1,$qid,$days,'Fictional queue filter request',UTC_TIMESTAMP()-INTERVAL $n MINUTE)");}
 else{
  $db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,requested_at,decision,approved_days,decided_at,decided_by) VALUES($qid,1,$qid,30,'Fictional queue filter allocation',UTC_TIMESTAMP()-INTERVAL 20 DAY,'approved',30,UTC_TIMESTAMP()-INTERVAL 11 DAY,$queueUser)");
  if($n<28){$hours=[24=>49,25=>48,26=>47,27=>0][$n];$db->exec("UPDATE borrow_requests SET decided_at=UTC_TIMESTAMP()-INTERVAL $hours HOUR WHERE request_id=$qid");$db->exec("INSERT INTO loans(request_id,copy_id) VALUES($qid,$copy)");}
  else{$offset=[28=>-1,29=>0,30=>2,31=>1][$n];$db->exec("INSERT INTO loans(request_id,copy_id,checked_out_at,checked_out_by,due_date) VALUES($qid,$copy,UTC_TIMESTAMP()-INTERVAL 10 DAY,$queueUser,DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)+INTERVAL $offset DAY)");if($n===31)$db->exec("UPDATE loans SET returned_at=UTC_TIMESTAMP(),returned_by=$queueUser WHERE request_id=$qid");}
 }
}
$queueClient=curl_init();curl_setopt_array($queueClient,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);[$code,$html]=accept_http($queueClient,'/login.php');accept_http($queueClient,'/login.php',['username'=>$queueName,'password'=>$password,'csrf_token'=>accept_token($html)]);
function queue_request_ids(string $html):array{preg_match_all('/name="request_id" value="(\d+)"/',$html,$matches);return array_values(array_unique(array_map('intval',$matches[1])));}
foreach(['oldest'=>12023,'newest'=>12000,'shortest'=>12018,'longest'=>12023]as$sort=>$first){
 [$code,$html]=accept_http($queueClient,'/review_requests.php?q=Queue+HTTP&sort='.$sort);$ids=queue_request_ids($html);
 accept_check($code===200 && count($ids)===20 && $ids[0]===$first,'Review HTTP order '.$sort);
 accept_check(str_contains($html,'sort='.$sort) && str_contains($html,'Queue+HTTP') && str_contains($html,' selected'),'Review sort/search pagination context '.$sort);
}
[$code,$html]=accept_http($queueClient,'/review_requests.php?q=Queue+HTTP&sort=newest&page=2');accept_check($code===200 && count(queue_request_ids($html))===4 && str_contains($html,'Page 2 of 2'),'Sorted review second page and totals');
[$code,$html]=accept_http($queueClient,'/review_requests.php?q=Queue+HTTP&sort=oldest&page=999');accept_check($code===200 && count(queue_request_ids($html))===4,'Sorted review page clamp');
foreach(['all'=>4,'ready'=>2,'expired'=>2]as$status=>$count){[$code,$html]=accept_http($queueClient,'/pickups.php?q=Queue+HTTP&status='.$status.'&sort=earliest');accept_check($code===200 && count(queue_request_ids($html))===$count,'Pickup HTTP filter '.$status);}
[$code,$html]=accept_http($queueClient,'/pickups.php?q=Queue+HTTP&status=ready&sort=latest');accept_check(queue_request_ids($html)[0]===12027,'Pickup HTTP reverse deadline order');
foreach(['all'=>3,'overdue'=>1,'due_today'=>1,'active'=>2]as$status=>$count){[$code,$html]=accept_http($queueClient,'/returns.php?q=Queue+HTTP&status='.$status.'&sort=earliest');accept_check($code===200 && count(queue_request_ids($html))===$count,'Return HTTP filter '.$status);}
[$code,$html]=accept_http($queueClient,'/returns.php?q=QUEUEHTTP-12029&status=active&sort=latest');accept_check(count(queue_request_ids($html))===1 && str_contains($html,'Due today'),'Return copy search combines with status and shows due-today label');
foreach(['/review_requests.php','/pickups.php','/returns.php']as$route){
 foreach(['sort=forged','sort%5B%5D=newest','status=invalid','status%5B%5D=expired']as$bad){[$code]=accept_http($queueClient,$route.'?'.$bad);accept_check($code===400,'Malformed circulation filter denied '.$route);}
 foreach(['student','admin']as$role){[$code]=accept_http($archiveClients[$role],$route.'?sort=earliest&status=all');accept_check($code===403,'Filtered queue role denied '.$role.$route);}
 [$code,$html]=accept_http($queueClient,$route.'?q=NoQueueMatch&sort='.($route==='/review_requests.php'?'newest':'latest').'&page=999');accept_check($code===200 && queue_request_ids($html)===[] && str_contains($html,'Page 1 of 1'),'Filtered empty-state clamp '.$route);
}
[$code,$html]=accept_http($queueClient,'/returns.php?q=Queue+HTTP&status=overdue&sort=latest');$qt=accept_token($html);
[$code,$html]=accept_http($queueClient,'/returns.php?q=Queue+HTTP&status=overdue&sort=latest',['action'=>'return','request_id'=>12028,'csrf_token'=>$qt]);accept_check($code===400 && str_contains($html,'value="overdue" selected') && str_contains($html,'value="latest" selected'),'Return validation retains filter controls');
[$code]=accept_http($queueClient,'/returns.php?q=Queue+HTTP&status=overdue&sort=latest',['action'=>'return','request_id'=>12028,'csrf_token'=>$qt,'receipt_confirmation'=>'on']);$location=curl_getinfo($queueClient,CURLINFO_REDIRECT_URL);accept_check($code===303 && str_contains($location,'status=overdue') && str_contains($location,'sort=latest') && str_contains($location,'q=Queue+HTTP'),'Return success preserves filter/search redirect');
[$code,$html]=accept_http($queueClient,'/pickups.php?q=Queue+HTTP&status=expired&sort=latest');$qt=accept_token($html);
[$code]=accept_http($queueClient,'/pickups.php?q=Queue+HTTP&status=expired&sort=latest',['action'=>'pickup','request_id'=>12024,'csrf_token'=>$qt]);$location=curl_getinfo($queueClient,CURLINFO_REDIRECT_URL);accept_check($code===303 && str_contains($location,'status=expired') && str_contains($location,'sort=latest'),'Expired pickup release preserves filter redirect');accept_check($db->query('SELECT checked_out_at FROM loans WHERE request_id=12024')->fetchColumn()===null,'Expired filter never permits physical pickup');
[$code,$html]=accept_http($queueClient,'/review_requests.php?q=Queue+HTTP&sort=shortest');$qt=accept_token($html);
[$code]=accept_http($queueClient,'/review_requests.php?q=Queue+HTTP&sort=shortest',['action'=>'reject','request_id'=>12000,'csrf_token'=>$qt,'note'=>'Fictional filtered rejection']);$location=curl_getinfo($queueClient,CURLINFO_REDIRECT_URL);accept_check($code===303 && str_contains($location,'sort=shortest') && str_contains($location,'q=Queue+HTTP'),'Review decision preserves sort/search redirect');
