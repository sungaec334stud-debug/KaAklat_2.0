<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit(1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/catalog_management.php';
$fixture=json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR);
if(!preg_match('/^kaaklat_acceptance_[a-f0-9]{12}$/D',$fixture['database']))throw new RuntimeException('Fictional fixture required');
$config=application_config()['database'];$config['database']=$fixture['database'];$pdo=database_connection($config);
start_application_session();$_SESSION=['user_id'=>3,'session_version'=>1];$token=csrf_token();
$cover=$pdo->query('SELECT cover_path FROM books WHERE book_id=8')->fetchColumn();
for($n=0;$n<15;$n++){
    $id=9000+$n;
    $existing=$pdo->prepare('SELECT archived_at FROM books WHERE book_id=?');$existing->execute([$id]);$row=$existing->fetch();
    if($row){if($row['archived_at']===null){catalog_manage($pdo,['action'=>'archive_book','book_id'=>$id],$token);}continue;}
    $pdo->prepare('INSERT INTO books(book_id,title,description,cover_path) VALUES(?,?,?,?)')->execute([$id,'Archived Fictional '.sprintf('%02d',$n),'Fictional archive browser fixture',$cover]);
    $pdo->exec("INSERT INTO book_authors(book_id,author_id,credit_order) VALUES($id,1,1)");
    $pdo->exec("INSERT INTO book_copies(book_id,copy_code) VALUES($id,'ARCHIVE-BROWSER-$id')");
    if($n===1){$copy=(int)$pdo->lastInsertId();catalog_manage($pdo,['action'=>'archive_copy','book_id'=>$id,'copy_id'=>$copy],$token);}
    catalog_manage($pdo,['action'=>'archive_book','book_id'=>$id],$token);
}
echo "Prepared15 fictional archived titles, including one without active copies.\n";
