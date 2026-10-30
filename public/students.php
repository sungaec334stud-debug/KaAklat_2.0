<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/student_accounts.php';
require_once dirname(__DIR__) . '/views/layout.php';

start_application_session();
try {
    $connection = database_connection(application_config()['database']);
    $user = current_user($connection);
    if ($user === null) {
        redirect('/login.php');
    }
    require_active_librarian($user);
    $sections = ['pending' => 'Activate pending students', 'active' => 'Suspend active students', 'suspended' => 'Reactivate suspended students'];
    $statusInput = $_GET['status'] ?? 'pending';
    $status = is_string($statusInput) && isset($sections[$statusInput]) ? $statusInput : 'pending';
    $sectionUrl = '/students.php?status=' . $status;
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
        http_response_code(405);
        header('Allow: GET, POST');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $id = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            change_student_status($connection, $id === false ? 0 : $id, $_POST['action'] ?? null, $_POST['csrf_token'] ?? null);
            flash_set('success', 'Student account updated. The student must sign in again.');
            redirect($sectionUrl);
        } catch (InvalidArgumentException $exception) {
            flash_set('error', $exception->getMessage());
            redirect($sectionUrl);
        }
    }
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]) ?: 1;
    $statement = $connection->prepare("SELECT user_id, username, display_name, account_status FROM users WHERE role = 'student' AND account_status = :status ORDER BY user_id LIMIT 25 OFFSET " . (($page - 1) * 25));
    $statement->execute(['status' => $status]);
    $students = $statement->fetchAll();
    $content = '<section class="panel"><h1>Student accounts</h1><p>Activate students after checking their school identity. Suspended students cannot sign in. Account changes require the student to sign in again.</p><p><a href="/">Dashboard</a></p>';
    $content .= '<nav class="account-tabs" aria-label="Student account actions"><ul>';
    foreach ($sections as $key => $label) {
        $content .= '<li><a href="?status=' . h($key) . '"' . ($status === $key ? ' aria-current="page"' : '') . '>' . h($label) . '</a></li>';
    }
    $content .= '</ul></nav><h2>' . h($sections[$status]) . '</h2>';
    foreach ($students as $student) {
        $action = match ($student['account_status']) { 'pending' => 'activate', 'active' => 'suspend', 'suspended' => 'reactivate' };
        $content .= '<article class="student-card"><div class="student-info"><h3>' . h($student['display_name']) . '</h3><p>' . h($student['username']) . ' <span class="badge badge-' . h($student['account_status']) . '">' . h(ucfirst($student['account_status'])) . '</span></p></div>'
            . '<form method="post" action="' . h($sectionUrl) . '"><input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'
            . '<input type="hidden" name="student_id" value="' . h($student['user_id']) . '"><button name="action" value="' . h($action) . '" aria-label="' . h(ucfirst($action) . ' ' . $student['display_name']) . '">' . h(ucfirst($action)) . '</button></form></article>';
    }
    if ($students === []) { $content .= '<p>No student accounts on this page.</p>'; }
    if ($page > 1) { $content .= '<a href="' . h($sectionUrl . '&page=' . ($page - 1)) . '">Previous page</a> '; }
    if (count($students) === 25) { $content .= '<a href="' . h($sectionUrl . '&page=' . ($page + 1)) . '">Next page</a>'; }
    render_page('Student accounts', $content . '</section>');
} catch (DomainException $exception) {
    http_response_code(403);
    render_page('Access denied', '<p role="alert">' . h($exception->getMessage()) . '</p>');
} catch (Throwable) {
    $reference = bin2hex(random_bytes(8));
    error_log('Student account page failed. Reference: ' . $reference);
    http_response_code(500);
    render_page('Unable to load student accounts', '<p role="alert">Please try again later. Reference: ' . h($reference) . '</p>');
}
