<?php
// Included only by the isolated fictional HTTP harness.
$confirmationClient = curl_init();
curl_setopt_array($confirmationClient, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 10]);
[$code, $html] = accept_http($confirmationClient, '/register.php');
$confirmationToken = accept_token($html);
$registration = ['school_student_id' => '26-7777-001', 'username' => 'confirmation_http',
    'display_name' => '<Fictional Confirmation>', 'password' => 'Fictional confirmation 123', 'csrf_token' => $confirmationToken];
$beforeUsers = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$beforeAudit = (int) $db->query('SELECT COUNT(*) FROM audit_events')->fetchColumn();
foreach ([null, '', 'Different fictional password', ['forged'], ' Fictional confirmation 123'] as $confirmation) {
    $payload = $registration;
    if ($confirmation !== null) { $payload['password_confirmation'] = $confirmation; }
    [$code, $html] = accept_http($confirmationClient, '/register.php', $payload);
    accept_check($code === 200 && str_contains($html, 'password_confirmation-error'), 'Missing, unequal or malformed confirmation rejected with field error');
    accept_check((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === $beforeUsers
        && (int) $db->query('SELECT COUNT(*) FROM audit_events')->fetchColumn() === $beforeAudit, 'Confirmation failure creates no account or audit');
    accept_check(!str_contains($html, $registration['password']) && !str_contains($html, 'Different fictional password')
        && str_contains($html, '&lt;Fictional Confirmation&gt;'), 'Both secrets omitted while escaped nonsecret values remain');
    accept_check(str_contains($html, 'href="#password_confirmation"') && str_contains($html, 'aria-invalid="true"')
        && str_contains($html, 'password_confirmation-hint password_confirmation-error'), 'Server confirmation error has linked accessible feedback without JavaScript');
}
$registration['password_confirmation'] = $registration['password'];
[$code, $html] = accept_http($confirmationClient, '/register.php', $registration + ['unused' => 'ignored']);
accept_check($code === 303, 'Matching confirmation creates the normal pending account');
$created = $db->query("SELECT * FROM users WHERE username='confirmation_http'")->fetch();
accept_check($created['role'] === 'student' && $created['account_status'] === 'pending'
    && password_verify($registration['password'], $created['password_hash']), 'Original pending/student policy and password hash preserved');
accept_check((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === $beforeUsers + 1
    && (int) $db->query('SELECT COUNT(*) FROM audit_events')->fetchColumn() === $beforeAudit + 1, 'Successful registration inserts exactly one account and audit');
[$code] = accept_http($confirmationClient, '/register.php', $registration);
accept_check($code === 200 && (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === $beforeUsers + 1, 'Repeated matching registration cannot duplicate account');
[$code, $html] = accept_http($confirmationClient, '/register.php', array_replace($registration, ['csrf_token' => 'forged', 'username' => 'confirmation_forged']));
accept_check($code === 200 && str_contains($html, 'Your form expired')
    && (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === $beforeUsers + 1, 'Matching confirmation cannot bypass CSRF');
curl_close($confirmationClient);
