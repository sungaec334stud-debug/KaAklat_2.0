<?php

declare(strict_types=1);
require_once __DIR__ . '/time.php';

function dashboard_welcome(array $user): string
{
    [$label, $message, $art] = match ($user['role']) {
        'librarian' => ['Librarian workspace', 'See which requests and pickups need your attention.',
            '<rect x="38" y="27" width="88" height="86" rx="7"/><path d="M63 27v-9h38v9M56 57l6 6 11-13m11 9h25M56 83l6 6 11-13m11 9h25"/>'],
        'administrator' => ['Administrator workspace', 'Manage staff access and catch up on account activity.',
            '<path d="M82 19l48 17v30c0 25-20 42-48 52-28-10-48-27-48-52V36l48-17zM62 68l14 14 27-30"/>'],
        default => ['Student library', 'Keep track of your requests, pickups, and returns.',
            '<path d="M82 38c-24-13-48-12-65-7v66c23-5 43-2 65 10 22-12 42-15 65-10V31c-17-5-41-6-65 7v69M32 51l32 8m-32 9 32 8m36-17 32-8m-32 25 32-8"/>'],
    };
    $name = $user['display_name'];
    $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_substr($part, 0, 1);
    }
    return '<header class="dashboard-hero"><div class="dashboard-welcome"><p class="dashboard-role">'
        . '<span class="dashboard-initials" aria-hidden="true">' . h(mb_strtoupper($initials)) . '</span>' . h($label)
        . '</p><h1>Welcome back, ' . h($name) . '.</h1><p>' . h($message) . '</p></div>'
        . '<svg class="dashboard-art" viewBox="0 0 164 132" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">'
        . $art . '</svg>' . ($user['role'] === 'administrator'
            ? '<a class="button" href="/staff.php">Manage staff accounts</a>' : '') . '</header>';
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
        $heading = 'Today at the desk';
        $description = 'Oldest requests and pickups closest to expiring';
        $empty = 'No requests to review or pickups awaiting collection.';
        $links = [['/review_requests.php', 'Review requests'], ['/pickups.php', 'Open pickups']];
        foreach ($preview['pending'] as $request) {
            $rows[] = [$request['title'], $request['student_name'] . ' · ' . dashboard_manila_time($request['event_time']) . ' (Manila)', 'Pending review'];
        }
        foreach ($preview['pickups'] as $pickup) {
            $rows[] = [$pickup['title'], $pickup['student_name'] . ' · ' . dashboard_manila_time($pickup['event_time']) . ' (Manila)', 'Pickup deadline'];
        }
    } else {
        $heading = 'Recent administrative activity';
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

    $html = '<section class="dashboard-preview panel"><header><h2>' . h($heading) . '</h2><p>' . h($description) . '</p></header>';
    if ($rows === []) {
        $html .= '<p class="dashboard-preview-empty">' . h($empty) . '</p>';
    } else {
        $html .= '<ul>';
        foreach ($rows as [$title, $detail, $tag]) {
            $html .= '<li><div><h3>' . h($title) . '</h3><p>' . h($detail)
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
