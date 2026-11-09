<?php
// Included by the existing isolated HTTP harness. No regular database writes.
$settingsGuest = curl_init();
curl_setopt_array($settingsGuest, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_COOKIEFILE=>'', CURLOPT_TIMEOUT=>10]);
[$code] = accept_http($settingsGuest, '/settings.php');
accept_check($code === 303, 'Settings requires authentication');
curl_close($settingsGuest);
foreach ($clients as $role => $client) {
    [$code, $html] = accept_http($client, '/settings.php');
    accept_check($code === 200 && str_contains($html, '<h1>Settings</h1>'), "$role settings access");
    accept_check(str_contains($html, 'href="/settings.php" aria-current="page"') && str_contains($html, 'name="appearance"'), "$role settings navigation and theme controls");
    accept_check(!str_contains($html, 'password_hash') && !str_contains($html, $password), 'Settings excludes private credentials');
    [$code] = accept_http($client, '/settings.php', ['role'=>'administrator','appearance'=>'dark']);
    accept_check($code === 405, 'Settings refuses POST mutation');
}
$settingsAccount = $db->prepare('INSERT INTO users(username,display_name,password_hash,role,account_status) VALUES(?,?,?,?,?)');
$settingsAccount->execute(['settings_pending','<Fictional settings student>', $hash, 'student','pending']);
$settingsId = (int)$db->lastInsertId();
$settingsClient = curl_init();
$settingsHeaders = [];
curl_setopt_array($settingsClient, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_COOKIEFILE=>'', CURLOPT_TIMEOUT=>10,
    CURLOPT_HEADERFUNCTION=>static function ($client, string $line) use (&$settingsHeaders): int { $settingsHeaders[] = $line; return strlen($line); }]);
[$code,$html] = accept_http($settingsClient, '/login.php');
accept_http($settingsClient, '/login.php', ['username'=>'settings_pending','password'=>$password,'csrf_token'=>accept_token($html)]);
[$code,$html] = accept_http($settingsClient, '/settings.php?user_id=1');
accept_check($code === 200 && !str_contains($html, 'Acceptance Student'), 'Pending student settings ignores forged account selectors');
accept_check(str_contains($html, '&lt;Fictional settings student&gt;') && !str_contains($html, '<Fictional settings student>'), 'Settings escapes current display name');
accept_check((bool)preg_grep('/^Cache-Control: private, no-store/i', $settingsHeaders), 'Settings prevents private page caching');
$db->prepare('UPDATE users SET session_version=session_version+1 WHERE user_id=?')->execute([$settingsId]);
[$code] = accept_http($settingsClient, '/settings.php');
accept_check($code === 303, 'Settings refuses stale sessions');
foreach (['suspended','archived'] as $settingsStatus) {
    $db->prepare("UPDATE users SET account_status='pending' WHERE user_id=?")->execute([$settingsId]);
    [$code,$html] = accept_http($settingsClient, '/login.php');
    accept_http($settingsClient, '/login.php', ['username'=>'settings_pending','password'=>$password,'csrf_token'=>accept_token($html)]);
    $db->prepare('UPDATE users SET account_status=? WHERE user_id=?')->execute([$settingsStatus,$settingsId]);
    [$code] = accept_http($settingsClient, '/settings.php');
    accept_check($code === 303, "Settings refuses freshly $settingsStatus account");
}
curl_close($settingsClient);
