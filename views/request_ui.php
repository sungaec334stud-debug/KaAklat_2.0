<?php
declare(strict_types=1);
require_once __DIR__ . '/time.php';
function request_status_label(string $state): string
{
    return match($state) { 'pending'=>'Pending review','awaiting pickup'=>'Ready for pickup','active'=>'Active loan','overdue'=>'Overdue','rejected'=>'Rejected','expired'=>'Pickup expired','returned'=>'Returned','student','librarian','cancelled'=>'Cancelled',default=>'Check with librarian' };
}
function request_progress_html(string $state): string
{
    $stage=match($state) { 'pending'=>0,'awaiting pickup'=>1,'active','overdue'=>2,'returned'=>3,default=>-1 };
    if($stage<0) { return '<p class="request-notice">'.h(match($state) { 'rejected'=>'This request was rejected. Read the librarian’s explanation below.', 'expired'=>'The collection deadline passed. Submit a new request if you still need this book.', default=>'This request is closed. Its history is kept here.' }).'</p>'; }
    $html='<ol class="request-progress" aria-label="Borrowing progress">';
    foreach(['Requested','Approved','Collected','Returned'] as $i=>$label) {
        $html.='<li'.($i===$stage?' aria-current="step"':'').' class="'.($i<=$stage?'step-reached':'step-upcoming').'">'.h($label).'<span class="sr-only">'.($i<$stage?' completed':($i===$stage?' current':' not yet recorded')).'</span></li>';
    }
    return $html.'</ol>';
}
function form_error_summary(array $errors): string
{
    if(!$errors) { return ''; }
    $html='<div class="flash flash-error error-summary" role="alert" tabindex="-1"><strong>Please check the following</strong><ul>';
    foreach($errors as $field=>$message) { $html.='<li>'.($field!==''?'<a href="#'.h($field).'">'.h($message).'</a>':h($message)).'</li>'; }
    return $html.'</ul></div>';
}
function field_error_html(array $errors,string $field): string
{
    return isset($errors[$field])?'<p class="field-error" id="'.h($field).'-error">'.h($errors[$field]).'</p>':'';
}
function borrowing_field_errors(mixed $days,mixed $reason): array
{
    $errors=[];
    if(!is_scalar($days) || is_bool($days) || filter_var($days,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>30]])===false) { $errors['days']='Enter whole days from 1 to 30.'; }
    if(!is_string($reason) || mb_strlen(trim($reason))<10 || mb_strlen(trim($reason))>500) { $errors['reason']='Enter a borrowing reason of 10–500 characters.'; }
    return $errors;
}
function borrowing_form_html(int $bookId,string $days,string $reason,array $errors): string
{
    return '<form method="post" novalidate><input type="hidden" name="action" value="create"><input type="hidden" name="book_id" value="'.h($bookId).'"><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'"><label for="days">Requested days (1–30)</label><input id="days" name="days" type="number" min="1" max="30" required value="'.h($days).'">'.field_error_html($errors,'days').'<label for="reason">Why do you need the book for this duration? (10–500 characters)</label><textarea id="reason" name="reason" minlength="10" maxlength="500" required>'.h($reason).'</textarea>'.field_error_html($errors,'reason').'<button>Submit request</button></form>';
}

function request_history_time(string $utc): string
{
    return display_manila_time($utc);
}

