<?php
declare(strict_types=1);
require_once __DIR__.'/time.php';

function student_account_details_html(array $student, bool $showSchoolId): string
{
    $html = '<dl class="student-account-facts">';
    if ($showSchoolId) {
        $html .= '<div><dt>School student ID</dt><dd>'.h($student['school_student_id'] ?? 'No school ID on file').'</dd></div>';
    }
    $dates = ['Registered'=>'created_at', 'First activated'=>'activated_at'];
    if ($student['account_status'] === 'suspended') { $dates['Last suspended'] = 'suspended_at'; }
    elseif (!empty($student['reactivated_at'])) { $dates['Last reactivated'] = 'reactivated_at'; }
    foreach ($dates as $label=>$field) {
        $value = !empty($student[$field]) ? display_manila_time($student[$field]) : 'Not recorded';
        $html .= '<div><dt>'.h($label).' (Manila)</dt><dd>'.h($value).'</dd></div>';
    }
    return $html.'</dl>';
}
