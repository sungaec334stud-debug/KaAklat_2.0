<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/approved_cancellation.php';
require_once dirname(__DIR__).'/views/request_ui.php';
require_once dirname(__DIR__).'/views/layout.php';
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
    $error=''; $failedId=null; $cancelNote=''; $cancelError='';
    if($method==='POST') {
        try {
            if(array_diff(array_keys($_POST),($_POST['action']??null)==='cancel_approved'?['request_id','csrf_token','action','cancellation_note']:['request_id','csrf_token','action']) || !in_array($_POST['action']??null,['pickup','cancel_approved'],true)) { throw new InvalidArgumentException('Unexpected form fields. Reload the page.'); }
            $id=filter_var($_POST['request_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
            if($id===false) { throw new InvalidArgumentException('Choose a valid pickup.'); }
            if ($_POST['action']==='cancel_approved') {
                $failedId=$id;
                $cancelNote=is_string($_POST['cancellation_note']??null)?$_POST['cancellation_note']:'';
                $result=cancel_approved_request($db,$id,$_POST['csrf_token']??null,$_POST['cancellation_note']??null);
                flash_set($result==='expired'?'error':'success',$result==='expired'?'This allocation expired; it was not voluntarily cancelled.':($result==='already_cancelled'?'Already cancelled. Original cancellation details are unchanged.':'Uncollected allocation cancelled. History is preserved.'));
                redirect('/pickups.php?'.http_build_query(['q'=>$search,'page'=>$page]));
            }
            $result=record_pickup($db,$id,$_POST['csrf_token']??null);
            flash_set($result==='expired'?'error':'success',match($result) {
                'expired'=>'This collection deadline has passed. The allocation was released; do not hand over the book.',
                'already_collected'=>'Pickup was already recorded. Its original date and due date are unchanged.',
                default=>'Physical pickup recorded. The student can now see the loan and due date.'
            });
            redirect('/pickups.php?'.http_build_query(['q'=>$search,'page'=>$page]));
        } catch(InvalidArgumentException $e) { http_response_code(400); $cancelError=$failedId!==null?$e->getMessage():''; $error=form_error_summary([''=>$e->getMessage()]); }
    }
    $rows=list_pickups($db,$page,$search);
    if($cancelError!=='' && in_array($failedId,array_map('intval',array_column(array_slice($rows,0,20),'request_id')),true)) {
        $error=form_error_summary(['cancel-note-'.$failedId=>$cancelError]);
    }
    $content='<section class="panel"><h1>Physical book pickup</h1>'.$error.'<p>Confirm only when you physically hand the allocated copy to the student. The borrowing period starts at handover.</p><form method="get" class="catalog-search"><label for="q">Search book or student</label><input id="q" name="q" maxlength="100" value="'.h($search).'"><button>Search</button> <a href="/pickups.php">Clear</a></form>';
    if(!$rows) { $content.='<p>No pickups on this page. Try clearing the search or return to the first page.</p><a href="/pickups.php">Clear search / first page</a>'; }
    foreach(array_slice($rows,0,20) as $row) {
        $deadline=(new DateTimeImmutable($row['decided_at'],new DateTimeZone('UTC')))->modify('+48 hours')->setTimezone(new DateTimeZone('Asia/Manila'))->format('M j, Y g:i a');
        $content.='<article class="management-section"><h2>'.h($row['title']).'</h2><p><strong>'.h($row['student_name']).'</strong> ('.h($row['username']).')</p><p>Allocated copy: <strong>'.h($row['copy_code']).'</strong><br>Approved duration: '.h($row['approved_days']).' days<br>Collection deadline: '.h($deadline).' (Manila)</p>';
        if((bool)$row['expired']) { $content.='<p class="flash flash-error">Expired — do not hand over this copy. This student needs a new request.</p>'; }
        else { $content.='<p>Awaiting collection. Eligibility is checked again when confirming.</p>'; }
        $content.='<form method="post"><input type="hidden" name="action" value="pickup"><input type="hidden" name="request_id" value="'.h($row['request_id']).'"><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'"><button aria-label="'.h(((bool)$row['expired']?'Release expired allocation for ':'Confirm pickup of ').$row['title'].', copy '.$row['copy_code']).'">'.((bool)$row['expired']?'Release expired allocation':'Confirm physical handover').'</button></form>';
        if (!(bool)$row['expired']) {
            $noteId='cancel-note-'.$row['request_id'];
            $value=$failedId===(int)$row['request_id']?$cancelNote:'';
            $content.='<form method="post" class="management-section"><h3>Cancel this allocation</h3><p>For '.h($row['student_name']).' — '.h($row['title']).', copy '.h($row['copy_code']).'. This releases the copy and preserves history. Do not cancel after handover.</p><input type="hidden" name="action" value="cancel_approved"><input type="hidden" name="request_id" value="'.h($row['request_id']).'"><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'"><label for="'.h($noteId).'">Explanation for the student (1–500 characters)</label>'.($failedId===(int)$row['request_id']?'<p class="field-error" id="'.h($noteId).'-error">'.h($cancelError).'</p>':'').'<textarea'.($failedId===(int)$row['request_id']?' aria-invalid="true" aria-describedby="'.h($noteId).'-error"':'').' id="'.h($noteId).'" name="cancellation_note" required maxlength="500">'.h($value).'</textarea><button class="button-caution" aria-label="'.h('Cancel allocation for '.$row['student_name'].', '.$row['title'].', copy '.$row['copy_code']).'">Cancel allocation</button></form>';
        }
        $content.='</article>';
    }
    $content.='<nav class="catalog-pagination" aria-label="Pickup pages">';
    if($page>1) { $content.='<a href="?'.h(http_build_query(['q'=>$search,'page'=>$page-1])).'">Previous</a>'; }
    if(count($rows)>20 && $page<10000) { $content.='<a href="?'.h(http_build_query(['q'=>$search,'page'=>$page+1])).'">Next</a>'; }
    render_page('Physical pickup',$content.'</nav></section>');
} catch(DomainException $e) {
    http_response_code(403); render_page('Access denied','<section class="panel"><h1>Access denied</h1><p role="alert">'.h($e->getMessage()).'</p><a href="/">Dashboard</a></section>');
} catch(InvalidArgumentException $e) {
    http_response_code(400); render_page('Check pickup','<p role="alert">'.h($e->getMessage()).'</p><a href="/pickups.php">Pickups</a>');
} catch(Throwable $e) {
    $reference=bin2hex(random_bytes(8)); error_log('Pickup failure ['.$reference.'] code='.$e->getCode());
    http_response_code(500); render_page('Pickup unavailable','<p role="alert">Pickup could not be completed. Check the current record before retrying. Reference: '.h($reference).'</p>');
}
