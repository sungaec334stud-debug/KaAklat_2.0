<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require dirname(__DIR__).'/app/bootstrap.php';
$config=application_config()['database']; $name='kaaklat_acceptance_'.bin2hex(random_bytes(6));
$db=database_connection($config);$db->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$config['database']=$name;$db=database_connection($config);
$sql=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql'));foreach(explode(';',$sql) as $part)if(trim($part)!=='')$db->exec($part);
$db->exec('CREATE TABLE categories(category_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,category_name VARCHAR(60) UNIQUE) ENGINE=InnoDB');$db->exec('ALTER TABLE books ADD category_id BIGINT UNSIGNED NULL, ADD publication_year SMALLINT UNSIGNED NULL');
$password=bin2hex(random_bytes(12));$hash=password_hash($password,PASSWORD_DEFAULT);
$s=$db->prepare('INSERT INTO users(user_id,username,display_name,password_hash,role,account_status) VALUES(?,?,?,?,?,?)');
foreach([[1,'accept_student','Acceptance Student','student'],[2,'accept_other','Other Student','student'],[3,'accept_librarian','Acceptance Librarian','librarian'],[4,'accept_admin','Acceptance Admin','administrator']] as [$id,$username,$display,$role])$s->execute([$id,$username,$display,$hash,$role,'active']);
for($i=1;$i<=3;$i++){$db->exec("INSERT INTO books(book_id,title,description) VALUES($i,'Fictional Acceptance $i','Fictional book for isolated acceptance')");$db->exec("INSERT INTO book_copies(copy_id,book_id,copy_code) VALUES($i,$i,'ACCEPT-$i')");}
function accept_check(bool $ok,string $label):void {if(!$ok)throw new RuntimeException($label);echo "PASS: $label\n";}
function accept_http($client,string $path,?array $data=null):array {curl_setopt_array($client,[CURLOPT_URL=>'http://127.0.0.1:8017'.$path,CURLOPT_POSTFIELDS=>$data===null?null:http_build_query($data),CURLOPT_POST=>$data!==null]);$body=curl_exec($client);if($body===false)throw new RuntimeException(curl_error($client));return [curl_getinfo($client,CURLINFO_RESPONSE_CODE),$body];}
function accept_token(string $html):string {if(!preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$html,$m))throw new RuntimeException('Missing token');return $m[1];}
putenv('KAAKLAT_ACCEPTANCE_DB='.$name);
$server=proc_open([PHP_BINARY,'-S','127.0.0.1:8017','-t',dirname(__DIR__).'/public',__DIR__.'/acceptance_router.php'],[0=>['pipe','r'],1=>['file',dirname(__DIR__).'/tmp/acceptance-server.log','a'],2=>['file',dirname(__DIR__).'/tmp/acceptance-server.log','a']],$pipes);
if(!is_resource($server))throw new RuntimeException('Server failed');fclose($pipes[0]);
try {
 $ready=curl_init('http://127.0.0.1:8017/login.php');
 curl_setopt_array($ready,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>2,CURLOPT_CONNECTTIMEOUT=>1]);
 $deadline=microtime(true)+45; $started=false;
 do {
     $probe=curl_exec($ready);
     if($probe!==false && curl_getinfo($ready,CURLINFO_RESPONSE_CODE)===200) { $started=true; break; }
     usleep(250000);
 } while(microtime(true)<$deadline);
 curl_close($ready);
 if(!$started) { throw new RuntimeException('Acceptance server did not become ready within 45 seconds.'); }
 $clients=[];
 foreach(['student','other','librarian','admin'] as $role){$c=curl_init();curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);[$code,$html]=accept_http($c,'/login.php');[$code]=accept_http($c,'/login.php',['username'=>'accept_'.$role,'password'=>$password,'csrf_token'=>accept_token($html)]);accept_check($code===303,"$role login");$clients[$role]=$c;}
 $student=$clients['student'];$staff=$clients['librarian'];
 [$code,$html]=accept_http($student,'/');accept_check($code===200 && str_contains($html,'status=pending') && !str_contains($html,'href="/pickups.php"'),'Student navigation/count links');
 foreach(['student','admin'] as $role){[$code]=accept_http($clients[$role],'/pickups.php');accept_check($code===403,"$role pickup access denied");}
 [$code,$html]=accept_http($student,'/requests.php?book=1');$token=accept_token($html);
 [$code,$html]=accept_http($student,'/requests.php?book=1',['action'=>'create','book_id'=>1,'days'=>31,'reason'=>'short','csrf_token'=>$token]);accept_check($code===400 && str_contains($html,'days-error') && str_contains($html,'>short</textarea>'),'Invalid request field errors and retained reason');
 [$code]=accept_http($student,'/requests.php?book=1',['action'=>'create','book_id'=>1,'days'=>7,'reason'=>'Fictional acceptance reason','csrf_token'=>$token]);accept_check($code===303,'Student request submission');
 $id=(int)$db->query('SELECT request_id FROM borrow_requests WHERE student_id=1')->fetchColumn();
 [$code,$html]=accept_http($staff,'/review_requests.php');$staffToken=accept_token($html);accept_check(str_contains($html,'href="/students.php"') && str_contains($html,'href="/pickups.php"'),'Librarian navigation');
 [$code,$html]=accept_http($staff,'/review_requests.php',['request_id'=>$id,'action'=>'approve','days'=>8,'note'=>'','csrf_token'=>$staffToken]);accept_check($code===400 && str_contains($html,'note-'.$id.'-error'),'Changed-duration field error');
 [$code]=accept_http($staff,'/review_requests.php',['request_id'=>$id,'action'=>'approve','days'=>8,'note'=>'Fictional approved duration explanation','csrf_token'=>$staffToken]);accept_check($code===303,'Approval HTTP transition');
 [$code,$html]=accept_http($staff,'/pickups.php');accept_check($code===200 && str_contains($html,'ACCEPT-1'),'Pickup allocated code shown');
 foreach([['csrf_token'=>'forged'],['role'=>'administrator']] as $forgery){[$code]=accept_http($staff,'/pickups.php',array_replace(['request_id'=>$id,'action'=>'pickup','csrf_token'=>$staffToken],$forgery));accept_check($code===400,'Forged pickup input refused');}
 require_once dirname(__DIR__).'/app/catalog.php'; $availableBefore=(int)catalog_book($db,1)['available_copies'];
 [$code]=accept_http($staff,'/pickups.php',['request_id'=>$id,'action'=>'pickup','csrf_token'=>$staffToken]);accept_check($code===303,'Pickup HTTP transition');
 accept_check((int)catalog_book($db,1)['available_copies']===$availableBefore,'Availability unchanged by pickup');
 $loan=$db->query('SELECT * FROM loans')->fetch();require_once dirname(__DIR__).'/app/pickups.php';accept_check($loan['due_date']===pickup_due_date($loan['checked_out_at'],8),'Actual Manila due date');
 [$code]=accept_http($staff,'/pickups.php',['request_id'=>$id,'action'=>'pickup','csrf_token'=>$staffToken]);accept_check($code===303 && $loan===$db->query('SELECT * FROM loans')->fetch() && (int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='loan.collected'")->fetchColumn()===1,'Repeated pickup unchanged and single audit');
 [$code,$html]=accept_http($student,'/requests.php?status=active');accept_check($code===200 && str_contains($html,'Active loan') && str_contains($html,'Picked up:') && str_contains($html,'Fictional approved duration explanation'),'Student active card and approved/original terms');
 [$code,$html]=accept_http($clients['other'],'/requests.php');accept_check(!str_contains($html,'Fictional Acceptance 1') && str_contains($html,'No requests'),'Other student isolated empty state');
 $db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,requested_at,decision,approved_days,decided_at,decided_by) VALUES(100,2,2,7,'Fictional expired acceptance',UTC_TIMESTAMP()-INTERVAL 4 DAY,'approved',7,UTC_TIMESTAMP()-INTERVAL 3 DAY,3)");
 $db->exec('INSERT INTO loans(request_id,copy_id) VALUES(100,2)');
 [$code]=accept_http($staff,'/pickups.php',['request_id'=>100,'action'=>'pickup','csrf_token'=>$staffToken]);
 $expired=$db->query('SELECT * FROM loans WHERE request_id=100')->fetch();
 accept_check($code===303 && $expired['checked_out_at']===null && $expired['cancellation_kind']==='expired','Expired pickup refused and released over HTTP');
 foreach(['student','admin'] as $role){[$code]=accept_http($clients[$role],'/returns.php');accept_check($code===403,"$role return access denied");}
 [$code,$html]=accept_http($staff,'/returns.php?q=ACCEPT-1');accept_check($code===200 && str_contains($html,'ACCEPT-1') && str_contains($html,'Acceptance Student'),'Return copy search and borrower');
 [$code,$errorHtml]=accept_http($staff,'/returns.php?q=ACCEPT-1&page=1',['request_id'=>$id,'action'=>'return','csrf_token'=>$staffToken]);
 accept_check($code===400 && str_contains($errorHtml,'href="#receipt-'.$id.'"') && str_contains($errorHtml,'aria-describedby="return-'.$id.'-error"') && str_contains($errorHtml,'value="ACCEPT-1"'),'HTTP receipt error association and search retention');
 [$code,$errorHtml]=accept_http($staff,'/returns.php?q=ACCEPT-1&page=1',['request_id'=>$id,'action'=>'return','csrf_token'=>'forged','receipt_confirmation'=>'on']);
 accept_check($code===400 && str_contains($errorHtml,'required checked') && str_contains($errorHtml,'href="#return-'.$id.'"'),'HTTP checked receipt preserved with row summary');
 foreach([['csrf_token'=>'forged'],['role'=>'administrator'],['receipt_confirmation'=>'']] as $forgery){[$code]=accept_http($staff,'/returns.php',array_replace(['request_id'=>$id,'action'=>'return','receipt_confirmation'=>'on','csrf_token'=>$staffToken],$forgery));accept_check($code===400,'Forged/unconfirmed return refused');}
 [$code]=accept_http($staff,'/returns.php',['request_id'=>$id,'action'=>'return','receipt_confirmation'=>'on','csrf_token'=>$staffToken]);accept_check($code===303,'Return HTTP transition');
 $returned=$db->query('SELECT * FROM loans WHERE request_id='.$id)->fetch();accept_check($returned['returned_at']!==null && (int)$returned['returned_by']===3 && $returned['checked_out_at']===$loan['checked_out_at'] && $returned['due_date']===$loan['due_date'],'Return actor/time and checkout preservation');
 accept_check((int)catalog_book($db,1)['available_copies']===1,'Returned copy available');
 [$code]=accept_http($staff,'/returns.php',['request_id'=>$id,'action'=>'return','receipt_confirmation'=>'on','csrf_token'=>$staffToken]);accept_check($code===303 && $returned===$db->query('SELECT * FROM loans WHERE request_id='.$id)->fetch() && (int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='loan.returned'")->fetchColumn()===1,'Return retry unchanged one audit');
 [$code,$html]=accept_http($student,'/requests.php');accept_check(str_contains($html,'Returned:') && str_contains($html,'This loan is complete'),'Student return history');
 [$code,$html]=accept_http($staff,'/returns.php');accept_check(!str_contains($html,'Receive copy:') && str_contains($html,'No outstanding loans'),'Returned loan leaves queue');
 echo "Scratch database retained: $name\n";
} finally {proc_terminate($server);proc_close($server);}
