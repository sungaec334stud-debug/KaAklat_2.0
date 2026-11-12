<?php
declare(strict_types=1);
require_once __DIR__ . '/time.php';
require_once __DIR__ . '/circulation_ui.php';
function return_success_message(string $result,callable $loadContext): string
{
    $label='';
    try { $context=$loadContext(); if(is_array($context)) { $label=$context['title'].' · copy '.$context['copy_code'].'. '; } } catch(Throwable) { /* Optional display context must not undo a committed success. */ }
    return $label.($result==='already_returned'?'This return was already recorded. The original return details are unchanged.':'Physical return recorded. The student can see the completed loan in their history.');
}
function return_card_html(array $row,?int $failedId,string $message,bool $checked): string
{
    $id=(int)$row['request_id']; $failed=$failedId===$id;
    $pickup=display_manila_time($row['checked_out_at']);
    $due=(new DateTimeImmutable($row['due_date'],new DateTimeZone('Asia/Manila')))->format('M j, Y');
    $receiptError=$failed && !$checked;
    return '<article class="management-section" id="return-'.$id.'" tabindex="-1"><span class="status-badge '.($row['overdue']?'status-overdue':'status-active').'">'.($row['overdue']?'Overdue':'Active loan').'</span><header class="circulation-heading">'.circulation_cover_html($row).'<h2>'.h($row['title']).'</h2></header><p>Borrower: <strong>'.h($row['student_name']).'</strong> ('.h($row['username']).')<br>Receive copy: <strong>'.h($row['copy_code']).'</strong><br>Picked up: '.h($pickup).' (Manila)<br>Due date: '.h($due).' (Manila)</p>'.($failed?'<p class="field-error" id="return-'.$id.'-error">'.h($message).'</p>':'').'<form method="post"><input type="hidden" name="action" value="return"><input type="hidden" name="request_id" value="'.$id.'"><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'"><label for="receipt-'.$id.'"><input id="receipt-'.$id.'" type="checkbox" name="receipt_confirmation" required'.($failed && $checked?' checked':'').($receiptError?' aria-invalid="true" aria-describedby="return-'.$id.'-error"':'').'> I physically received copy '.h($row['copy_code']).' from '.h($row['student_name']).'.</label><button aria-label="'.h('Confirm return of '.$row['title'].', copy '.$row['copy_code']).'">Confirm physical return</button></form></article>';
}
