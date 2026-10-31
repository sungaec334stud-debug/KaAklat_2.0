<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';

start_application_session();
$user = null;

if (isset($_SESSION['user_id'], $_SESSION['session_version'])) {
    $user = current_user(database_connection(application_config()['database']));
}

if ($user !== null) {
    $message = $user['account_status'] === 'pending'
        ? 'You can browse the catalog. A librarian must activate borrowing before you can request a book.'
        : 'Explore the catalog and check which books are available.';

    render_page(
        'Dashboard',
        '<section class="panel"><h1>Welcome, ' . h($user['display_name']) . '</h1><p>' . h($message) . '</p>'
        . '<p><a class="button" href="/catalog.php">Browse books</a></p>'
        . ($user['role'] === 'student' ? '<p><a href="/requests.php">My borrowing requests</a></p>' : '')
        . ($user['role'] === 'librarian' ? '<p><a href="/students.php">Manage student accounts</a> · <a href="/manage_books.php">Manage books</a> · <a href="/review_requests.php">Review borrowing requests</a> · <a href="/pickups.php">Record physical pickup</a></p>' : '')
        . '<form method="post" action="/logout.php"><input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '"><button type="submit">Log out</button></form></section>'
    );

    exit;
}

render_page(
    'Setup in progress',
    '<section class="panel"><h1>KaAklat</h1><p>Create a student account or sign in. A librarian activates borrowing access after registration.</p>'
    . '<p><a class="button" href="/register.php">Create student account</a> <a class="button button-secondary" href="/login.php">Log in</a></p></section>'
);
