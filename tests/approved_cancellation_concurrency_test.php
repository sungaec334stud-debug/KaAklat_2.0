<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/approved_cancellation.php';
if(PHP_SAPI!=='cli') { exit(1); }
$config=application_config()['database'];
if(($argv[1]??'')==='worker') {
    $name=$argv[2]??'';
    if(!preg_match('/^kaaklat_cancel_test_[a-f0-9]{12}$/D',$name)) { exit(2); }
    $config['database']=$name; $db=database_connection($config); start_application_session();
    $_SESSION['user_id']=(int)$argv[3]; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
    while(microtime(true)<(float)$argv[6]) { usleep(1000); }
    try {
        if($argv[5]==='cancel') { cancel_approved_request($db,(int)$argv[4],$token,'Fixture cancellation'); } elseif($argv[5]==='pickup') { record_pickup($db,(int)$argv[4],$token); }
        else { decide_borrow_request($db,(int)$argv[4],'approve',7,'',$token); }
    } catch(InvalidArgumentException) { /* Expected losing decision. */ }
    exit(0);
}
$admin=database_connection($config); $name='kaaklat_cancel_test_'.bin2hex(random_bytes(6));
$admin->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$config['database']=$name; $db=database_connection($config);
$sql=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql'));
foreach(explode(';',$sql) as $statement) { if(trim($statement)!=='') { $db->exec($statement); } }
$db->exec("INSERT INTO users(user_id,username,display_name,role,account_status) VALUES(1,'race_student_one','Fixture One','student','active'),(2,'race_student_two','Fixture Two','student','active'),(3,'race_lib_one','Fixture Librarian','librarian','active'),(4,'race_lib_two','Other Librarian','librarian','active'),(5,'race_student_three','Fixture Three','student','active')");
for($i=1;$i<=9;$i++) { $db->exec("INSERT INTO books(book_id,title,description) VALUES($i,'Fictional race $i','Fixture')"); $db->exec("INSERT INTO book_copies(copy_id,book_id,copy_code) VALUES($i,$i,'DECISION-RACE-$i')"); }
function race_request(PDO $db,int $id,int $student,int $book): void { $db->prepare('INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason) VALUES(?,?,?,7,?)')->execute([$id,$student,$book,'Fictional concurrent reason']); }
function race_decisions(string $name,array $workers): void {
    $processes=[]; $start=(string)(microtime(true)+1);
    foreach($workers as [$actor,$request,$action]) {
        $p=proc_open([PHP_BINARY,__FILE__,'worker',$name,(string)$actor,(string)$request,$action,$start],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$pipes);
        if(!is_resource($p)) { throw new RuntimeException('Worker launch failed'); } fclose($pipes[0]); $processes[]=$p;
    }
    foreach($processes as $p) { if(proc_close($p)!==0) { throw new RuntimeException('Worker failed'); } }
}
function race_check(bool $ok,string $label): void { if(!$ok) { throw new RuntimeException($label); } }

require_once dirname(__DIR__).'/app/pickups.php';
start_application_session(); $_SESSION['user_id']=3; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
race_request($db,1,1,1); decide_borrow_request($db,1,'approve',7,'',$token);
race_decisions($name,[[3,1,'cancel'],[4,1,'cancel']]);
race_check((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='loan.cancelled'")->fetchColumn()===1,'Single return audit');
race_check($db->query('SELECT cancelled_at FROM loans WHERE request_id=1')->fetchColumn()!==null,'Returned');
race_request($db,2,1,2); decide_borrow_request($db,2,'approve',7,'',$token); race_request($db,3,2,2);
race_decisions($name,[[3,2,'cancel'],[4,3,'approve']]);
race_check($db->query('SELECT cancelled_at FROM loans WHERE request_id=2')->fetchColumn()!==null,'Concurrent return persisted');
$decision=$db->query('SELECT decision FROM borrow_requests WHERE request_id=3')->fetchColumn();
if($decision==='pending') { decide_borrow_request($db,3,'approve',7,'',$token); }
race_check((int)$db->query('SELECT COUNT(*) FROM loans WHERE copy_id=2 AND returned_at IS NULL AND cancelled_at IS NULL')->fetchColumn()===1,'Exactly one new allocation');
race_check((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='loan.cancelled'")->fetchColumn()===2,'Two distinct returns audited');
race_request($db,4,1,3); decide_borrow_request($db,4,'approve',7,'',$token);
race_decisions($name,[[3,4,'cancel'],[4,4,'pickup']]);
$row=$db->query('SELECT * FROM loans WHERE request_id=4')->fetch();
race_check(($row['checked_out_at']===null)!==($row['cancelled_at']===null),'Exactly one pickup/cancellation outcome');
$count=(int)$db->query("SELECT COUNT(*) FROM audit_events WHERE entity_type='loan' AND entity_id=".(int)$row['loan_id']." AND event_code IN ('loan.cancelled','loan.collected')")->fetchColumn();
race_check($count===1,'Single winning transition audit');
echo "Three independent-process cancellation races PASS. Scratch database retained: $name\n";