<?php
// Included by the isolated HTTP harness; uses only its fictional database/clients.
$anonymous=curl_init(); curl_setopt_array($anonymous,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);
[$code,$landingHtml]=accept_http($anonymous,'/');
accept_check($code===200 && str_contains($landingHtml,'landing-hero') && str_contains($landingHtml,'href="/login.php"') && str_contains($landingHtml,'href="/register.php"'),'Signed-out landing images and entry routes');
foreach(['/assets/library-reading.jpg','/assets/library-study.jpg'] as $asset) {[$code,$bytes]=accept_http($anonymous,$asset); accept_check($code===200 && str_starts_with($bytes,"\xff\xd8"),'Public illustrative landing image loads');}
[$code,$html]=accept_http($anonymous,'/register.php'); $registerToken=accept_token($html);
[$code,$html]=accept_http($anonymous,'/register.php',['school_student_id'=>'26-0001-004','username'=>'new_http_student','display_name'=>'Fictional new student','password'=>'fictional'.chr(0).'password','csrf_token'=>$registerToken]);
accept_check($code===200 && str_contains($html,'NUL character') && !str_contains($html,'ValueError'),'NUL password validation without exception');
[$code]=accept_http($anonymous,'/register.php',['school_student_id'=>'26-0001-004','username'=>'new_http_student','display_name'=>'Fictional new student','password'=>$password,'role'=>'administrator','account_status'=>'active','csrf_token'=>$registerToken]);
accept_check($code===303,'Registration HTTP');
$newStudent=$db->query("SELECT * FROM users WHERE username='new_http_student'")->fetch();
accept_check($newStudent['role']==='student' && $newStudent['account_status']==='pending','Forged registration role/status cannot escalate');
[$code,$html]=accept_http($anonymous,'/login.php');
[$code]=accept_http($anonymous,'/login.php',['username'=>'new_http_student','password'=>$password,'csrf_token'=>accept_token($html)]); accept_check($code===303,'Pending student can sign in');
[$code]=accept_http($anonymous,'/catalog.php'); accept_check($code===200,'Pending student catalog access');
[$code,$html]=accept_http($anonymous,'/logout.php'); $newToken=accept_token($html);
[$code]=accept_http($anonymous,'/requests.php',['action'=>'create','book_id'=>1,'days'=>7,'reason'=>'Fictional study reason','csrf_token'=>$newToken]); accept_check($code===403,'Pending borrowing blocked');
$review = ['student_id'=>$newStudent['user_id'],'review_version'=>1,'csrf_token'=>$staffToken];
accept_check($newStudent['school_student_id']==='26-0001-004','School ID preserves leading zeros');
foreach (['26-1-004','26-0001-004'] as $claim) {
    [$code,$body]=accept_http($anonymous,'/register.php',['school_student_id'=>$claim,'username'=>'second_claim','display_name'=>'Fictional duplicate','password'=>$password,'csrf_token'=>$newToken]);
    $message=$claim==='26-1-004' ? 'YY-####-### format' : 'visit the library';
    accept_check($code===200 && str_contains($body,$message) && !str_contains($body,'26-0001-004') && !str_contains($body,'new_http_student'),'Invalid or duplicate claim rejected without exposing owner or ID');
}
[$code,$body]=accept_http($student,'/students.php'); accept_check($code===403 && !str_contains($body,'26-0001-004'),'Student cannot see other ID');
[$code,$body]=accept_http($clients['admin'],'/students.php'); accept_check($code===200 && !str_contains($body,'26-0001-004'),'Administrator cannot see school ID');
[$code,$body]=accept_http($staff,'/students.php'); accept_check($code===200 && str_contains($body,'26-0001-004'),'Librarian sees pending claim');
[$code,$body]=accept_http($clients['admin'],'/staff.php');
[$code]=accept_http($clients['admin'],'/students.php',array_replace($review,['action'=>'activate','identity_confirmation'=>'on','csrf_token'=>accept_token($body)]));
accept_check($code===403,'Administrator activation denied');
[$code]=accept_http($staff,'/students.php',$review+['action'=>'activate']);
accept_check($code===303 && $db->query('SELECT account_status FROM users WHERE user_id='.(int)$newStudent['user_id'])->fetchColumn()==='pending','Confirmation required before activation');
[$code]=accept_http($staff,'/students.php',$review+['action'=>'release_id','identity_confirmation'=>'on']);
accept_check($code===303 && $db->query('SELECT school_student_id FROM users WHERE user_id='.(int)$newStudent['user_id'])->fetchColumn()===null,'Mistaken pending claim released');
[$code]=accept_http($staff,'/students.php',array_replace($review,['action'=>'correct_id','identity_confirmation'=>'on','replacement_id'=>'26-0001-005','review_version'=>2]));
accept_check($code===303 && $db->query('SELECT school_student_id FROM users WHERE user_id='.(int)$newStudent['user_id'])->fetchColumn()==='26-0001-005','Librarian corrects pending claim');
[$code]=accept_http($staff,'/students.php',$review+['action'=>'activate','identity_confirmation'=>'on']);
accept_check($code===303 && $db->query('SELECT account_status FROM users WHERE user_id='.(int)$newStudent['user_id'])->fetchColumn()==='pending','Stale activation review refused');
// Sign in again while pending so activation still tests session revocation.
[$code,$html]=accept_http($anonymous,'/login.php');
accept_http($anonymous,'/login.php',['username'=>'new_http_student','password'=>$password,'csrf_token'=>accept_token($html)]);
[$code]=accept_http($staff,'/students.php',array_replace($review,['action'=>'activate','identity_confirmation'=>'on','review_version'=>3]));
accept_check($code===303 && $db->query('SELECT account_status FROM users WHERE user_id='.(int)$newStudent['user_id'])->fetchColumn()==='active','Librarian activation HTTP');
accept_check((int)$db->query("SELECT COUNT(*) FROM audit_events WHERE event_code='student.activate' AND actor_user_id=3")->fetchColumn()===1,'Librarian activation audited');
[$code]=accept_http($anonymous,'/requests.php'); accept_check($code===303,'Activation invalidates previous student session');
[$code,$html]=accept_http($anonymous,'/login.php'); accept_http($anonymous,'/login.php',['username'=>'new_http_student','password'=>$password,'csrf_token'=>accept_token($html)]);
[$code]=accept_http($staff,'/students.php?status=active',['student_id'=>$newStudent['user_id'],'action'=>'suspend','csrf_token'=>$staffToken]); accept_check($code===303,'Student suspension HTTP');
[$code]=accept_http($anonymous,'/catalog.php'); accept_check($code===303,'Suspension rejects existing student session');
[$code]=accept_http($staff,'/students.php?status=suspended',['student_id'=>$newStudent['user_id'],'action'=>'reactivate','csrf_token'=>$staffToken]); accept_check($code===303,'Student reactivation HTTP');
// Real multipart upload and catalog create/edit/copy/archive, then cleanup ONLY generated fixture files.
$imagePath=dirname(__DIR__).'/tmp/audit-image-'.bin2hex(random_bytes(6)).'.png';
$image=imagecreatetruecolor(12,18); imagepng($image,$imagePath); $fixtureCovers=[];
try {
    $data=['action'=>'create','title'=>'Fictional HTTP Audit','description'=>'Fictional audit description','authors'=>"Fixture Author One\nFixture Author Two",'quantity'=>3,'category_name'=>'Fictional audit','publication_year'=>2001,'csrf_token'=>$staffToken,'cover'=>new CURLFile($imagePath,'image/png','fixture.png')];
    curl_setopt_array($staff,[CURLOPT_URL=>'http://127.0.0.1:8017/manage_books.php',CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$data]); $body=curl_exec($staff); $code=curl_getinfo($staff,CURLINFO_RESPONSE_CODE);
    accept_check($code===303,'Multipart create catalog HTTP');
    $book=$db->query("SELECT * FROM books WHERE title='Fictional HTTP Audit'")->fetch(); $fixtureCovers[]=$book['cover_path']; $bookId=(int)$book['book_id'];
    accept_check((int)$db->query('SELECT COUNT(*) FROM book_copies WHERE book_id='.$bookId)->fetchColumn()===3 && (int)$db->query('SELECT COUNT(*) FROM book_authors WHERE book_id='.$bookId)->fetchColumn()===2,'Three copies and two authors saved');
    [$code,$html]=accept_http($student,'/catalog.php?id='.$bookId); accept_check($code===200 && str_contains($html,'Fictional HTTP Audit'),'Book details HTTP');
    [$code,$bytes]=accept_http($student,'/cover.php?id='.$bookId); accept_check($code===200 && str_starts_with($bytes,"\xff\xd8"),'Authorized re-encoded JPEG cover');
    $guest=curl_init(); curl_setopt_array($guest,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);
    [$code]=accept_http($guest,'/cover.php?id='.$bookId); accept_check($code===403,'Signed-out cover denied');
    [$code]=accept_http($guest,'/catalog.php'); accept_check($code===303,'Signed-out catalog login required');
    [$code]=accept_http($staff,'/manage_books.php',['action'=>'edit','book_id'=>$bookId,'title'=>'Fictional HTTP Audit Edited','description'=>'Edited fixture description','authors'=>"Fixture Author One\nFixture Author Two",'category_name'=>'Fictional audit','publication_year'=>2002,'csrf_token'=>$staffToken]); accept_check($code===303,'Catalog edit HTTP');
    [$code]=accept_http($staff,'/manage_books.php',['action'=>'add_copies','book_id'=>$bookId,'quantity'=>1,'csrf_token'=>$staffToken]); accept_check($code===303 && (int)catalog_book($db,$bookId)['available_copies']===4,'Add copies HTTP');
    $copyId=(int)$db->query('SELECT MIN(copy_id) FROM book_copies WHERE book_id='.$bookId)->fetchColumn();
    [$code]=accept_http($staff,'/manage_books.php',['action'=>'archive_copy','book_id'=>$bookId,'copy_id'=>$copyId,'csrf_token'=>$staffToken]); accept_check($code===303 && (int)catalog_book($db,$bookId)['available_copies']===3,'Archive copy HTTP availability');
    $data=['action'=>'cover','book_id'=>$bookId,'csrf_token'=>$staffToken,'cover'=>new CURLFile($imagePath,'image/png','replacement.png')];
    curl_setopt_array($staff,[CURLOPT_URL=>'http://127.0.0.1:8017/manage_books.php',CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$data]); curl_exec($staff); $code=curl_getinfo($staff,CURLINFO_RESPONSE_CODE);
    $newCover=$db->query('SELECT cover_path FROM books WHERE book_id='.$bookId)->fetchColumn(); $fixtureCovers[]=$newCover;
    accept_check($code===303 && $newCover!==$book['cover_path'],'Cover replacement HTTP');
    [$code]=accept_http($staff,'/manage_books.php',['action'=>'archive_book','book_id'=>$bookId,'csrf_token'=>$staffToken]); accept_check($code===303 && catalog_book($db,$bookId)===null,'Archive title HTTP');
    [$code]=accept_http($student,'/cover.php?id='.$bookId); accept_check($code===404,'Archived title cover unavailable');
    [$code,$html]=accept_http($student,'/logout.php');
    accept_check($code===200 && str_contains($html, 'Yes, log out') && str_contains($html, 'Stay signed in'), 'Logout GET requests confirmation');
    $logoutToken = accept_token($html);
    [$code]=accept_http($student,'/requests.php'); accept_check($code===200,'Opening logout confirmation preserves session');
    [$code]=accept_http($student,'/'); accept_check($code===200,'Cancel destination preserves session');
    [$code]=accept_http($student,'/logout.php',['csrf_token'=>$logoutToken]); accept_check($code===400,'Unconfirmed logout rejected');
    [$code]=accept_http($student,'/logout.php',['csrf_token'=>'forged','confirm'=>'yes']); accept_check($code===400,'Logout CSRF rejected');
    [$code]=accept_http($student,'/requests.php'); accept_check($code===200,'Rejected logout preserves session');
    [$code]=accept_http($student,'/logout.php',['csrf_token'=>$logoutToken,'confirm'=>'yes']); accept_check($code===303,'Confirmed logout HTTP');
    [$code]=accept_http($student,'/requests.php'); accept_check($code===303,'Logged-out history protected');
} finally {
    if(is_file($imagePath)) unlink($imagePath);
    foreach(array_unique($fixtureCovers) as $filename) {
        if(is_string($filename) && preg_match('/^upload-[a-f0-9]{32}\.jpg$/D',$filename)) {
            $path=dirname(__DIR__).'/private/covers/'.$filename; if(is_file($path)) unlink($path);
        }
    }
}
