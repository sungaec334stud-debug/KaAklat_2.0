<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/borrow_requests.php';
require_once dirname(__DIR__) . '/views/layout.php';
start_application_session();
header('Cache-Control: no-store');
try {
    $db=database_connection(application_config()['database']);
    $user=current_user($db);
    if ($user === null) { redirect('/login.php'); }
    if ($user['role'] !== 'student' || !in_array($user['account_status'], ['pending', 'active'], true)) {
        throw new DomainException('Only students can view their borrowing requests.');
    }
    $method=$_SERVER['REQUEST_METHOD'];
    if (!in_array($method,['GET','POST'],true)) { http_response_code(405); header('Allow: GET, POST'); exit; }
    $filter=request_history_filter($_GET['status'] ?? 'all');
    $errors='';
    if ($method === 'POST') {
        try {
            $action=$_POST['action'] ?? null;
            $allowed=$action === 'create' ? ['action','book_id','days','reason','csrf_token'] : ['action','request_id','csrf_token'];
            if (array_diff(array_keys($_POST),$allowed)) { throw new InvalidArgumentException('Unexpected form fields. Please reload the page.'); }
            $id=filter_var($_POST[$action === 'create' ? 'book_id' : 'request_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
            if ($id === false) { throw new InvalidArgumentException('Choose a valid book or request.'); }
            if ($action === 'create') {
                create_borrow_request($db,$id,$_POST['days'] ?? null,$_POST['reason'] ?? null,$_POST['csrf_token'] ?? null);
                flash_set('success','Request submitted. A librarian will review it. No copy is reserved yet.');
            } elseif ($action === 'cancel') {
                cancel_borrow_request($db,$id,$_POST['csrf_token'] ?? null);
                flash_set('success','Your pending request is cancelled.');
            } else { throw new InvalidArgumentException('Choose a valid action.'); }
            redirect('/requests.php?status=' . ($action === 'create' ? 'pending' : $filter));
        } catch (InvalidArgumentException $e) { http_response_code(400); $errors='<p role="alert">'.h($e->getMessage()).'</p>'; }
    }
    $content='<section class="panel"><h1>My borrowing requests</h1>'.$errors.'<p><a href="/catalog.php">Find a book</a></p>';
    $bookId=$_GET['book'] ?? ($method === 'POST' && ($_POST['action'] ?? null) === 'create' ? ($_POST['book_id'] ?? null) : null);
    if ($bookId !== null && $user['account_status'] === 'active') {
        $id=filter_var($bookId,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $book=$id === false ? null : catalog_book($db,$id);
        if (!$book) { throw new InvalidArgumentException('This book is no longer listed.'); }
        $days=is_string($_POST['days'] ?? null) ? $_POST['days'] : '7';
        $reason=is_string($_POST['reason'] ?? null) ? $_POST['reason'] : '';
        $content.='<h2>Request '.h($book['title']).'</h2><p>The librarian decides the final duration. Borrowing starts at physical pickup.</p>';
        if ((int)$book['available_copies'] < 1) { $content.='<p>No copies available. Please check again later.</p>'; }
        else { $content.='<form method="post"><input type="hidden" name="action" value="create"><input type="hidden" name="book_id" value="'.h($id).'"><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'"><label for="days">Requested days (1–30)</label><input id="days" name="days" type="number" min="1" max="30" required value="'.h($days).'"><label for="reason">Why do you need the book for this duration? (10–500 characters)</label><textarea id="reason" name="reason" minlength="10" maxlength="500" required>'.h($reason).'</textarea><button>Submit request</button></form>'; }
    }
    $page=filter_var($_GET['page'] ?? '1',FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>10000]]);
    if ($page === false) { throw new InvalidArgumentException('Choose a valid page.'); }
    $tabs=['all'=>'All requests','pending'=>'Pending','rejected'=>'Rejected','awaiting_pickup'=>'Awaiting pickup','active'=>'Active'];
    $content.='<nav class="account-tabs" aria-label="Borrowing request status"><ul>';
    foreach($tabs as $key=>$label) {
        $content.='<li><a href="?status='.h($key).'"'.($filter===$key?' aria-current="page"':'').'>'.h($label).'</a></li>';
    }
    $content.='</ul></nav>';
    if($filter==='active') { $content.='<p>Active loans include overdue books. Check each due date below.</p>'; }
    $rows=own_borrow_requests($db,$page,$filter);
    if (!$rows) { $content.='<p>No requests in this tab on this page. Choose another tab or browse the catalog.</p>'; }
    foreach(array_slice($rows,0,20) as $row) {
        $content.='<article class="panel"><h2>'.h($row['title']).'</h2><p>Status: <strong>'.h(ucfirst($row['lifecycle'])).'</strong></p><p>Requested '.h($row['requested_days']).' days · Submitted '.h((new DateTimeImmutable($row['requested_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Manila'))->format('M j, Y g:i a')).' (Manila)</p><p>'.nl2br(h($row['request_reason'])).'</p>';
        if ($row['decision'] === 'approved') {
            $deadline = (new DateTimeImmutable($row['decided_at'], new DateTimeZone('UTC')))
                ->modify('+48 hours')->setTimezone(new DateTimeZone('Asia/Manila'))->format('M j, Y g:i a');
            $content .= '<p>Approved duration: <strong>' . h($row['approved_days']) . ' days</strong><br>Pickup deadline: ' . h($deadline) . ' (Manila)</p>';
            if ($row['lifecycle'] === 'awaiting pickup') { $content .= '<p>Your copy is held until the pickup deadline. Your borrowing period has not started.</p>'; }
        }
        if ($row['checked_out_at'] !== null) {
            $pickup=(new DateTimeImmutable($row['checked_out_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Manila'))->format('M j, Y g:i a');
            $due=(new DateTimeImmutable($row['due_date'],new DateTimeZone('Asia/Manila')))->format('M j, Y');
            $content.='<p>Picked up: '.h($pickup).' (Manila)<br>Due date: <strong>'.h($due).'</strong> (Manila; the entire due date is allowed)</p>';
        }
        if (in_array($row['decision'], ['approved', 'rejected'], true) && $row['decision_note'] !== null && $row['decision_note'] !== '') {
            $content .= '<p class="book-description"><strong>Librarian’s explanation:</strong><br>' . nl2br(h($row['decision_note'])) . '</p>';
        }
        if ($row['decision'] === 'pending' && $user['account_status'] === 'active') { $content.='<form method="post"><input type="hidden" name="action" value="cancel"><input type="hidden" name="request_id" value="'.h($row['request_id']).'"><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'"><button>Cancel pending request</button></form>'; }
        $content.='</article>';
    }
    $content.='<nav aria-label="Request pages">';
    if ($page>1) { $content.='<a href="?status='.h($filter).'&amp;page='.($page-1).'">Previous</a> '; }
    if (count($rows)>20 && $page<10000) { $content.='<a href="?status='.h($filter).'&amp;page='.($page+1).'">Next</a>'; }
    render_page('My requests',$content.'</nav></section>');
} catch (DomainException $e) {
    http_response_code(403); render_page('Borrowing unavailable','<section class="panel"><h1>Borrowing unavailable</h1><p role="alert">'.h($e->getMessage()).'</p><a href="/">Dashboard</a></section>');
} catch (InvalidArgumentException $e) {
    http_response_code(400); render_page('Check your request','<p role="alert">'.h($e->getMessage()).'</p><a href="/requests.php">My requests</a>');
} catch (Throwable $e) {
    $reference=bin2hex(random_bytes(8));
    error_log('Borrow request failure ['.$reference.'] code='.$e->getCode());
    http_response_code(500); render_page('Requests unavailable','<p role="alert">We could not complete this action. Refresh your requests before retrying. Reference: '.h($reference).'</p>');
}
