<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';

start_application_session();
header('Cache-Control: no-store');
$config = application_config();
$errors = [];
$values = ['display_name' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['display_name'] = is_string($_POST['display_name'] ?? null) ? $_POST['display_name'] : '';
    $values['username'] = is_string($_POST['username'] ?? null) ? $_POST['username'] : '';

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form expired. Please try again.';
    } else {
        try {
            register_student(
                database_connection($config['database']),
                $values['username'],
                $values['display_name'],
                is_string($_POST['password'] ?? null) ? $_POST['password'] : '',
                $_POST['school_student_id'] ?? null
            );
            flash_set('success', 'Your account was created. A librarian must activate borrowing before you can request books.');
            redirect('/login.php');
        } catch (InvalidArgumentException $exception) {
            $errors[] = $exception->getMessage();
        } catch (Throwable) {
            $reference = bin2hex(random_bytes(8));
            error_log('KaAklat registration failed. Reference: ' . $reference);
            $errors[] = 'We could not create the account. Please try again later. Reference: ' . $reference;
        }
    }
}

$errorList = $errors === [] ? '' : '<div class="flash flash-error" role="alert"><ul><li>' . implode('</li><li>', array_map('h', $errors)) . '</li></ul></div>';
$content = '<section class="panel auth-panel auth-entry" aria-labelledby="auth-title"><div class="auth-entry-main">'
    . '<h1 id="auth-title">Create a student account</h1><p>Have your school ID handy.</p>' . $errorList
    . '<form method="post" action="/register.php" novalidate>'
    . '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'
    . '<label for="school_student_id">School student ID</label><input id="school_student_id" name="school_student_id" autocomplete="off" maxlength="11" pattern="[0-9]{2}-[0-9]{4}-[0-9]{3}" aria-describedby="school-id-hint" required>'
    . '<p id="school-id-hint" class="auth-entry-hint">Include the dashes, as in 26-0123-456.</p>'
    . '<label for="display_name">Display name</label><input id="display_name" name="display_name" autocomplete="name" maxlength="100" required value="' . h($values['display_name']) . '">'
    . '<label for="username">Username</label><input id="username" name="username" autocomplete="username" maxlength="32" pattern="[a-zA-Z0-9_]{3,32}" aria-describedby="username-hint" required value="' . h($values['username']) . '">'
    . '<p id="username-hint" class="auth-entry-hint">3–32 letters, numbers or underscores.</p>'
    . '<label for="password">Password</label><input id="password" name="password" autocomplete="new-password" type="password" minlength="12" maxlength="72" aria-describedby="password-hint" required>'
    . '<p id="password-hint" class="auth-entry-hint">Use a short phrase with 12–72 basic letters, numbers, spaces or symbols.</p>'
    . '<button type="submit">Create account</button></form>'
    . '<p class="auth-entry-switch">Already have an account? <a href="/login.php">Log in</a></p></div>'
    . '<aside class="auth-entry-guide" aria-labelledby="auth-guide-title"><p class="auth-entry-guide-label">Library access</p>'
    . '<h2 id="auth-guide-title">What happens next?</h2><ol>'
    . '<li>Show your physical school ID to a librarian.</li>'
    . '<li>Once the librarian activates your account, you can request books.</li>'
    . '</ol></aside></section>';

render_page('Create account', $content);
