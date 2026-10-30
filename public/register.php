<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';

start_application_session();
$config = application_config();
$errors = [];
$values = ['display_name' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['display_name'] = (string) ($_POST['display_name'] ?? '');
    $values['username'] = (string) ($_POST['username'] ?? '');

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form expired. Please try again.';
    } else {
        try {
            register_student(
                database_connection($config['database']),
                $values['username'],
                $values['display_name'],
                (string) ($_POST['password'] ?? '')
            );
            flash_set('success', 'Your account was created. A librarian must activate borrowing before you can request books.');
            redirect('/login.php');
        } catch (InvalidArgumentException $exception) {
            $errors[] = $exception->getMessage();
        } catch (Throwable $exception) {
            $reference = bin2hex(random_bytes(8));
            error_log(sprintf(
                'KaAklat registration failed [%s]: %s: %s',
                $reference,
                $exception::class,
                $exception->getMessage()
            ));
            if ($config['app_env'] === 'local') {
                $errors[] = 'Local diagnostic. Reference: ' . $reference . '. Error: ' . $exception->getMessage();
            } else {
                $errors[] = 'We could not create the account. Please try again later. Reference: ' . $reference;
            }
        }
    }
}

$errorList = $errors === [] ? '' : '<div class="flash flash-error" role="alert"><ul><li>' . implode('</li><li>', array_map('h', $errors)) . '</li></ul></div>';
$content = $errorList
    . '<section class="panel"><h1>Create student account</h1><p>Use a username with 3-32 lowercase letters, numbers, or underscores. Passwords need 12-72 characters.</p>'
    . '<form method="post" action="/register.php" novalidate>'
    . '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'
    . '<label for="display_name">Display name</label><input id="display_name" name="display_name" maxlength="100" required value="' . h($values['display_name']) . '">'
    . '<label for="username">Username</label><input id="username" name="username" maxlength="32" pattern="[a-zA-Z0-9_]{3,32}" required value="' . h($values['username']) . '">'
    . '<label for="password">Password</label><input id="password" name="password" type="password" minlength="12" maxlength="72" required>'
    . '<button type="submit">Create account</button></form><p><a href="/login.php">Already have an account? Log in</a></p></section>';

render_page('Create account', $content);
