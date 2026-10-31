<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/helpers.php';
require_once dirname(__DIR__).'/views/request_ui.php';
function ui_assert(bool $ok): void { if(!$ok) { throw new RuntimeException('Request UI assertion failed'); } }
ui_assert(borrowing_field_errors(0,'short')===['days'=>'Enter whole days from 1 to 30.','reason'=>'Enter a borrowing reason of 10–500 characters.']);
ui_assert(borrowing_field_errors(7,'Fictional study reason')===[]);
ui_assert(count(borrowing_field_errors([],[]))===2);
ui_assert(request_status_label('overdue')==='Overdue');
ui_assert(request_status_label('awaiting pickup')==='Ready for pickup');
ui_assert(substr_count(request_progress_html('active'),'aria-current="step"')===1);
ui_assert(!str_contains(request_progress_html('rejected'),'aria-current'));
ui_assert(str_contains(request_progress_html('expired'),'deadline passed'));
ui_assert(str_contains(request_progress_html('returned'),'Returned'));
ui_assert(str_contains(form_error_summary(['reason'=>'<script>']), '&lt;script&gt;'));
ui_assert(str_contains(form_error_summary(['reason'=>'Check reason']), 'href="#reason"'));
ui_assert(str_contains(field_error_html(['reason'=>'Check reason'],'reason'),'id="reason-error"'));
echo "Request status/progress, field validation and escaped error summary PASS.\n";
