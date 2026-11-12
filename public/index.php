<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/views/dashboard.php';
require_once dirname(__DIR__) . '/app/dashboard.php';
require_once dirname(__DIR__) . '/app/catalog.php';
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
    if ($student) {
        try {
            $counts = dashboard_counts($db);
            $content .= student_overdue_notice_html($counts['overdue']);
            $content .= portal_summary_html(student_summary_items($counts, 'Ready to collect'),
                'Your borrowing at a glance', 'Only your records');
        } catch (Throwable) {
            $content .= '<p class="flash flash-info" role="status">The summary is temporarily unavailable. You can still use the main navigation.</p>';
        }
        try { $content .= dashboard_preview_html('student', dashboard_preview($db)); }
        catch (Throwable) {
            $content .= '<p class="flash flash-info" role="status">The dashboard preview is temporarily unavailable.</p>';
        }
    } else {
        $preview = null;
        $panels = null;
        try { $preview = dashboard_preview($db); } catch (Throwable) { /* Independent display fallback below. */ }
        try { $panels = dashboard_staff_panels($db); } catch (Throwable) { /* Never invent a zero count on failure. */ }
        try {
            if ($librarian) {
                $counts = dashboard_counts($db);
                $summaries = [
                    ['Pending reviews', $counts['pending'], 'Requests awaiting a decision', '/review_requests.php'],
                    ['Ready to collect', $counts['awaiting'], 'Allocated copies', '/pickups.php'],
                    ['Outstanding loans', $counts['active'], 'Receive returned books', '/returns.php'],
                ];
            } else {
                $counts = dashboard_administrator_counts($db);
                $summaries = [
                    ['Pending students', $counts['pending'], 'Librarian ID check required', '/students.php?status=pending'],
                    ['Active staff', $counts['staff'], 'Librarians and administrators', '/staff.php'],
                    ['Recent account events', $preview === null ? '—' : count($preview),
                        $preview === null ? 'Temporarily unavailable' : 'Latest up to three account events', '/audit.php'],
                ];
            }
            $content .= dashboard_staff_summary_html($summaries, $librarian ? 'Circulation at a glance' : 'Accounts at a glance');
        } catch (Throwable) {
            $content .= '<p class="flash flash-info" role="status">The summary is temporarily unavailable. You can still use the main navigation.</p>';
        }
        $content .= '<div class="staff-dashboard-grid">';
        $content .= $preview === null
            ? '<section class="panel staff-panel-body"><h2>Recent activity</h2><p>The dashboard preview is temporarily unavailable.</p></section>'
            : dashboard_preview_html($user['role'], $preview);
        $content .= $panels === null
            ? '<section class="panel staff-panel-body"><h2>Account overview</h2><p>The account overview is temporarily unavailable.</p></section>'
            : dashboard_account_panel_html($user['role'], $panels);
        $content .= '</div>';
        if ($librarian && $panels !== null) { $content .= dashboard_desk_activity_html($panels['activity']); }
    }
    if ($student) {
        try { $content .= dashboard_catalog_html(catalog_books($db, '', 1)['books']); }
        catch (Throwable) {
            $content .= '<p class="flash flash-info" role="status">The catalog preview is temporarily unavailable. <a href="/catalog.php">Open the catalog</a>.</p>';
        }
    }
    $content .= '<div class="session-actions"><span>Signed in as ' . h(ucfirst($user['role'])) . ' · ' . h(ucfirst($user['account_status'])) . '</span></div></div>';
    render_page('Dashboard', $content);
    exit;
}
require_once dirname(__DIR__).'/views/landing.php';
render_page('Welcome', landing_html(), true);
