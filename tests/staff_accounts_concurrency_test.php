<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/staff_accounts.php';
if(PHP_SAPI!=='cli') exit(1);
$config=application_config()['database'];
if(($argv[1]??'')==='worker') {
    $name=$argv[2]; if(!preg_match('/^kaaklat_staff_test_[a-f0-9]{12}$/D',$name)) exit(2);
    $config['database']=$name; $db=database_connection($config); start_application_session();
    $_SESSION['user_id']=(int)$argv[3]; $_SESSION['session_version']=1; $_SESSION['last_activity']=time(); $token=csrf_token();
    while(microtime(true)<(float)$argv[5]) usleep(1000);
    try {
        if($argv[4]==='bootstrap') bootstrap_administrator($db,'boot_'.$argv[3],'Fictional admin','Fictional fixture secret');
        else staff_update($db,(int)$argv[3],'administrator','suspended',1,$token);
    } catch(InvalidArgumentException|DomainException) {}
    exit(0);
}
$admin=database_connection($config); $name='kaaklat_staff_test_'.bin2hex(random_bytes(6));
$admin->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'); $config['database']=$name; $db=database_connection($config);
$sql=preg_replace('/^--.*$/m','',file_get_contents(dirname(__DIR__).'/schema.sql')); foreach(explode(';',$sql) as $part) if(trim($part)!=='') $db->exec($part);
function staff_race(string $name,array $workers):void {
    $processes=[]; $start=(string)(microtime(true)+1);
    foreach($workers as [$actor,$action]) {
        $p=proc_open([PHP_BINARY,__FILE__,'worker',$name,(string)$actor,$action,$start],[0=>['pipe','r'],1=>STDOUT,2=>STDERR],$pipes);
        if(!is_resource($p)) throw new RuntimeException('Worker failed'); fclose($pipes[0]); $processes[]=$p;
    }
    foreach($processes as $p) if(proc_close($p)!==0) throw new RuntimeException('Worker failure');
}
staff_race($name,[[1,'bootstrap'],[2,'bootstrap']]);
if((int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn()!==1) throw new RuntimeException('Bootstrap race');
$first=(int)$db->query('SELECT user_id FROM users')->fetchColumn();
$s=$db->prepare("INSERT INTO users(username,display_name,role,account_status,password_hash) VALUES('second_admin','Fictional second','administrator','active',?)"); $s->execute([password_hash('Fictional second password',PASSWORD_DEFAULT)]); $second=(int)$db->lastInsertId();
staff_race($name,[[$first,'suspend'],[$second,'suspend']]);
if((int)$db->query("SELECT COUNT(*) FROM users WHERE role='administrator' AND account_status='active' AND password_hash IS NOT NULL")->fetchColumn()!==1) throw new RuntimeException('Last admin race');
echo "Concurrent bootstrap and last usable administrator PASS. Scratch retained: $name\n";
