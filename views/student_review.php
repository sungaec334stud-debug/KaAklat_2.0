<?php
declare(strict_types=1);

/** Separate native forms keep each identity confirmation attached to its own action. */
function student_identity_review_html(array $student, string $actionUrl, string $token): string
{
    $id=(int)$student['user_id'];
    $hasClaim=$student['school_student_id']!==null;
    $form='<form method="post" action="'.h($actionUrl).'" class="student-review-form">'
        .'<input type="hidden" name="csrf_token" value="'.h($token).'">'
        .'<input type="hidden" name="student_id" value="'.h($id).'">'
        .'<input type="hidden" name="review_version" value="'.h($student['session_version']).'">';
    $confirm=static fn(string $text): string=>'<label class="student-review-confirmation"><input type="checkbox" name="identity_confirmation" required><span>'.h($text).'</span></label>';
    $html='<div class="student-identity-review"><p>School student ID: <strong>'.h($student['school_student_id'] ?? 'No ID assigned').'</strong></p>';
    if($hasClaim){
        $html.='<section class="student-review-action"><h4>Activate account</h4><p>Confirm the student’s identity before enabling borrowing.</p>'.$form
            .$confirm('I checked this student’s physical school ID in person.')
            .'<button name="action" value="activate">Activate account</button></form></section>';
    }
    $html.='<details class="student-claim-review"><summary>Resolve a student ID claim</summary>'
        .'<p>Choose the action that matches the verified school ID. Correcting or releasing an ID keeps this account pending.</p>'
        .'<div class="student-claim-actions"><section class="student-review-action"><h4>Correct the school ID</h4><p>Use this when the account holder entered the wrong ID.</p>'.$form
        .'<label class="student-review-field" for="replacement-id-'.h($id).'">Correct school student ID<input id="replacement-id-'.h($id).'" name="replacement_id" maxlength="11" autocomplete="off" pattern="[0-9]{2}-[0-9]{4}-[0-9]{3}" required aria-describedby="replacement-hint-'.h($id).'"></label>'
        .'<p class="hint" id="replacement-hint-'.h($id).'">Include the dashes, for example 26-0123-456.</p>'
        .$confirm('I verified the account holder’s correct school ID in person.')
        .'<button name="action" value="correct_id">Save corrected ID</button></form></section>';
    if($hasClaim){
        $html.='<section class="student-review-action"><h4>Release a mistaken claim</h4><p>Remove this ID from the account so the rightful student can use it. This does not activate the account.</p>'.$form
            .$confirm('I checked the physical school ID and verified that this claim should be released.')
            .'<button class="button-caution" name="action" value="release_id">Release mistaken claim</button></form></section>';
    }
    return $html.'</div></details></div>';
}
