<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/returns.php';
require_once dirname(__DIR__).'/views/layout.php';
require_once dirname(__DIR__).'/views/return_ui.php';
require_once dirname(__DIR__).'/views/request_ui.php';
start_application_session();
header('Cache-Control: no-store');
try {
    $db=database_connection(application_config()['database']);
    $user=current_user($db);
    if($user===null) { redirect('/login.php'); }
    require_request_librarian($user);
    $method=$_SERVER['REQUEST_METHOD'];
    if(!in_array($method,['GET','POST'],true)) { http_response_code(405); header('Allow: GET, POST'); exit; }
    [$search,$page]=catalog_parameters($_GET);
    $error=''; $failedId=null; $message=''; $checked=($_POST['receipt_confirmation']??null)==='on';
    if($method==='POST') {
        try {
            if(array_diff(array_keys($_POST),['request_id','csrf_token','action','receipt_confirmation']) || ($_POST['action']??null)!=='return') { throw new InvalidArgumentException('Unexpected form fields. Reload the page.'); }
            $id=filter_var($_POST['request_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
            if($id===false) { throw new InvalidArgumentException('Choose a valid loan.'); }
            $failedId=$id;
            if(($_POST['receipt_confirmation']??null)!=='on') { throw new InvalidArgumentException('Confirm that you physically received the listed copy.'); }
            $result=record_return($db,$id,$_POST['csrf_token']??null);
            flash_set('success',return_success_message($result,function() use($db,$id) {
                $q=$db->prepare('SELECT b.title,c.copy_code FROM loans l JOIN borrow_requests r ON r.request_id=l.request_id JOIN books b ON b.book_id=r.book_id JOIN book_copies c ON c.copy_id=l.copy_id WHERE r.request_id=?');
                $q->execute([$id]); return $q->fetch();
            }));
            redirect('/returns.php?'.http_build_query(['q'=>$search,'page'=>$page]));
        } catch(InvalidArgumentException $e) { http_response_code(400); $message=$e->getMessage(); }
    }
    $rows=list_returns($db,$page,$search,$pagination); $page=$pagination['page'];
    if($message!=='') {
        $visible=array_filter(array_slice($rows,0,20),fn(array $row):bool => (int)$row['request_id']===$failedId);
        $error=form_error_summary([($visible?($checked?'return-':'receipt-').$failedId:'')=>$message]);
    }
    $content='<section class="panel"><h1>Receive returned books</h1>'.$error.'<p>Confirm only after physically receiving the exact copy shown. Overdue books and borrowers with inactive accounts can still return books.</p><form method="get" class="catalog-search"><label for="q">Search book, student or copy code</label><input id="q" name="q" maxlength="100" value="'.h($search).'"><button>Search</button> <a href="/returns.php">Clear</a></form>';
    if(!$rows) { $content.='<div class="empty-state"><h2>No outstanding loans on this page</h2><p>Try another search or return to the first page.</p><a href="/returns.php">Clear search / first page</a></div>'; }
    foreach(array_slice($rows,0,20) as $row) {
        $content.=return_card_html($row,$failedId,$message,$checked);
    }
    $content .= pagination_html($pagination, static fn(int $target): string => '?'.http_build_query(['q'=>$search,'page'=>$target]), 'Return pages', 'loans');
    render_page('Returns',$content.'</section>');
} catch(DomainException $e) {
    http_response_code(403); render_page('Access denied','<section class="panel"><h1>Access denied</h1><p role="alert">'.h($e->getMessage()).'</p><a href="/">Dashboard</a></section>');
} catch(InvalidArgumentException $e) {
    http_response_code(400); render_page('Check return','<p role="alert">'.h($e->getMessage()).'</p><a href="/returns.php">Returns</a>');
} catch(Throwable $e) {
    $reference=bin2hex(random_bytes(8)); error_log('Return failure ['.$reference.'] code='.$e->getCode());
    http_response_code(500); render_page('Return unavailable','<p role="alert">Return could not be completed. Check the current loan before retrying. Reference: '.h($reference).'</p>');
}
