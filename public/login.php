<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';

start_application_session();
$config = application_config();
$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = is_string($_POST['username'] ?? null) ? $_POST['username'] : '';

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form expired. Please try again.';
    } else {
        try {
            $user = authenticate_user(database_connection($config['database']), $username, $_POST['password'] ?? '');

            if ($user === null) {
                $errors[] = 'Invalid username or password.';
            } else {
                establish_login_session($user);
                flash_set('success', 'You are now signed in.');
                redirect('/');
            }
        } catch (Throwable) {
            $errors[] = 'We could not sign you in. Please try again later.';
        }
    }
}

$errorList = $errors === [] ? '' : '<div class="flash flash-error" role="alert"><ul><li>' . implode('</li><li>', array_map('h', $errors)) . '</li></ul></div>';
$content = '<section class="panel auth-panel auth-entry" aria-labelledby="auth-title"><div class="auth-entry-main">'
    . '<h1 id="auth-title">Log in to KaAklat</h1><p>Enter your username and password.</p>' . $errorList
    . '<form method="post" action="/login.php" novalidate>'
    . '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'
    . '<label for="username">Username</label><input id="username" name="username" autocomplete="username" maxlength="32" required value="' . h($username) . '">'
    . '<label for="password">Password</label><input id="password" name="password" autocomplete="current-password" type="password" required>'
    . '<button type="submit">Log in</button></form>'
    . '<p class="auth-entry-switch">New student? <a href="/register.php">Create an account</a></p></div>'
    . '<aside class="auth-entry-guide" aria-labelledby="auth-guide-title"><p class="auth-entry-guide-label">Library help desk</p>'
    . '<h2 id="auth-guide-title">Forgot your password?</h2>'
    . '<p>Ask your librarian or administrator in person for a recovery secret.</p>'
    . '<a href="/reset_password.php">Use a recovery secret</a></aside></section>';

render_page('Log in', $content);
