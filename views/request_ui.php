<?php
declare(strict_types=1);
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
