<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';

start_application_session();
$config = application_config();
$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = (string) ($_POST['username'] ?? '');

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
$content = $errorList
    . '<section class="panel auth-panel"><div class="page-eyebrow">Welcome back</div><h1>Your library awaits.</h1><p>Sign in to your KaAklat account.</p><form method="post" action="/login.php" novalidate>'
    . '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'
    . '<label for="username">Username</label><input id="username" name="username" maxlength="32" required value="' . h($username) . '">'
    . '<label for="password">Password</label><input id="password" name="password" type="password" required>'
    . '<button type="submit">Log in</button></form><p><a href="/register.php">Need an account? Register as a student</a></p></section>';

render_page('Log in', $content);
