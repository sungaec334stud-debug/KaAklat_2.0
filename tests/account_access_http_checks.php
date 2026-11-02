<?php
// Included only by the isolated real-HTTP harness; no secret output.
$adminClient=$clients['admin'];
[$code,$html]=accept_http($adminClient,'/students.php'); accept_check($code===200,'Admin student page HTTP');
[$code]=accept_http($adminClient,'/students.php',['student_id'=>$newStudent['user_id'],'action'=>'suspend','csrf_token'=>$adminToken]); accept_check($code===303 && $db->query('SELECT account_status FROM users WHERE user_id='.(int)$newStudent['user_id'])->fetchColumn()==='suspended','Admin student suspension HTTP');
[$code]=accept_http($adminClient,'/students.php',['student_id'=>$newStudent['user_id'],'action'=>'reactivate','csrf_token'=>$adminToken]); accept_check($code===303,'Admin student reactivation HTTP');
$guest=curl_init(); curl_setopt_array($guest,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);
foreach(['/recover_account.php','/audit.php'] as $path) {[$code]=accept_http($guest,$path); accept_check($code===303,'Signed-out privileged account page denied');}
[$code,$html]=accept_http($staff,'/recover_account.php'); $issueToken=accept_token($html);
[$code]=accept_http($staff,'/recover_account.php',['username'=>'accept_admin','identity_confirmation'=>'on','csrf_token'=>$issueToken]); accept_check($code===403,'Librarian staff recovery denied HTTP');
[$code]=accept_http($staff,'/recover_account.php',['username'=>'new_http_student','csrf_token'=>$issueToken]); accept_check($code===400,'Identity confirmation required HTTP');
[$code]=accept_http($staff,'/recover_account.php',['username'=>'new_http_student','identity_confirmation'=>'on','csrf_token'=>'forged']); accept_check($code===400,'Issuance CSRF denied HTTP');
[$code]=accept_http($staff,'/recover_account.php',['username'=>'new_http_student','identity_confirmation'=>'on','csrf_token'=>$issueToken]); accept_check($code===303,'Recovery issuance HTTP');
[$code,$html]=accept_http($staff,'/recover_account.php'); if(!preg_match('~<code>([a-f0-9]{64})</code>~',$html,$match)) throw new RuntimeException('Missing private delivery'); $secret=$match[1];
accept_check($code===200 && $db->query('SELECT HEX(token_hash) FROM password_resets WHERE user_id='.(int)$newStudent['user_id'])->fetchColumn()===strtoupper(hash('sha256',hex2bin($secret))),'One-time delivery matches stored digest');
[$code,$html]=accept_http($staff,'/recover_account.php'); accept_check(!str_contains($html,$secret),'Secret not redisplayed');
[$code,$html]=accept_http($guest,'/reset_password.php'); $resetToken=accept_token($html);
[$code,$html]=accept_http($guest,'/reset_password.php',['secret'=>$secret,'password'=>'Fictional recovery password','password_confirmation'=>'mismatch','csrf_token'=>$resetToken]); accept_check($code===400 && !str_contains($html,$secret) && str_contains($html,'password_confirmation-error'),'Password mismatch and secret not reflected');
[$code]=accept_http($guest,'/reset_password.php',['secret'=>$secret,'password'=>'Fictional recovery password','password_confirmation'=>'Fictional recovery password','csrf_token'=>$resetToken]); accept_check($code===303 && (int)$db->query('SELECT COUNT(*) FROM password_resets WHERE user_id='.(int)$newStudent['user_id'])->fetchColumn()===0,'Recovery redemption HTTP');
[$code,$html]=accept_http($guest,'/reset_password.php');
[$code]=accept_http($guest,'/reset_password.php',['secret'=>$secret,'password'=>'Fictional replay password','password_confirmation'=>'Fictional replay password','csrf_token'=>accept_token($html)]); accept_check($code===400,'Recovery replay HTTP denied');
[$code,$html]=accept_http($guest,'/login.php');
[$code]=accept_http($guest,'/login.php',['username'=>'new_http_student','password'=>'Fictional recovery password','csrf_token'=>accept_token($html)]); accept_check($code===303,'Recovered password sign-in HTTP');
[$code]=accept_http($guest,'/audit.php'); accept_check($code===403,'Student audit denied HTTP');
[$code]=accept_http($staff,'/audit.php'); accept_check($code===403,'Librarian audit denied HTTP');
$db->prepare("INSERT INTO audit_events(actor_user_id,event_code,entity_type,entity_id,event_summary) VALUES(4,'test.private','users',4,?)")->execute(['SENSITIVE_AUDIT_FIXTURE']);
[$code,$html]=accept_http($adminClient,'/audit.php?event=test.private&actor=accept_admin'); accept_check($code===200 && str_contains($html,'test.private') && !str_contains($html,$secret) && !str_contains($html,'SENSITIVE_AUDIT_FIXTURE'),'Admin filtered audit excludes sensitive summary');
[$code]=accept_http($adminClient,'/audit.php',['action'=>'delete']); accept_check($code===405,'Audit read only HTTP');
[$code]=accept_http($adminClient,'/audit.php?page[]=1'); accept_check($code===400,'Audit forged pagination refused');
