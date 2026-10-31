<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/request_decisions.php';
if(PHP_SAPI!=='cli') { exit(1); }
$config=application_config()['database'];
if(($argv[1]??'')==='worker') {
    $name=$argv[2]??'';
    if(!preg_match('/^kaaklat_decision_test_[a-f0-9]{12}$/D',$name)) { exit(2); }
    $config['database']=$name; $db=database_connection($config); start_application_session();
    $_SESSION['user_id']=(int)$argv[3]; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
    while(microtime(true)<(float)$argv[6]) { usleep(1000); }
    try {
        if($argv[5]==='cancel') { cancel_borrow_request($db,(int)$argv[4],$token); }
        else { decide_borrow_request($db,(int)$argv[4],'approve',7,'',$token); }
    } catch(InvalidArgumentException) { /* Expected losing decision. */ }
    exit(0);
}
$admin=database_connection($config); $name='kaaklat_decision_test_'.bin2hex(random_bytes(6));
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
race_request($db,1,1,1); race_decisions($name,[[3,1,'approve'],[4,1,'approve']]);
race_check((int)$db->query('SELECT COUNT(*) FROM loans WHERE request_id=1')->fetchColumn()===1,'Same request allocated once');
race_check((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='request.approved' AND entity_id=1")->fetchColumn()===1,'Same request audited once');
race_request($db,2,1,2); race_request($db,3,2,2); race_decisions($name,[[3,2,'approve'],[4,3,'approve']]);
race_check((int)$db->query('SELECT COUNT(*) FROM loans WHERE copy_id=2')->fetchColumn()===1,'Last copy allocated once');
race_check((int)$db->query("SELECT COUNT(*) FROM borrow_requests WHERE request_id IN(2,3) AND decision='pending'")->fetchColumn()===1,'Last copy loser remains pending');
start_application_session(); $_SESSION['user_id']=3; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
for($i=4;$i<=7;$i++) { race_request($db,$i,5,$i); decide_borrow_request($db,$i,'approve',7,'',$token); }
race_request($db,8,5,8); race_request($db,9,5,9); race_decisions($name,[[3,8,'approve'],[4,9,'approve']]);
race_check((int)$db->query("SELECT COUNT(*) FROM borrow_requests WHERE student_id=5 AND decision='approved'")->fetchColumn()===5,'Last allowance slot bounded');
// Use an additional copy so the cancellation race is not decided by availability.
$db->exec("INSERT INTO book_copies(book_id,copy_code) VALUES(3,'DECISION-CANCEL')");
race_request($db,10,2,3); race_decisions($name,[[2,10,'cancel'],[3,10,'approve']]);
$state=$db->query('SELECT decision FROM borrow_requests WHERE request_id=10')->fetchColumn();
$count=(int)$db->query('SELECT COUNT(*) FROM loans WHERE request_id=10')->fetchColumn();
race_check(($state==='approved' && $count===1)||($state==='cancelled' && $count===0),'Cancellation decision serialized');
race_check((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE entity_type='borrow_request' AND entity_id=10")->fetchColumn()===1,'Cancellation decision one audit');
echo "Same request, last copy, last allowance and cancellation/approval concurrency PASS. Scratch database retained: $name\n";
