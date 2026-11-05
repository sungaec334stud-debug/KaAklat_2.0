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
    $html = '<section class="panel"><h1>My Profile</h1><p>Your library account at a glance.</p>'
        . '<h2>Account details</h2><dl class="profile-details">'
        . '<div><dt>Display name</dt><dd>' . h($profile['display_name']) . '</dd></div>'
        . '<div><dt>Username</dt><dd>' . h($profile['username']) . '</dd></div>'
        . '<div><dt>School student ID</dt><dd>' . h($profile['school_student_id'] ?? 'No school ID on file') . '</dd></div>'
        . '<div><dt>Account status</dt><dd><span class="status-badge">' . h($label) . '</span><p>' . h($explanation) . '</p></dd></div>'
        . '<div><dt>Registered (Manila time)</dt><dd><time datetime="' . h($registered->format(DateTimeInterface::ATOM)) . '">'
        . h($registered->format('j F Y, g:i A')) . '</time></dd></div></dl>'
        . '<p>These details are view-only. Contact your librarian if something needs correcting.</p></section>';
    $counts = $profile['counts'];
    $html .= portal_summary_html([
        ['Pending requests',$counts['pending'],'/requests.php?status=pending'],
        ['Ready for pickup',$counts['awaiting'],'/requests.php?status=awaiting_pickup'],
        ['Active loans',$counts['active'] - $counts['overdue'],'/requests.php?status=active'],
    ], 'Your borrowing at a glance', 'Only your records');
    if ($counts['overdue'] > 0) {
        $html .= '<p class="flash flash-error" role="status">You have ' . h((string)$counts['overdue'])
            . ' <a href="/requests.php?status=overdue">' . ($counts['overdue'] === 1 ? 'overdue loan' : 'overdue loans')
            . '</a>. Please speak with your librarian.</p>';
    }
    if ($counts['pending'] + $counts['awaiting'] + $counts['active'] === 0) {
        $html .= '<section class="empty-state"><h2>No current borrowing activity</h2><p>Explore the catalog to find your next book. Your past requests remain in your history.</p>'
            . '<a href="/catalog.php">Browse books</a> · <a href="/requests.php">View my history</a></section>';
    }
    return $html;
}
