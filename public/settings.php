<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/views/settings.php';

start_application_session();
header('Cache-Control: private, no-store');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
try {
    $user = current_user(database_connection(application_config()['database']));
    if ($user === null) { redirect('/login.php'); }
    $allowed = ($user['role'] === 'student' && in_array($user['account_status'], ['active', 'pending'], true))
        || (in_array($user['role'], ['librarian', 'administrator'], true) && $user['account_status'] === 'active');
    if (!$allowed) {
        http_response_code(403);
        render_page('Settings unavailable', '<section class="panel"><h1>Settings unavailable</h1><p>Please sign in with an eligible account.</p><a href="/">Go to dashboard</a></section>');
        exit;
    }
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD');
        render_page('Settings', '<section class="panel"><h1>Settings</h1><p>Appearance is saved in your browser, not submitted to your account.</p><a href="/settings.php">Open settings</a></section>');
        exit;
    }
    render_page('Settings', appearance_settings_html());
} catch (Throwable $error) {
    $reference = bin2hex(random_bytes(8));
    error_log('Settings failure [' . $reference . '] code=' . $error->getCode());
    http_response_code(503);
    render_page('Settings temporarily unavailable', '<section class="panel"><h1>Settings temporarily unavailable</h1><p>Please try again shortly. Reference: ' . h($reference) . '</p><a href="/settings.php">Try again</a></section>');
}
