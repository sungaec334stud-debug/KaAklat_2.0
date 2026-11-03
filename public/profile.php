<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/student_profile.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/views/student_profile.php';

start_application_session();
header('Cache-Control: private, no-store');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
try {
    $db = database_connection(application_config()['database']);
    if (current_user($db) === null) { redirect('/login.php'); }
    $profile = student_profile($db);
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','HEAD'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD');
        render_page('View-only profile', '<section class="panel"><h1>View-only profile</h1><p>Profile details cannot be changed here.</p><a href="/profile.php">Open My Profile</a></section>');
        exit;
    }
    if ($_GET !== []) {
        http_response_code(400);
        render_page('Open your own profile', '<section class="panel"><h1>Open your own profile</h1><p>This page always shows the account you are signed in with.</p><a href="/profile.php">Open My Profile</a></section>');
        exit;
    }
    render_page('My Profile', student_profile_html($profile));
} catch (DomainException $error) {
    http_response_code(403);
    render_page('Profile unavailable', '<section class="panel"><h1>Profile unavailable</h1><p>' . h($error->getMessage()) . '</p><a href="/">Go to dashboard</a></section>');
} catch (Throwable $error) {
    $reference = bin2hex(random_bytes(8));
    error_log('Student profile failure [' . $reference . '] code=' . $error->getCode());
    http_response_code(503);
    render_page('Profile temporarily unavailable', '<section class="panel"><h1>Profile temporarily unavailable</h1><p>Please try again shortly. Reference: ' . h($reference) . '</p><a href="/profile.php">Try again</a> · <a href="/">Go to dashboard</a></section>');
}
