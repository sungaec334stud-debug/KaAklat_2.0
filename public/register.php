<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/views/auth_entry.php';

start_application_session();
header('Cache-Control: no-store');
$config = application_config();
$errors = [];
$values = ['display_name' => '', 'username' => '', 'school_student_id' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['school_student_id'] = is_string($_POST['school_student_id'] ?? null) ? $_POST['school_student_id'] : '';
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

$errorList = $errors === [] ? '' : '<div class="flash flash-error error-summary" role="alert" tabindex="-1"><ul><li>' . implode('</li><li>', array_map('h', $errors)) . '</li></ul></div>';
$content = '<section class="auth-wrap register" aria-labelledby="auth-title"><div class="auth-shell">' . auth_entry_scene(true)
    . '<div class="auth-main enter-later"><p class="eyebrow">Create a student account</p><h1 id="auth-title">Start your next chapter.</h1>'
    . '<p class="auth-intro">Have your school ID handy. It only takes a moment.</p>' . $errorList
    . '<form method="post" action="/register.php" novalidate>'
    . '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'
    . '<div class="field"><label for="school_student_id">School student ID</label><input id="school_student_id" name="school_student_id" autocomplete="off" maxlength="11" pattern="[0-9]{2}-[0-9]{4}-[0-9]{3}" aria-describedby="school-id-hint" required value="' . h($values['school_student_id']) . '">'
    . '<p id="school-id-hint" class="hint">Include the dashes, as in 26-0123-456.</p></div>'
    . '<div class="form-grid"><div class="field"><label for="display_name">Display name</label><input id="display_name" name="display_name" autocomplete="name" maxlength="100" required value="' . h($values['display_name']) . '"></div>'
    . '<div class="field"><label for="username">Username</label><input id="username" name="username" autocomplete="username" maxlength="32" pattern="[a-zA-Z0-9_]{3,32}" aria-describedby="username-hint" required value="' . h($values['username']) . '">'
    . '<p id="username-hint" class="hint">3–32 letters, numbers or underscores.</p></div></div>'
    . '<div class="field"><label for="password">Password</label><div class="password-wrap"><input id="password" name="password" autocomplete="new-password" type="password" minlength="12" maxlength="72" aria-describedby="password-hint" required>'
    . '<button class="password-toggle" type="button" data-password="password" aria-controls="password" aria-label="Show password" aria-pressed="false" hidden>Show</button></div>'
    . '<p id="password-hint" class="hint">Use a short phrase with 12–72 basic letters, numbers, spaces or symbols.</p></div>'
    . '<div class="access-note"><div><strong>One quick check before you borrow.</strong><p>Show your physical school ID to a librarian to activate your account.</p></div></div>'
    . '<button class="btn submit" type="submit">Create account</button></form>'
    . '<p class="auth-switch">Already have an account? <a href="/login.php">Log in</a></p></div></div></section>';

render_page('Create account', $content);
