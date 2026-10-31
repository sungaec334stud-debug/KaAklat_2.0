<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/pickups.php';
if(PHP_SAPI!=='cli') { exit(1); }
$config=application_config()['database'];
if(($argv[1]??'')==='worker') {
    $name=$argv[2]??'';
    if(!preg_match('/^kaaklat_pickup_test_[a-f0-9]{12}$/D',$name)) { exit(2); }
    $config['database']=$name; $db=database_connection($config); start_application_session();
    $_SESSION['user_id']=(int)$argv[3]; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
    while(microtime(true)<(float)$argv[6]) { usleep(1000); }
    try {
        if($argv[5]==='pickup') { record_pickup($db,(int)$argv[4],$token); }
        else { decide_borrow_request($db,(int)$argv[4],'approve',7,'',$token); }
    } catch(InvalidArgumentException) { /* Expected losing decision. */ }
    exit(0);
}
$admin=database_connection($config); $name='kaaklat_pickup_test_'.bin2hex(random_bytes(6));
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

start_application_session(); $_SESSION['user_id']=3; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
race_request($db,1,1,1); decide_borrow_request($db,1,'approve',7,'',$token);
race_decisions($name,[[3,1,'pickup'],[4,1,'pickup']]);
race_check((int)$db->query('SELECT COUNT(*) FROM loans WHERE request_id=1 AND checked_out_at IS NOT NULL')->fetchColumn()===1,'One checkout');
race_check((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='loan.collected'")->fetchColumn()===1,'One checkout audit');
race_request($db,2,1,2); decide_borrow_request($db,2,'approve',7,'',$token);
$db->exec('UPDATE borrow_requests SET requested_at=UTC_TIMESTAMP()-INTERVAL 4 DAY,decided_at=UTC_TIMESTAMP()-INTERVAL 3 DAY WHERE request_id=2');
race_request($db,3,2,2);
race_decisions($name,[[3,2,'pickup'],[4,3,'approve']]);
race_check($db->query('SELECT cancellation_kind FROM loans WHERE request_id=2')->fetchColumn()==='expired','Stale allocation expired');
race_check($db->query('SELECT checked_out_at FROM loans WHERE request_id=2')->fetchColumn()===null,'Stale pickup refused');
race_check((int)$db->query('SELECT COUNT(*) FROM loans WHERE copy_id=2 AND cancelled_at IS NULL')->fetchColumn()===1,'Copy reallocated once');
race_check((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='loan.expired'")->fetchColumn()===1,'Single expiry audit');
echo "Concurrent same-allocation pickup and expiry/reallocation versus stale pickup PASS. Scratch database retained: $name\n";
