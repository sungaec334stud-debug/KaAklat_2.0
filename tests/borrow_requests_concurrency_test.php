<?php
declare(strict_types=1);
// Creates a NEW, uniquely named scratch database. Never imports into the demo database.
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/borrow_requests.php';
if (PHP_SAPI !== 'cli') { exit(1); }
$config=application_config()['database'];
if (($argv[1] ?? '') === 'worker') {
    $name=$argv[2] ?? '';
    if (!preg_match('/^kaaklat_request_test_[a-f0-9]{12}$/D',$name)) { exit(2); }
    $config['database']=$name;
    $db=database_connection($config);
    start_application_session();
    $_SESSION['user_id']=(int)$argv[3]; $_SESSION['session_version']=1; $_SESSION['last_activity']=time();
    $token=csrf_token();
    while(microtime(true)<(float)$argv[5]) { usleep(1000); }
    foreach(explode(',',$argv[4]) as $book) {
        try { create_borrow_request($db,(int)$book,7,'Fictional concurrent study reason',$token); }
        catch(InvalidArgumentException $e) { /* Expected policy conflict. */ }
    }
    exit(0);
}
$admin=database_connection($config);
$name='kaaklat_request_test_'.bin2hex(random_bytes(6));
$admin->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$config['database']=$name; $db=database_connection($config);
$sql=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql'));
foreach(explode(';',$sql) as $statement) { if(trim($statement)!=='') { $db->exec($statement); } }
$db->exec("INSERT INTO users(user_id,username,display_name,account_status) VALUES(1,'race_one','Fixture One','active'),(2,'race_two','Fixture Two','active')");
for($i=1;$i<=6;$i++) {
    $db->exec("INSERT INTO books(book_id,title,description) VALUES($i,'Fictional race title $i','Fixture')");
    $db->exec("INSERT INTO book_copies(book_id,copy_code) VALUES($i,'RACE-$i')");
}
function run_request_race(string $name,int $student,array $sets): void {
    $processes=[]; $start=(string)(microtime(true)+1);
    foreach($sets as $set) {
        $p=proc_open([PHP_BINARY,__FILE__,'worker',$name,(string)$student,$set,$start],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$pipes);
        if(!is_resource($p)) { throw new RuntimeException('Worker failed to start'); }
        fclose($pipes[0]); $processes[]=$p;
    }
    foreach($processes as $p) { if(proc_close($p)!==0) { throw new RuntimeException('Race worker failed'); } }
}
run_request_race($name,1,['1,2,3','4,5,6']);
if((int)$db->query('SELECT COUNT(*) FROM borrow_requests WHERE student_id=1')->fetchColumn()!==5) { throw new RuntimeException('Pending cap race failed'); }
run_request_race($name,2,['1','1']);
if((int)$db->query('SELECT COUNT(*) FROM borrow_requests WHERE student_id=2')->fetchColumn()!==1) { throw new RuntimeException('Duplicate title race failed'); }
if((int)$db->query('SELECT COUNT(*) FROM audit_events')->fetchColumn()!==6) { throw new RuntimeException('Race audit count failed'); }
echo "Concurrent pending-cap and duplicate-title races PASS. Fictional scratch database retained for inspection: $name\n";
