<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/views/dashboard.php';
require_once dirname(__DIR__) . '/app/dashboard.php';
start_application_session();
$user = null;
if (isset($_SESSION['user_id'], $_SESSION['session_version'])) {
    $db = database_connection(application_config()['database']);
    $user = current_user($db);
}
if ($user !== null) {
    $student = $user['role'] === 'student';
    $librarian = $user['role'] === 'librarian';
    $content = '<div class="dashboard">' . dashboard_welcome($user);

    if ($student && $user['account_status'] === 'pending') {
        $content .= '<p class="flash flash-info" role="status">Your account is waiting for librarian activation. You can browse the catalog while you wait.</p>';
    }
    if ($student || $librarian) {
        try {
            $counts = dashboard_counts($db);
            $summaries = $student
                ? student_summary_items($counts, 'Ready to collect')
                : [['Requests to review', $counts['pending'], '/review_requests.php'],
                   ['Awaiting collection', $counts['awaiting'], '/pickups.php']];
            if ($student) { $content .= student_overdue_notice_html($counts['overdue']); }
            $content .= portal_summary_html($summaries, $student ? 'Your borrowing at a glance' : 'Circulation at a glance',
                $student ? 'Only your records' : 'Library overview');
        } catch (Throwable) {
            $content .= '<p class="flash flash-info" role="status">The summary is temporarily unavailable. You can still use the main navigation.</p>';
        }
    }
    try {
        $content .= dashboard_preview_html($user['role'], dashboard_preview($db));
    } catch (Throwable) {
        $content .= '<p class="flash flash-info" role="status">The dashboard preview is temporarily unavailable.</p>';
    }
    $content .= '<div class="session-actions"><span>Signed in as ' . h(ucfirst($user['role'])) . ' · ' . h(ucfirst($user['account_status'])) . '</span></div></div>';
    render_page('Dashboard', $content);
    exit;
}
require_once dirname(__DIR__).'/views/landing.php';
render_page('Welcome', landing_html(), true);
