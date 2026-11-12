<?php

declare(strict_types=1);
require_once __DIR__ . '/time.php';
require_once __DIR__ . '/circulation_ui.php';
require_once __DIR__ . '/audit_ui.php';

function dashboard_welcome(array $user): string
{
    [$message, $url, $action] = match ($user['role']) {
        'librarian' => ['A clear view of the circulation desk.', '/review_requests.php', 'Review requests'],
        'administrator' => ['Your library’s access and account activity, at a glance.', '/students.php', 'Review students'],
        default => ['Keep track of your requests, pickups, and returns.', '/catalog.php', 'Find your next book'],
    };
    $name = $user['display_name'];
    $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_substr($part, 0, 1);
    }
    return '<header class="dashboard-hero"><span class="dashboard-initials" aria-hidden="true">'
        . h(mb_strtoupper($initials)) . '</span><div class="dashboard-welcome"><h1>Welcome back, '
        . h($name) . '.</h1><p>' . h($message) . '</p></div><a class="button" href="' . h($url) . '">'
        . h($action) . '</a></header>';

}

function dashboard_manila_time(string $utc): string
{
    return display_manila_time($utc, 'M j, g:i A');
}

function dashboard_preview_html(string $role, array $preview): string
{
    $rows = [];
    if ($role === 'student') {
        $heading = 'Next up';
        $description = 'Your nearest borrowing deadline';
        $empty = 'You have no pickup or return deadlines right now.';
        $links = [['/catalog.php', 'Browse books']];
        if ($preview !== []) {
            $loan = $preview[0];
            $pickup = $loan['due_date'] === null;
            $deadline = $pickup ? dashboard_manila_time($loan['deadline_utc'])
                : (new DateTimeImmutable($loan['due_date'], new DateTimeZone('Asia/Manila')))->format('M j, Y');
            $rows[] = [$loan['title'], $deadline . ' (Manila)', $pickup ? 'Pick up by' : 'Return by'];
            $overdue = !$pickup && $loan['due_date'] < (new DateTimeImmutable('today', new DateTimeZone('Asia/Manila')))->format('Y-m-d');
            $filter = $pickup ? 'awaiting_pickup' : ($overdue ? 'overdue' : 'active');
            $links = [['/requests.php?status=' . $filter, 'View my requests']];
        }
    } elseif ($role === 'librarian') {
        $heading = 'At the circulation desk';
        $description = 'Oldest requests and pickups closest to expiring';
        $empty = 'No requests to review or pickups awaiting collection.';
        $links = [['/review_requests.php', 'Review requests'], ['/pickups.php', 'Open pickups']];
        foreach ($preview['pending'] as $request) {
            $rows[] = [$request['title'], $request['student_name'] . ' · ' . dashboard_manila_time($request['event_time']) . ' (Manila)', 'Pending review', circulation_cover_html($request)];
        }
        foreach ($preview['pickups'] as $pickup) {
            $rows[] = [$pickup['title'], $pickup['student_name'] . ' · ' . dashboard_manila_time($pickup['event_time']) . ' (Manila)', 'Pickup deadline', circulation_cover_html($pickup)];
        }
    } else {
        $heading = 'Recent activity';
        $description = 'Latest staff and student account changes';
        $empty = 'There are no recent account changes to show.';
        $links = [['/audit.php', 'View audit records']];
        $labels = [
            'staff.created' => 'Staff account created', 'staff.updated' => 'Staff account updated',
            'student.activate' => 'Student account activated', 'student.suspend' => 'Student account suspended',
            'student.reactivate' => 'Student account reactivated',
        ];
        foreach ($preview as $event) {
            $rows[] = [$labels[$event['event_code']], 'By ' . ($event['actor_username'] ?? 'system / local operator'),
                dashboard_manila_time($event['occurred_at']) . ' (Manila)'];
        }
    }

    $html = '<section class="dashboard-preview panel dashboard-preview-' . h($role) . '"><header><h2>' . h($heading) . '</h2><p>' . h($description) . '</p></header>';
    if ($rows === []) {
        $html .= '<p class="dashboard-preview-empty">' . h($empty) . '</p>';
    } else {
        if ($role === 'student') {
            $date = new DateTimeImmutable($preview[0]['deadline_utc'], new DateTimeZone('UTC'));
            if ($preview[0]['due_date'] !== null) {
                $date = new DateTimeImmutable($preview[0]['due_date'], new DateTimeZone('Asia/Manila'));
            } else { $date = $date->setTimezone(new DateTimeZone('Asia/Manila')); }
            $html .= '<div class="dashboard-calendar" aria-hidden="true"><span>' . h($date->format('M'))
                . '</span><strong>' . h($date->format('j')) . '</strong><small>' . h($date->format('Y')) . '</small></div>';
        }
        $html .= '<ul>';
        foreach ($rows as $row) {
            [$title, $detail, $tag] = $row;
            if ($role === 'administrator') {
                $html .= '<li><span class="desk-activity-mark" aria-hidden="true">✓</span><div><h3>'
                    . h($title) . '</h3><p>' . h($detail) . ' · ' . h($tag) . '</p></div></li>';
                continue;
            }
            $html .= '<li>' . ($row[3] ?? '') . '<div><h3>' . h($title) . '</h3><p>' . h($detail)
                . '</p></div><span class="status-badge">' . h($tag) . '</span></li>';
        }
        $html .= '</ul>';
    }
    $html .= '<footer>';
    foreach ($links as [$url, $label]) {
        $html .= '<a href="' . h($url) . '">' . h($label) . ' <span aria-hidden="true">→</span></a>';
    }
    return $html . '</footer></section>';
}

