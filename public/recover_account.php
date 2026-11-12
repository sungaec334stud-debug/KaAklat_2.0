<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/recovery.php';
require_once dirname(__DIR__).'/views/layout.php';
require_once dirname(__DIR__).'/views/request_ui.php';
start_application_session(); header('Cache-Control: no-store'); header('Referrer-Policy: no-referrer');
try {
    $db=database_connection(application_config()['database']); $user=current_user($db);
    if ($user===null) redirect('/login.php'); require_recovery_staff($user);
    if (!in_array($_SERVER['REQUEST_METHOD'],['GET','POST'],true)) {http_response_code(405); header('Allow: GET, POST'); exit;}
    $errors=[]; $username=''; $confirmed=false;
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        unset($_SESSION['recovery_delivery']);
        try {
            if (array_diff(array_keys($_POST),['username','identity_confirmation','csrf_token'])) throw new InvalidArgumentException('Unexpected form fields. Refresh the page.');
            $username=is_string($_POST['username']??null)?$_POST['username']:'';
            $confirmed=($_POST['identity_confirmation']??null)==='on';
            if (!$confirmed) {$errors['identity_confirmation']='Confirm the account holder’s identity in person first.'; throw new InvalidArgumentException('Confirm identity.');}
            $secret=issue_recovery($db,$_POST['username']??null,$_POST['csrf_token']??null,$confirmed);
            $_SESSION['recovery_delivery']=['secret'=>$secret,'issued'=>time(),'actor'=>(int)$user['user_id']];
            redirect('/recover_account.php');
        } catch(InvalidArgumentException $e) {http_response_code(400); if(!$errors) $errors=['username'=>$e->getMessage()];}
    }
    $delivery=$_SESSION['recovery_delivery']??null; unset($_SESSION['recovery_delivery']);
    $html='<section class="panel"><h1>Help recover an account</h1>'.form_error_summary($errors).'<p>Verify the account holder in person against the demonstration identity roster. Librarians can assist students; administrators can assist students or staff. Never ask for their current password.</p>';
    if (is_array($delivery) && $delivery['actor']===(int)$user['user_id'] && time()-$delivery['issued']<1800) {
        $html.='<section class="management-section"><h2>Deliver privately now</h2><p>This secret is displayed once. Give it only to the verified account holder. It expires 30 minutes after issuance; issuing another invalidates this one.</p><p><code>'.h($delivery['secret']).'</code></p><p>Ask them to open <a href="/reset_password.php">Choose a new password</a> and enter it there. Do not send the secret in a URL or screenshot.</p></section>';
    }
    $html.='<div class="recovery-layout"><div class="recovery-form"><form method="post" novalidate><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'"><label for="username">Account username</label><input id="username" name="username" maxlength="32" required value="'.h($username).'">'.field_error_html($errors,'username').'<label for="identity_confirmation"><input id="identity_confirmation" type="checkbox" name="identity_confirmation" required'.($confirmed?' checked':'').'> I verified this account holder’s identity in person.</label>'.field_error_html($errors,'identity_confirmation').'<button>Issue private recovery secret</button></form></div><aside class="recovery-guidance"><h2>A private handover</h2><ol><li><strong>Check identity</strong><br>Confirm the account holder in person before proceeding.</li><li><strong>Issue a recovery secret</strong><br>The secret is shown once and expires after 30 minutes.</li><li><strong>Hand it over privately</strong><br>The account holder chooses their own new password.</li></ol></aside></div></section>';
    render_page('Account recovery',$html);
} catch(DomainException $e) {http_response_code(403); render_page('Access denied','<p role="alert">'.h($e->getMessage()).'</p>');}
catch(Throwable $e) {$ref=bin2hex(random_bytes(8)); error_log('Recovery issuance ['.$ref.'] code='.$e->getCode()); http_response_code(500); render_page('Recovery unavailable','<p role="alert">Recovery could not be completed. Refresh before retrying. Reference: '.h($ref).'</p>');}
