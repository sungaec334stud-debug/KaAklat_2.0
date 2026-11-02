<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/recovery.php';
require_once dirname(__DIR__).'/views/layout.php';
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
$html='<section class="panel auth-panel"><h1>Choose a new password</h1>'.form_error_summary($errors).'<p>Enter the one-time secret given privately by your librarian or administrator. It expires after 30 minutes. Recovery does not activate or unsuspend an account.</p><form method="post" novalidate><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'">';
foreach(['secret'=>'Recovery secret (64 characters)','password'=>'New password (12–72 UTF-8 bytes)','password_confirmation'=>'Repeat new password'] as $field=>$label) {
    $html.='<label for="'.$field.'">'.h($label).'</label><input id="'.$field.'" name="'.$field.'" type="password" autocomplete="'.($field==='secret'?'off':'new-password').'" required maxlength="'.($field==='secret'?64:72).'">'.field_error_html($errors,$field);
}
render_page('Choose new password',$html.'<button>Change password</button></form><p><a href="/login.php">Return to login</a></p></section>');
