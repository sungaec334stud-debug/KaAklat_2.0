<?php

// Included only by the isolated HTTP harness; all mutations are fictional scratch fixtures.
declare(strict_types=1);

$profileHttp = curl_init();
$newRegistrationAge = $db->prepare('SELECT TIMESTAMPDIFF(SECOND,created_at,UTC_TIMESTAMP()) FROM users WHERE user_id=?');
$newRegistrationAge->execute([(int)$newStudent['user_id']]);
$newRegistrationAge = (int)$newRegistrationAge->fetchColumn();
accept_check($newRegistrationAge >= 0 && $newRegistrationAge < 120, 'New HTTP registration timestamp uses UTC');
$profileHeaders = [];
curl_setopt_array($profileHttp, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_COOKIEFILE=>'', CURLOPT_TIMEOUT=>10,
    CURLOPT_HEADERFUNCTION=>static function ($client, string $line) use (&$profileHeaders): int {
        $profileHeaders[] = trim($line);
        return strlen($line);
    }]);
[$code] = accept_http($profileHttp, '/profile.php');
accept_check($code === 303, 'Profile requires sign-in');
accept_check(in_array('Cache-Control: private, no-store', $profileHeaders, true), 'Profile redirect is not cacheable');
foreach (['librarian','admin'] as $role) {
    [$code,$body] = accept_http($clients[$role], '/profile.php');
    accept_check($code === 403 && !str_contains($body, 'href="/profile.php"'), 'Profile denied and hidden for ' . $role);
}
[$code,$html] = accept_http($profileHttp, '/login.php');
[$code] = accept_http($profileHttp, '/login.php', ['username'=>'accept_student','password'=>$password,'csrf_token'=>accept_token($html)]);
accept_check($code === 303, 'Profile fixture student login');
$originalProfile = $db->query('SELECT display_name,created_at FROM users WHERE user_id=1')->fetch();
$db->prepare('UPDATE users SET display_name=?,created_at=? WHERE user_id=1')->execute(['<script>Fictional & Profile</script>','2026-11-03 17:30:00']);
$db->exec("UPDATE users SET school_student_id='06-0001-001' WHERE user_id=1");
$db->exec("UPDATE users SET school_student_id='06-0001-002' WHERE user_id=2");
$profileHeaders = [];
[$code,$html] = accept_http($profileHttp, '/profile.php');
$profileDom = new DOMDocument();
@$profileDom->loadHTML($html);
$profileCurrent = (new DOMXPath($profileDom))->query('//nav[@aria-label="Main navigation"]/a[@href="/profile.php" and @aria-current="page"]');
accept_check($code === 200 && str_contains($html, '<h1>My Profile</h1>') && $profileCurrent->length === 1
    && trim($profileCurrent->item(0)->textContent) === 'My Profile', 'Authenticated profile and current navigation');
accept_check(in_array('Cache-Control: private, no-store', $profileHeaders, true), 'Private profile no-store header');
accept_check(str_contains($html, '06-0001-001') && !str_contains($html, '06-0001-002'), 'HTTP profile shows only the signed-in student’s school ID');
accept_check(str_contains($html, '&lt;script&gt;Fictional &amp; Profile&lt;/script&gt;') && !str_contains($html, '<script>Fictional'), 'HTTP profile escapes display name');
accept_check(str_contains($html, '4 November 2026, 1:30 AM'), 'HTTP profile registration shown in Manila');
foreach (['password_hash','session_version','token_hash','accept_other'] as $privateValue) {
    accept_check(!str_contains($html, $privateValue), 'HTTP profile excludes private field or other identity');
}
foreach (['pending','awaiting_pickup','active'] as $filter) {
    accept_check(str_contains($html, 'href="/requests.php?status=' . $filter . '"'), 'HTTP profile links existing tab ' . $filter);
}
foreach (['?user_id=2','?username=accept_other','?user_id[]=2'] as $forged) {
    [$code,$html] = accept_http($profileHttp, '/profile.php' . $forged);
    accept_check($code === 400 && !str_contains($html, 'Other Student'), 'Forged profile selector refused');
}
[$code] = accept_http($profileHttp, '/profile.php', ['display_name'=>'Forged edit','user_id'=>2]);
accept_check($code === 405 && $db->query('SELECT display_name FROM users WHERE user_id=1')->fetchColumn() === '<script>Fictional & Profile</script>', 'Profile POST cannot edit account');
$db->prepare('INSERT INTO users(username,display_name,password_hash,role,account_status) VALUES(?,?,?,?,?)')
    ->execute(['profile_http_pending','Fictional Pending Profile',password_hash($password,PASSWORD_DEFAULT),'student','pending']);
$pendingClient = curl_init();
curl_setopt_array($pendingClient, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_COOKIEFILE=>'', CURLOPT_TIMEOUT=>10]);
[$code,$html] = accept_http($pendingClient, '/login.php');
[$code] = accept_http($pendingClient, '/login.php', ['username'=>'profile_http_pending','password'=>$password,'csrf_token'=>accept_token($html)]);
[$code,$html] = accept_http($pendingClient, '/profile.php');
accept_check($code === 200 && str_contains($html, 'Waiting for librarian activation') && str_contains($html, 'No current borrowing activity'), 'Pending profile and zero-count guidance');
[$code] = accept_http($pendingClient, '/catalog.php');
accept_check($code === 200, 'Pending profile does not change catalog browsing');
foreach (['suspended','archived'] as $status) {
    $db->exec("UPDATE users SET account_status='pending' WHERE username='profile_http_pending'");
    [$code,$html] = accept_http($pendingClient, '/login.php');
    [$code] = accept_http($pendingClient, '/login.php', ['username'=>'profile_http_pending','password'=>$password,'csrf_token'=>accept_token($html)]);
    accept_check($code === 303, 'Fresh pending fixture login before ' . $status);
    $db->prepare('UPDATE users SET account_status=? WHERE username=?')->execute([$status,'profile_http_pending']);
    [$code] = accept_http($pendingClient, '/profile.php');
    accept_check($code === 303, 'Inactive profile session denied: ' . $status);
}
$db->exec('RENAME TABLE loans TO profile_unavailable_loans'); // This harness's new fictional DB only.
try {
    [$code,$html] = accept_http($profileHttp, '/profile.php');
    accept_check($code === 503 && str_contains($html, 'Profile temporarily unavailable')
        && str_contains($html, '>Try again</a>') && !str_contains($html, 'SQLSTATE'), 'Profile database failure gives safe retry state');
} finally {
    $db->exec('RENAME TABLE profile_unavailable_loans TO loans');
}
$db->exec('UPDATE users SET session_version=session_version+1 WHERE user_id=1');
[$code] = accept_http($profileHttp, '/profile.php');
accept_check($code === 303, 'Stale profile session rejected over HTTP');
$db->prepare('UPDATE users SET display_name=?,created_at=? WHERE user_id=1')->execute([$originalProfile['display_name'],$originalProfile['created_at']]);
curl_close($profileHttp);
curl_close($pendingClient);
