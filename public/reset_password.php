<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/recovery.php';
require_once dirname(__DIR__).'/views/layout.php';
require_once dirname(__DIR__).'/views/auth_entry.php';
require_once dirname(__DIR__).'/views/request_ui.php';
start_application_session(); header('Cache-Control: no-store'); header('Referrer-Policy: no-referrer');
if (!in_array($_SERVER['REQUEST_METHOD'],['GET','POST'],true)) {http_response_code(405); header('Allow: GET, POST'); exit;}
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        if (array_diff(array_keys($_POST),['secret','password','password_confirmation','csrf_token'])) throw new InvalidArgumentException('Unexpected form fields. Refresh the page.');
        if (!is_string($_POST['password']??null) || $_POST['password']!==($_POST['password_confirmation']??null)) {$errors['password_confirmation']='The passwords must match.'; throw new InvalidArgumentException('Check the passwords.');}
        try {validate_new_password($_POST['password']);} catch(InvalidArgumentException $e) {$errors['password']=$e->getMessage(); throw $e;}
        redeem_recovery(database_connection(application_config()['database']),$_POST['secret']??null,$_POST['password'],$_POST['csrf_token']??null);
        flash_set('success','Password changed. Sign in with your new password. Your account’s activation or suspension status is unchanged.'); redirect('/login.php');
    } catch(InvalidArgumentException $e) {http_response_code(400); if(!$errors) $errors=['secret'=>$e->getMessage()];}
    catch(Throwable $e) {$ref=bin2hex(random_bytes(8)); error_log('Recovery redemption ['.$ref.'] code='.$e->getCode()); http_response_code(500); $errors=[''=>'Unable to change the password. Ask staff for help. Reference: '.$ref];}
}
$html='<section class="auth-wrap" aria-labelledby="auth-title"><div class="auth-shell">'.auth_entry_scene().'<div class="auth-main enter-later"><p class="eyebrow">Account recovery</p><h1 id="auth-title">Choose a new password</h1>'.form_error_summary($errors).'<p class="auth-intro">Use the private recovery code from your librarian or administrator to choose a new password.</p><form method="post" action="/reset_password.php" novalidate><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'">';
$hints = ['secret'=>'Paste the complete code from staff. It works once and expires after 30 minutes.',
    'password'=>'Use a short phrase with 12–72 basic letters, numbers, spaces or symbols.',
    'password_confirmation'=>'Enter the same new password again.'];
$visibilityLabels = ['secret'=>'recovery code','password'=>'new password','password_confirmation'=>'password confirmation'];
foreach(['secret'=>'Recovery code','password'=>'New password','password_confirmation'=>'Confirm new password'] as $field=>$label) {
    $html.='<div class="field"><label for="'.$field.'">'.h($label).'</label><div class="password-wrap"><input id="'.$field.'" name="'.$field.'" type="password" autocomplete="'.($field==='secret'?'off':'new-password').'" aria-describedby="'.$field.'-hint" required maxlength="'.($field==='secret'?64:72).'">'
        .password_visibility_button_html($field,$visibilityLabels[$field]).'</div><p id="'.$field.'-hint" class="hint">'.h($hints[$field]).'</p>'.field_error_html($errors,$field).'</div>';
}
render_page('Choose new password',$html.'<details class="help recovery-code-help"><summary>Need a recovery code?</summary><p>Ask your librarian or administrator in person. If your code has expired, ask for a new one. Keep it private.</p></details><button class="btn submit" type="submit">Change password</button></form><p class="hint">This changes your password only. Your account’s approval or suspension status stays the same.</p><p class="auth-switch"><a href="/login.php">Return to login</a></p></div></div></section>');
