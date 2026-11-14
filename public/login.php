<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/views/auth_entry.php';

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

$errorList = $errors === [] ? '' : '<div class="flash flash-error error-summary" role="alert" tabindex="-1"><ul><li>' . implode('</li><li>', array_map('h', $errors)) . '</li></ul></div>';
$content = '<section class="auth-wrap" aria-labelledby="auth-title"><div class="auth-shell">' . auth_entry_scene()
    . '<div class="auth-main enter-later"><p class="eyebrow">Your library account</p><h1 id="auth-title">Welcome back.</h1>'
    . '<p class="auth-intro">Log in with your username and password.</p>' . $errorList
    . '<form method="post" action="/login.php" novalidate>'
    . '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'
    . '<div class="field"><label for="username">Username</label><input id="username" name="username" autocomplete="username" maxlength="32" required value="' . h($username) . '"></div>'
    . '<div class="field"><label for="password">Password</label><div class="password-wrap"><input id="password" name="password" autocomplete="current-password" type="password" required>'
    . '<button class="password-toggle" type="button" data-password="password" aria-controls="password" aria-label="Show password" aria-pressed="false" hidden>Show</button></div></div>'
    . '<button class="btn submit" type="submit">Log in</button></form>'
    . '<p class="auth-switch">New student? <a href="/register.php">Create an account</a></p>'
    . '<details class="help" id="recovery"><summary>Forgot your password?</summary><p>Ask your librarian or administrator in person for a recovery secret.</p>'
    . '<a href="/reset_password.php">Use a recovery secret</a></details></div></div></section>';

render_page('Log in', $content);
