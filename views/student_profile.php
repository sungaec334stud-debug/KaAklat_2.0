<?php

declare(strict_types=1);

require_once __DIR__ . '/portal_ui.php';

function student_profile_html(array $profile): string
{
    $pending = $profile['account_status'] === 'pending';
    $label = $pending ? 'Pending activation' : 'Active';
    $explanation = $pending
        ? 'Waiting for librarian activation. You can browse books and view your existing requests. New requests become available after activation.'
        : 'Your account is active. New requests remain subject to book availability and borrowing eligibility.';
    $registered = (new DateTimeImmutable($profile['created_at'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Manila'));
    $initial = mb_substr($profile['display_name'], 0, 1, 'UTF-8');
    $html = '<section class="panel"><h1>My Profile</h1><p>Your library account at a glance.</p>'
        . '<div class="profile-layout"><aside class="profile-card" aria-label="Student library account">'
        . '<span class="profile-monogram" aria-hidden="true">' . h($initial) . '</span>'
        . '<p>Student · Universidad de Dagupan</p><h2>' . h($profile['display_name']) . '</h2>'
        . '<p>@' . h($profile['username']) . '</p></aside><div class="profile-account">'
        . '<h2>Account details</h2><dl class="profile-details">'
        . '<div><dt>Display name</dt><dd>' . h($profile['display_name']) . '</dd></div>'
        . '<div><dt>Username</dt><dd>' . h($profile['username']) . '</dd></div>'
        . '<div><dt>School student ID</dt><dd>' . h($profile['school_student_id'] ?? 'No school ID on file') . '</dd></div>'
        . '<div><dt>Account status</dt><dd><span class="status-badge">' . h($label) . '</span><p>' . h($explanation) . '</p></dd></div>'
        . '<div><dt>Registered (Manila time)</dt><dd><time datetime="' . h($registered->format(DateTimeInterface::ATOM)) . '">'
        . h($registered->format('j F Y, g:i A')) . '</time></dd></div></dl>'
        . '<p>These details are view-only. Contact your librarian if something needs correcting.</p></div></div></section>';
    $counts = $profile['counts'];
    $html .= portal_summary_html(student_summary_items($counts), 'Your borrowing at a glance', 'Only your records');
    $html .= student_overdue_notice_html($counts['overdue']);
    if ($counts['pending'] + $counts['awaiting'] + $counts['active'] === 0) {
        $html .= '<section class="empty-state"><h2>No current borrowing activity</h2><p>Explore the catalog to find your next book. Your past requests remain in your history.</p>'
            . '<a href="/catalog.php">Browse books</a> · <a href="/requests.php">View my history</a></section>';
    }
    return $html;
}