function request_history_row_html(array $row, bool $activeStudent): string
{
    $state = $row['lifecycle'];
    $submitted = request_history_time($row['requested_at']);
    $pickupDeadline = $row['decision'] === 'approved'
        ? request_history_time((new DateTimeImmutable($row['decided_at'], new DateTimeZone('UTC')))->modify('+48 hours')->format('Y-m-d H:i:s'))
        : '';
    $due = $row['due_date'] === null ? '' : (new DateTimeImmutable($row['due_date']))->format('M j, Y');
    $returned = $row['returned_at'] === null ? '' : request_history_time($row['returned_at']);
    $cancelled = $row['cancelled_at'] === null ? '' : request_history_time($row['cancelled_at']);
    $keyDate = match ($state) {
        'awaiting pickup' => 'Pick up by ' . $pickupDeadline,
        'active' => 'Return by ' . $due,
        'overdue' => 'Was due ' . $due,
        'returned' => 'Returned ' . $returned,
        'rejected' => 'Decision recorded ' . request_history_time($row['decided_at']),
        'expired' => 'Pickup window closed ' . $pickupDeadline,
        'student', 'librarian' => 'Cancelled ' . $cancelled,
        'cancelled' => $cancelled === '' ? 'Request cancelled' : 'Cancelled ' . $cancelled,
        default => 'Submitted ' . $submitted,
    };
    $cover = $row['cover_path'] === null
        ? '<span class="request-thumbnail-placeholder">KA</span>'
        : '<img src="/cover.php?id=' . h($row['book_id']) . '" alt="" loading="lazy" width="36" height="50">';
    $html = '<details class="request-row"><summary><span class="request-thumbnail" aria-hidden="true">' . $cover
        . '</span><span class="request-row-copy"><span class="request-row-title"><strong>' . h($row['title'])
        . '</strong><span class="status-badge status-' . h(str_replace(' ', '-', $state)) . '">' . h(request_status_label($state))
        . '</span></span><span class="request-key-date' . ($state === 'overdue' ? ' request-key-overdue' : '') . '">'
        . h($keyDate) . ($state === 'cancelled' && $cancelled === '' ? '' : ' (Manila)') . '</span></span>'
        . '<span class="request-row-toggle"><span class="request-show">View details</span><span class="request-hide">Hide details</span></span>'
        . '</summary><div class="request-row-detail">' . request_progress_html($state)
        . '<p>Requested ' . h($row['requested_days']) . ' days · Submitted ' . h($submitted) . ' (Manila)</p>';

    if ($row['decision'] === 'approved') {
        $html .= '<p>Approved duration: <strong>' . h($row['approved_days']) . ' days</strong><br>Pickup deadline: ' . h($pickupDeadline) . ' (Manila)</p>';
        if ($state === 'awaiting pickup') {
            $html .= '<p>Your copy is held until the pickup deadline. Your borrowing period has not started.</p>';
        }
    }
    if ($row['checked_out_at'] !== null) {
        $html .= '<p>Picked up: ' . h(request_history_time($row['checked_out_at'])) . ' (Manila)<br>Due date: <strong>'
            . h($due) . '</strong> (Manila; the entire due date is allowed)</p>';
    }
    if ($returned !== '') {
        $html .= '<p><strong>Returned:</strong> ' . h($returned) . ' (Manila). This loan is complete.</p>';
    }
    $html .= '<p><strong>Original borrowing reason:</strong><br>' . nl2br(h($row['request_reason'])) . '</p>';
    if (in_array($row['decision'], ['approved', 'rejected'], true) && $row['decision_note'] !== null && $row['decision_note'] !== '') {
        $html .= '<p class="book-description"><strong>Librarian’s explanation:</strong><br>' . nl2br(h($row['decision_note'])) . '</p>';
    }
    if ($cancelled !== '') {
        $label = $row['cancellation_kind'] === 'expired' ? 'Expiry recorded'
            : 'Cancelled by ' . ($row['cancellation_kind'] === 'student' ? 'you' : 'a librarian');
        $html .= '<p>' . h($label) . ': ' . h($cancelled) . ' (Manila)</p>';
        if ($row['cancellation_note'] !== null) {
            $html .= '<p><strong>Cancellation explanation:</strong> ' . nl2br(h($row['cancellation_note'])) . '</p>';
        }
    }
    if (($state === 'pending' && $activeStudent) || $state === 'awaiting pickup') {
        $allocation = $state === 'awaiting pickup';
        $html .= '<form method="post">' . ($allocation
            ? '<p>Cancel only if you no longer need this allocation. Its copy will be released; a new request is needed if you change your mind.</p>' : '')
            . '<input type="hidden" name="action" value="' . ($allocation ? 'cancel_approved' : 'cancel') . '">'
            . '<input type="hidden" name="request_id" value="' . h($row['request_id']) . '">'
            . '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'
            . '<button class="button-caution" aria-label="' . h(($allocation ? 'Cancel uncollected allocation for ' : 'Cancel pending request for ') . $row['title'])
            . '">' . ($allocation ? 'Cancel allocation' : 'Cancel pending request') . '</button></form>';
    }
    return $html . '</div></details>';
}
