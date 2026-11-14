<?php
// Runs exclusively inside the fictional HTTP acceptance harness.
$entryGuest=curl_init();
curl_setopt_array($entryGuest,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);
foreach (['/','/login.php','/register.php','/reset_password.php'] as $entryRoute) {
    [$code,$body]=accept_http($entryGuest,$entryRoute);
    accept_check($code===200 && str_contains($body,'href="/about.php"'),'Entry route preserves About: '.$entryRoute);
    accept_check(!str_contains($body,'Design preview') && !str_contains($body,'No real accounts'),'No prototype-only controls: '.$entryRoute);
}
[$code,$body]=accept_http($entryGuest,'/register.php');
$entryToken=accept_token($body);
[$code,$body]=accept_http($entryGuest,'/register.php',['school_student_id'=>'26-0123-45','username'=>'<fictional>','display_name'=>'<Fictional Student>','password'=>'not-retained-secret','csrf_token'=>$entryToken]);
accept_check($code===200 && str_contains($body,'value="26-0123-45"') && str_contains($body,'&lt;Fictional Student&gt;'),'Invalid registration retains escaped nonsecret fields');
accept_check(!str_contains($body,'not-retained-secret'),'Failed registration never echoes password');
[$code,$body]=accept_http($entryGuest,'/login.php',['username'=>'<fictional>','password'=>'not-retained-secret','csrf_token'=>'forged']);
accept_check($code===200 && str_contains($body,'Your form expired') && str_contains($body,'&lt;fictional&gt;') && !str_contains($body,'not-retained-secret'),'Login CSRF and escaped retained username');
curl_close($entryGuest);
