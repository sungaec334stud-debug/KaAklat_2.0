<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

start_application_session();
header('Cache-Control: no-store');

if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
    if (!isset($_SESSION['user_id'], $_SESSION['session_version'])) {
        redirect('/login.php');
    }
    require_once dirname(__DIR__) . '/views/layout.php';
    $token = h(csrf_token());
    render_page('Log out', '<section class="panel auth-panel logout-confirmation">'
        . '<h1>Log out of KaAklat?</h1><p>You can sign in again whenever you need to.</p>'
        . '<p>Any changes you have not submitted will not be saved.</p>'
        . '<form method="post" action="/logout.php"><input type="hidden" name="csrf_token" value="' . $token . '">'
        . '<div class="logout-actions"><a class="button button-secondary" href="/">Stay signed in</a>'
        . '<button type="submit" name="confirm" value="yes">Yes, log out</button></div></form></section>');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)
    || ($_POST['confirm'] ?? null) !== 'yes') {
    http_response_code(400);
    exit('Invalid request.');
}

clear_login_session();
flash_set('success', 'You are now logged out.');
redirect('/login.php');
