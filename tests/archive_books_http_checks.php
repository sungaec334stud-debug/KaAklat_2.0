<?php
// Included by the existing scratch-only HTTP harness after its baseline checks.
require_once dirname(__DIR__).'/app/archived_catalog.php';
$coverName='upload-'.bin2hex(random_bytes(16)).'.jpg';
$image=imagecreatetruecolor(40,60);imagefill($image,0,0,imagecolorallocate($image,20,60,100));imagejpeg($image,dirname(__DIR__).'/private/covers/'.$coverName);imagedestroy($image);
$db->exec("INSERT INTO authors(author_id,author_name) VALUES(8000,'Archive HTTP Author')");
for($n=0;$n<15;$n++){
    $bookId=8000+$n;
    $db->prepare("INSERT INTO books(book_id,title,description,cover_path,created_at,archived_at) VALUES(?,?,?,?,'2026-09-01 00:00:00',?)")->execute([$bookId,'Archive HTTP '.sprintf('%02d',$n).' <Title>','Fictional archive HTTP test',$coverName,'2026-11-01 00:00:00']);
    $db->exec("INSERT INTO book_authors(book_id,author_id,credit_order) VALUES($bookId,8000,1)");
    $db->exec("INSERT INTO book_copies(book_id,copy_code,created_at,archived_at) VALUES($bookId,'ARCHIVE-HTTP-$bookId','2026-09-01 00:00:00',NULL),($bookId,'ARCHIVED-HTTP-$bookId','2026-09-01 00:00:00','2026-10-01 00:00:00')");
}
$restoreClient=curl_init();curl_setopt_array($restoreClient,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);
[$code,$html]=accept_http($restoreClient,'/login.php');accept_http($restoreClient,'/login.php',['username'=>'accept_librarian','password'=>$password,'csrf_token'=>accept_token($html)]);
[$code,$html]=accept_http($restoreClient,'/manage_books.php?status=archived');
// A selected record supplies the POST token; the unselected list has no mutation form.
if(!preg_match('/name="csrf_token"/',$html)){[$code,$html]=accept_http($restoreClient,'/manage_books.php?status=archived&id=8000');$restoreToken=accept_token($html);}
accept_check($code===200 && str_contains($html,'Archived books') && str_contains($html,'I confirm this title'),'Archived librarian review and confirmation');
[$code,$html]=accept_http($restoreClient,'/manage_books.php?status=archived&q=Archive+HTTP&page=1');
accept_check($code===200 && substr_count($html,'class="managed-catalog-record')===12 && str_contains($html,'&lt;Title&gt;'),'Archived list has escaped titles and12 rows');
[$code,$html]=accept_http($restoreClient,'/manage_books.php?status=archived&q=Archive+HTTP&page=999');
accept_check($code===200 && substr_count($html,'class="managed-catalog-record')===3 && str_contains($html,'Page 2 of 2') && str_contains($html,'status=archived'),'Archived bounded pagination and context');
[$code,$html]=accept_http($restoreClient,'/manage_books.php?status=archived&q=Archive+HTTP+Author');accept_check($code===200 && str_contains($html,'15 books'),'Archived author search');
[$code,$html]=accept_http($restoreClient,'/manage_books.php?status=archived&q=%25');accept_check($code===200 && str_contains($html,'No archived books found'),'Archived literal wildcard search');
[$code]=accept_http($restoreClient,'/cover.php?id=8000&archived=1');accept_check($code===200,'Librarian archived cover allowed');
[$code]=accept_http($restoreClient,'/cover.php?id=8000');accept_check($code===404,'Normal cover path still hides archived titles');
$archiveClients=[];
foreach(['student','pending','admin']as$index=>$role){
    $testUser=8100+$index;$username='archive_http_'.$role;
    $db->prepare('INSERT INTO users(user_id,username,display_name,password_hash,role,account_status) VALUES(?,?,?,?,?,?)')->execute([$testUser,$username,'Fictional archive role',$hash,$role==='admin'?'administrator':'student',$role==='pending'?'pending':'active']);
    $client=curl_init();curl_setopt_array($client,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);[$code,$login]=accept_http($client,'/login.php');accept_http($client,'/login.php',['username'=>$username,'password'=>$password,'csrf_token'=>accept_token($login)]);$archiveClients[$role]=$client;
    [$code]=accept_http($client,'/manage_books.php?status=archived');accept_check($code===403,"$role archived list denied");
    [$code]=accept_http($client,'/cover.php?id=8000&archived=1');accept_check($code===403,"$role archived cover denied");
    [$code]=accept_http($client,'/manage_books.php?status=archived',['action'=>'unarchive_book','book_id'=>8000,'archive_version'=>'2026-11-01 00:00:00','archive_event'=>'0','restore_confirmation'=>'yes','csrf_token'=>$restoreToken]);accept_check($code===403,"$role restore denied");
}
$anon=curl_init();curl_setopt_array($anon,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);[$code]=accept_http($anon,'/cover.php?id=8000&archived=1');accept_check($code===403,'Anonymous archived cover denied');
$post=['action'=>'unarchive_book','book_id'=>8000,'archive_version'=>'2026-11-01 00:00:00','archive_event'=>'0','restore_confirmation'=>'yes','csrf_token'=>$restoreToken];
foreach([['csrf_token'=>'forged'],['restore_confirmation'=>''],['archive_version'=>'2020-01-01 00:00:00'],['archive_event'=>'55'],['role'=>'administrator']]as$change){[$code,$html]=accept_http($restoreClient,'/manage_books.php?status=archived&id=8000',array_replace($post,$change));accept_check($code===400 && str_contains($html,'role="alert"'),'Forged/stale/unconfirmed restore refused with feedback');}
[$code]=accept_http($restoreClient,'/manage_books.php?status=archived&id=8000&action=unarchive_book');accept_check($code===200 && $db->query('SELECT archived_at FROM books WHERE book_id=8000')->fetchColumn()!==null,'GET never restores');
$counts=[];foreach(['books','book_copies','borrow_requests','loans','authors','book_authors']as$table){$counts[$table]=$db->query("SELECT COUNT(*) FROM $table")->fetchColumn();}
[$code]=accept_http($restoreClient,'/manage_books.php?status=archived&q=Archive+HTTP&page=2&id=8000',$post);accept_check($code===303,'Restore uses POST redirect GET');
accept_check($db->query('SELECT archived_at FROM books WHERE book_id=8000')->fetchColumn()===null,'Restore clears only title archive state');
accept_check((int)catalog_book($db,8000)['available_copies']===1,'Restored HTTP availability derived');
foreach($counts as$table=>$count){accept_check($count===$db->query("SELECT COUNT(*) FROM $table")->fetchColumn(),"Restoration preserves $table count");}
[$code]=accept_http($restoreClient,'/manage_books.php?status=archived&id=8000',$post);accept_check($code===400,'Repeated HTTP restore is harmless');
accept_check((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE entity_id=8000 AND event_code='catalog.unarchive_book'")->fetchColumn()===1,'Single restore audit after repeated POST');
[$code,$html]=accept_http($archiveClients['admin'],'/audit.php?event=catalog.unarchive_book');accept_check($code===200 && str_contains($html,'Catalog title unarchived'),'Admin sees restoration audit');
[$code]=accept_http($archiveClients['student'],'/cover.php?id=8000');accept_check($code===200,'Restored normal cover visible to catalog users');
$db->exec("UPDATE book_copies SET archived_at='2026-10-01 00:00:00' WHERE book_id=8001");
[$code]=accept_http($restoreClient,'/manage_books.php?status=archived&id=8001',array_replace($post,['book_id'=>8001]));accept_check($code===303 && (int)catalog_book($db,8001)['available_copies']===0,'Zero-active-copy restoration remains unavailable');
[$code]=accept_http($restoreClient,'/manage_books.php?status=invalid');accept_check($code===400,'Invalid archive tab rejected');
[$code]=accept_http($restoreClient,'/manage_books.php?status=archived&id=9999999');accept_check($code===404,'Missing archived title returns404');
// Retained cover is fictional and stays only in the isolated scratch source.
