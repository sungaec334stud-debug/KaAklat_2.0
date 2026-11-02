<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/recovery.php';
if(PHP_SAPI!=='cli') exit(1);
$config=application_config()['database'];
if(($argv[1]??'')==='worker') {
    $name=$argv[2]; if(!preg_match('/^kaaklat_recovery_test_[a-f0-9]{12}$/D',$name)) exit(2);
    $config['database']=$name; $db=database_connection($config); start_application_session(); $_SESSION=['user_id'=>1,'session_version'=>1,'last_activity'=>time()]; $token=csrf_token();
    while(microtime(true)<(float)$argv[4]) usleep(1000);
    try {
        if($argv[3]==='redeem') redeem_recovery($db,str_repeat('ab',32),'Fictional concurrent password',$token);
        else issue_recovery($db,'race_student',$token,true);
    } catch(InvalidArgumentException) {}
    exit(0);
}
$admin=database_connection($config); $name='kaaklat_recovery_test_'.bin2hex(random_bytes(6)); $admin->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'); $config['database']=$name; $db=database_connection($config);
$sql=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql')); foreach(explode(';',$sql) as $part) if(trim($part)!=='') $db->exec($part);
$db->exec("INSERT INTO users(user_id,username,display_name,role,account_status) VALUES(1,'race_admin','Fictional admin','administrator','active'),(2,'race_student','Fictional student','student','pending')");
function recovery_race(string $name,array $actions):void {
    $processes=[]; $start=(string)(microtime(true)+1);
    foreach($actions as $action) {$p=proc_open([PHP_BINARY,__FILE__,'worker',$name,$action,$start],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$pipes); if(!is_resource($p)) throw new RuntimeException('Worker launch failed'); fclose($pipes[0]); $processes[]=$p;}
    foreach($processes as $p) if(proc_close($p)!==0) throw new RuntimeException('Worker failed');
}
function seed_race_reset(PDO $db):void {
    $db->prepare('INSERT INTO password_resets(user_id,token_hash,issued_by,expires_at) VALUES(2,?,1,UTC_TIMESTAMP()+INTERVAL 30 MINUTE)')->execute([hash('sha256',hex2bin(str_repeat('ab',32)),true)]);
}
seed_race_reset($db); recovery_race($name,['redeem','redeem']);
if((int)$db->query('SELECT session_version FROM users WHERE user_id=2')->fetchColumn()!==2 || (int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='account.reset_redeemed'")->fetchColumn()!==1) throw new RuntimeException('Double redemption');
seed_race_reset($db); recovery_race($name,['redeem','issue']);
$reset=$db->query('SELECT * FROM password_resets WHERE user_id=2')->fetch();
if(!$reset || hash_equals($reset['token_hash'],hash('sha256',hex2bin(str_repeat('ab',32)),true))) throw new RuntimeException('Reissue must retain a fresh replacement');
if($db->query('SELECT account_status FROM users WHERE user_id=2')->fetchColumn()!=='pending') throw new RuntimeException('Status changed');
echo "Concurrent one-time redemption and reissue/redemption PASS. Scratch retained: $name\n";