/** Whole-card native links match the accepted mockup; counts remain server-derived. */
function dashboard_staff_summary_html(array $items, string $heading): string
{
    $html = '<section class="staff-summary" aria-label="' . h($heading) . '">';
    foreach ($items as [$label, $count, $detail, $url]) {
        $html .= '<a class="staff-stat" href="' . h($url) . '"><span>' . h($label)
            . '</span><strong>' . h((string)$count) . '</strong><small>' . h($detail)
            . '</small><span class="staff-stat-arrow" aria-hidden="true">↗</span></a>';
    }
    return $html . '</section>';
}

function dashboard_avatar_html(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) { $initials .= mb_substr($part, 0, 1); }
    return '<span class="staff-avatar" aria-hidden="true">' . h(mb_strtoupper($initials)) . '</span>';
}

function dashboard_account_panel_html(string $role, array $panels): string
{
    if ($role === 'librarian') {
        $html = '<section class="panel dashboard-account-panel"><header><h2>Student verification</h2><span class="status-badge status-pending">'
            . h((string)$panels['pending_count']) . ' pending</span></header><div class="staff-panel-body">';
        if ($panels['students'] === []) {
            $html .= '<p>No students are waiting for verification.</p>';
        } else {
            $student = $panels['students'][0];
            $html .= '<div class="staff-person">' . dashboard_avatar_html($student['display_name'])
                . '<div><h3>' . h($student['display_name']) . '</h3><p>' . h($student['username'])
                . '</p><small>Registered ' . h(dashboard_manila_time($student['created_at'])) . ' (Manila)</small></div></div>'
                . '<p>Check the physical school ID in person before activating this account.</p>';
        }
        return $html . '<a class="button button-secondary" href="/students.php?status=pending">Review students →</a></div></section>';
    }
    $html = '<section class="panel dashboard-account-panel"><header><h2>Account access</h2><a href="/staff.php">View all →</a></header>';
    if ($panels['staff'] === []) { $html .= '<p class="staff-panel-body">No active staff accounts to display.</p>'; }
    foreach ($panels['staff'] as $staff) {
        $html .= '<div class="staff-person staff-access-row">' . dashboard_avatar_html($staff['display_name'])
            . '<div><h3>' . h($staff['display_name']) . '</h3><p>' . h(ucfirst($staff['role'])) . ' · '
            . h(ucfirst($staff['account_status'])) . '</p></div><a href="/staff.php?q=' . rawurlencode($staff['username'])
            . '" aria-label="' . h('View staff account ' . $staff['display_name']) . '">View →</a></div>';
    }
    return '<div class="staff-dashboard-side">' . $html . '</section><p class="staff-policy-note"><strong>Keep access separate.</strong> Administrators manage accounts and audit records. Circulation actions remain with librarians.</p></div>';
}

function dashboard_desk_activity_html(array $events): string
{
    $html = '<section class="panel dashboard-desk-activity"><header><h2>Recent desk activity</h2><p>Your latest circulation actions · Manila time</p></header>';
    if ($events === []) { return $html . '<p class="staff-panel-body">No recent circulation actions recorded for your account.</p></section>'; }
    $html .= '<ol>';
    foreach ($events as $event) {
        $html .= '<li><span class="desk-activity-mark" aria-hidden="true">✓</span><div><strong>'
            . h(audit_event_label($event['event_code'])) . '</strong><p>'
            . h(dashboard_manila_time($event['occurred_at'])) . ' (Manila)</p></div></li>';
    }
    return $html . '</ol></section>';
}

/** A bounded, read-only catalog preview using the existing authenticated cover renderer. */
function dashboard_catalog_html(array $books): string
{
    $html = '<section class="dashboard-discovery panel"><header><h2>Explore the collection</h2><a href="/catalog.php">Browse catalog →</a></header><div class="dashboard-books">';
    foreach (array_slice($books, 0, 4) as $book) {
        $html .= '<article><a href="/catalog.php?id=' . h($book['book_id']) . '">' . catalog_cover_html($book)
            . '<h3>' . h($book['title']) . '</h3></a><p>' . h($book['authors'] ?? '') . '</p>'
            . catalog_metadata_html($book) . '<p class="availability">' . h((string)$book['available_copies'])
            . ' copies available</p></article>';
    }
    if ($books === []) { $html .= '<p>No catalog books to display yet. Check the catalog again later.</p>'; }
    return $html . '</div></section>';
}
