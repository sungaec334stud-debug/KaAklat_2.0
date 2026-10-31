<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/request_decisions.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/views/request_ui.php';

start_application_session();
header('Cache-Control: no-store');

try {
    $db = database_connection(application_config()['database']);
    $user = current_user($db);
    if ($user === null) { redirect('/login.php'); }
    require_request_librarian($user);
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, ['GET', 'POST'], true)) {
        http_response_code(405);
        header('Allow: GET, POST');
        exit;
    }
    [$search, $page] = catalog_parameters($_GET);
    $errors = '';
    $failedId = null;
    $fieldErrors = [];
    if ($method === 'POST') {
        try {
            if (array_diff(array_keys($_POST), ['action', 'request_id', 'days', 'note', 'csrf_token'])) {
                throw new InvalidArgumentException('Unexpected form fields. Please reload the page.');
            }
            $id = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $action = $_POST['action'] ?? null;
            if ($id === false || !is_string($action) || !in_array($action, ['approve', 'reject'], true)) {
                throw new InvalidArgumentException('Choose a valid request and decision.');
            }
            $failedId = $id;
            decide_borrow_request($db, $id, $action, $_POST['days'] ?? null, $_POST['note'] ?? null, $_POST['csrf_token'] ?? null);
            flash_set('success', $action === 'approve'
                ? 'Request approved. One copy is held for 48 hours. Borrowing has not started yet.'
                : 'Request rejected. The student can see your explanation.');
            redirect('/review_requests.php?' . http_build_query(['q' => $search, 'page' => $page]));
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            $field = str_contains($e->getMessage(),'duration') || str_contains($e->getMessage(),'Explain') || str_contains($e->getMessage(),'Explanation') ? 'note-' . $failedId : (str_contains($e->getMessage(),'whole days') ? 'days-' . $failedId : '');
            $fieldErrors = [$field => $e->getMessage()];
            $errors = form_error_summary($fieldErrors);
        }
    }
    $rows = list_review_requests($db, $page, $search);
    $content = '<section class="panel"><h1>Review borrowing requests</h1>' . $errors
        . '<p>Review the student’s reason and choose the final duration. Approval holds one copy for 48 hours; the loan starts only at physical pickup.</p>'
        . '<form method="get" class="catalog-search"><label for="q">Search pending requests by book or student</label>'
        . '<input id="q" name="q" maxlength="100" value="' . h($search) . '"><button>Search</button> <a href="/review_requests.php">Clear</a></form>';
    if (!$rows) {
        $content .= '<div class="empty-state"><h2>No pending reviews here</h2><p>Try clearing the search or check ready-to-collect books.</p><a href="/review_requests.php">Clear search / first page</a> · <a href="/pickups.php">View pickups</a></div>';
    }
    foreach (array_slice($rows, 0, 20) as $row) {
        $id = (int) $row['request_id'];
        $days = $failedId === $id && is_string($_POST['days'] ?? null) ? $_POST['days'] : (string) $row['requested_days'];
        $note = $failedId === $id && is_string($_POST['note'] ?? null) ? $_POST['note'] : '';
        $submitted = (new DateTimeImmutable($row['requested_at'], new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Asia/Manila'))->format('M j, Y g:i a');
        $eligibility = $row['account_status'] !== 'active' ? 'Account is not active.'
            : ((bool) $row['has_overdue'] ? 'Overdue loan: approval is blocked.'
            : ((int) $row['live_allowance'] >= 5 ? 'Five-book allowance is full.' : 'Account and allowance checks currently allow review.'));
        $content .= '<article class="management-section"><h2>' . h($row['title']) . '</h2>'
            . '<p><strong>' . h($row['student_name']) . '</strong> (' . h($row['username']) . ') · ' . h(ucfirst($row['account_status'])) . '</p>'
            . '<p>' . h($eligibility) . ' Approved pickups and active loans: ' . h($row['live_allowance']) . '/5. Available copies: ' . h($row['available_copies']) . '.</p>'
            . '<p>Requested duration: <strong>' . h($row['requested_days']) . ' days</strong><br>Submitted ' . h($submitted) . ' (Manila)</p>'
            . '<p class="book-description">' . nl2br(h($row['request_reason'])) . '</p>'
            . '<form method="post" novalidate><input type="hidden" name="request_id" value="' . h($id) . '">'
            . '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'
            . '<label for="days-' . $id . '">Approved duration (1–30 days)</label><input id="days-' . $id . '" name="days" type="number" min="1" max="30" value="' . h($days) . '">'
            . field_error_html($fieldErrors,'days-' . $id)
            . '<label for="note-' . $id . '">Explanation for the student (maximum 500 characters)</label>'
            . '<p id="note-help-' . $id . '">Required when rejecting or changing the requested duration. Eligibility and availability are checked again when you approve.</p>'
            . '<textarea id="note-' . $id . '" name="note" maxlength="500" rows="3" aria-describedby="note-help-' . $id . '">' . h($note) . '</textarea>'
            . field_error_html($fieldErrors,'note-' . $id)
            . '<div class="decision-actions"><section><h3>Approve collection</h3><p>Approve <strong>'.h($row['title']).'</strong> for <strong>'.h($row['student_name']).'</strong>. One copy will be held for 48 hours using the duration above.</p><button name="action" value="approve">Approve request</button></section><section><h3>Reject this request</h3><p>Reject this request for '.h($row['student_name']).'. Explain the reason above so the student understands your decision.</p><button class="button-danger" name="action" value="reject">Reject request</button></section></div></form></article>';
    }
    $content .= '<nav class="catalog-pagination" aria-label="Pending request pages">';
    if ($page > 1) { $content .= '<a href="?' . h(http_build_query(['q' => $search, 'page' => $page - 1])) . '">Previous</a>'; }
    if (count($rows) > 20 && $page < 10000) { $content .= '<a href="?' . h(http_build_query(['q' => $search, 'page' => $page + 1])) . '">Next</a>'; }
    render_page('Review requests', $content . '</nav></section>');
} catch (DomainException $e) {
    http_response_code(403);
    render_page('Access denied', '<section class="panel"><h1>Access denied</h1><p role="alert">' . h($e->getMessage()) . '</p><a href="/">Dashboard</a></section>');
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    render_page('Check your request', '<p role="alert">' . h($e->getMessage()) . '</p><a href="/review_requests.php">Review requests</a>');
} catch (Throwable $e) {
    $reference = bin2hex(random_bytes(8));
    error_log('Borrow decision failure [' . $reference . '] code=' . $e->getCode());
    http_response_code(500);
    render_page('Review unavailable', '<p role="alert">We could not complete this action. Refresh the request list before retrying. Reference: ' . h($reference) . '</p>');
}
