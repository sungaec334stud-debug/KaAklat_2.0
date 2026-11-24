<?php
// Runs in the existing isolated harness. Never uses pilot credentials or records.
foreach([['active','student','active'],['pending','student','pending'],['librarian','librarian','active'],['admin','administrator','active']]as$i=>[$key,$role,$status]){
 $id=18000+$i;$username='history_http_'.$key;$display='Fictional history '.$key;
 $db->prepare('INSERT INTO users(user_id,username,display_name,password_hash,role,account_status) VALUES(?,?,?,?,?,?)')->execute([$id,$username,$display,$hash,$role,$status]);
 $client=curl_init();curl_setopt_array($client,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);
 [$code,$html]=accept_http($client,'/login.php');accept_check($code===200&&!str_contains($html,'portal-topbar'),'Guest login remains available '.$key);
 [$code]=accept_http($client,'/login.php',['username'=>$username,'password'=>$password,'csrf_token'=>accept_token($html)]);accept_check($code===303,'History fixture sign-in '.$key);
 $usersBefore=$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
 foreach(['/login.php','/register.php']as$route){
  [$code,$html]=accept_http($client,$route);$target=curl_getinfo($client,CURLINFO_REDIRECT_URL);
  accept_check($code===303&&str_ends_with($target,'/')&&!str_contains($html,'auth-main'),'Signed-in GET entry redirects without mixed form '.$key.$route);
  [$code]=accept_http($client,$route,['username'=>'must_not_create_history','display_name'=>'Forbidden cached form','password'=>$password,'school_student_id'=>'26-1234-567','csrf_token'=>'forged']);
  accept_check($code===303&&str_ends_with(curl_getinfo($client,CURLINFO_REDIRECT_URL),'/'),'Signed-in stale POST entry redirects before processing '.$key.$route);
 }
 accept_check($db->query('SELECT COUNT(*) FROM users')->fetchColumn()===$usersBefore,'Stale registration creates no account '.$key);
 [$code,$html]=accept_http($client,'/');accept_check($code===200&&str_contains($html,$display)&&!str_contains($html,'name="username"'),'Current role identity remains logged in '.$key);
 [$code,$html]=accept_http($client,'/logout.php');[$code]=accept_http($client,'/logout.php',['confirm'=>'yes','csrf_token'=>accept_token($html)]);accept_check($code===303,'Explicit logout works '.$key);
 foreach(['/login.php','/register.php']as$route){[$code,$html]=accept_http($client,$route);accept_check($code===200&&str_contains($html,'auth-main')&&!str_contains($html,'sidebar-identity'),'After logout entry is truly guest '.$key.$route);}
 // Re-login then revoke this fixture's session; an entry redirect must not revive it.
 [$code,$html]=accept_http($client,'/login.php');accept_http($client,'/login.php',['username'=>$username,'password'=>$password,'csrf_token'=>accept_token($html)]);
 $db->exec('UPDATE users SET session_version=session_version+1 WHERE user_id='.$id);
 [$code,$html]=accept_http($client,'/login.php');accept_check($code===200&&str_contains($html,'auth-main')&&!str_contains($html,'sidebar-identity'),'Revoked session renders genuine guest login '.$key);
}
