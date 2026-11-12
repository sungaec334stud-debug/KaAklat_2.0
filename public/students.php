<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/student_accounts.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/app/pagination.php';

start_application_session();
header('Cache-Control: no-store');
try {
    $connection = database_connection(application_config()['database']);
    $user = current_user($connection);
    if ($user === null) {
        redirect('/login.php');
    }
    require_student_manager($user);
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
            if (array_diff(array_keys($_POST), ['student_id','action','csrf_token','identity_confirmation','review_version','replacement_id'])) { throw new InvalidArgumentException('Unexpected form fields. Refresh the page.'); }
            $id = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $reviewVersion = filter_var($_POST['review_version'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            change_student_status($connection, $id === false ? 0 : $id, $_POST['action'] ?? null,
                $_POST['csrf_token'] ?? null, $_POST['identity_confirmation'] ?? null,
                $reviewVersion === false ? null : $reviewVersion, $_POST['replacement_id'] ?? null);
            flash_set('success', 'Student account updated. The student must sign in again.');
            redirect($sectionUrl);
        } catch (InvalidArgumentException $exception) {
            flash_set('error', $exception->getMessage());
            redirect($sectionUrl);
        }
    }
    $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]) ?: 1;
    $canReview = $user['role'] === 'librarian' && $status === 'pending';
    $reviewColumns = $canReview ? ', school_student_id, session_version' : '';
    $students = array_slice(pagination_rows($connection, "SELECT user_id, username, display_name, account_status{$reviewColumns} FROM users WHERE role = 'student' AND account_status = :status", ['status'=>$status], 'user_id', $page, 25, $pagination, 100000), 0, 25);
    $page = $pagination['page'];
    $content = '<section class="panel"><h1>Student accounts</h1><p>Activate students after checking their school identity. Suspended students cannot sign in. Account changes require the student to sign in again.</p><p><a href="/">Dashboard</a></p>';
    $content .= '<nav class="account-tabs" aria-label="Student account actions"><ul>';
    foreach ($sections as $key => $label) {
        $content .= '<li><a href="?status=' . h($key) . '"' . ($status === $key ? ' aria-current="page"' : '') . '>' . h($label) . '</a></li>';
    }
    $content .= '</ul></nav><h2>' . h($sections[$status]) . '</h2>';
    foreach ($students as $student) {
        $action = match ($student['account_status']) { 'pending' => 'activate', 'active' => 'suspend', 'suspended' => 'reactivate' };
        $content .= '<article class="student-card"><div class="student-info"><h3>' . h($student['display_name']) . '</h3><p>' . h($student['username']) . ' <span class="badge badge-' . h($student['account_status']) . '">' . h(ucfirst($student['account_status'])) . '</span></p></div>';
        $form = '<form method="post" action="' . h($sectionUrl) . '"><input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'
            . '<input type="hidden" name="student_id" value="' . h($student['user_id']) . '">';
        if ($canReview) {
            $hasClaim = $student['school_student_id'] !== null;
            $reviewForm = $form . '<input type="hidden" name="review_version" value="' . h($student['session_version']) . '">'
                . '<label><input type="checkbox" name="identity_confirmation" required> I checked the physical school ID in person.</label>';
            $content .= '<div><p>School student ID: ' . h($student['school_student_id'] ?? 'No ID assigned') . '</p>';
            if ($hasClaim) {
                $content .= $reviewForm . '<button name="action" value="activate">Activate account</button></form>';
            }
            $content .= '<details><summary>Resolve a student ID claim</summary>'
                . '<p>Correct the ID after verifying the account holder, or release a mistaken claim after verifying the rightful student. A released account stays pending.</p>'
                . $reviewForm . '<label>Correct school student ID (YY-####-###)<input name="replacement_id" maxlength="11" autocomplete="off" pattern="[0-9]{2}-[0-9]{4}-[0-9]{3}"></label>'
                . '<button name="action" value="correct_id">Save corrected ID</button></form>';
            if ($hasClaim) {
                $content .= $reviewForm . '<button class="button-caution" name="action" value="release_id">Release mistaken claim</button></form>';
            }
            $content .= '</details></div>';
        } elseif ($status !== 'pending') {
            $content .= $form . '<button name="action" value="' . h($action) . '" aria-label="' . h(ucfirst($action) . ' ' . $student['display_name']) . '">' . h(ucfirst($action)) . '</button></form>';
        } else {
            $content .= '<p>A librarian must verify the ID and activate this account.</p>';
        }
        $content .= '</article>';
    }
    if ($students === []) { $content .= '<p>No student accounts on this page.</p>'; }
    $content .= pagination_html($pagination, static fn(int $target): string => '?'.http_build_query(['status'=>$status,'page'=>$target]), 'Student account pages', 'students');
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
