<?php

declare(strict_types=1);

function student_summary_items(array $counts, string $pickupLabel = 'Ready for pickup'): array
{
    return [
        ['Pending requests', $counts['pending'], '/requests.php?status=pending'],
        [$pickupLabel, $counts['awaiting'], '/requests.php?status=awaiting_pickup'],
        ['Active loans', $counts['active'] - $counts['overdue'], '/requests.php?status=active'],
    ];
}

function student_overdue_notice_html(int $count): string
{
    if ($count <= 0) { return ''; }
    return '<p class="flash flash-error" role="status">You have ' . h((string) $count)
        . ' <a href="/requests.php?status=overdue">' . ($count === 1 ? 'overdue loan' : 'overdue loans')
        . '</a>. Please speak with your librarian.</p>';
}

// Static decorative artwork. Route names come from existing navigation, never SVG input.
function portal_icon(string $route): string
{
    $paths = [
        '/' => '<path d="M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z"/>',
        '/catalog.php' => '<path d="M12 5C8 2 4 3 2 4v16c3-2 7-1 10 1 3-2 7-3 10-1V4c-3-2-7-1-10 1zm0 0v16"/>',
        '/requests.php' => '<path d="M7 4H4v18h16V4h-3 M8 2h8v5H8z M8 12h8 M8 17h6"/>',
        '/review_requests.php' => '<path d="M7 4H4v18h16V4h-3 M8 2h8v5H8z M8 12h8 M8 17h6"/>',
        '/pickups.php' => '<path d="M4 8h16v13H4z M8 3h8v5 M8 14l3 3 5-6"/>',
        '/returns.php' => '<path d="M8 7H3v-5 M3 7a9 9 0 1 1-1 9"/>',
        '/manage_books.php' => '<path d="M3 3h4v18H3z M10 3h4v18h-4z M17 3l4-1 4 18-4 1z"/>',
        '/students.php' => '<path d="M8 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M1 22v-4c0-5 14-5 14 0v4 M17 4a4 4 0 0 1 0 8 M19 15c3 0 4 2 4 5"/>',
        '/staff.php' => '<path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10z M3 23v-4c0-7 18-7 18 0v4"/>',
        '/profile.php' => '<path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10z M3 23v-4c0-7 18-7 18 0v4"/>',
        '/audit.php' => '<path d="M5 2h14v20H5z M8 7h8 M8 12h8 M8 17h5"/>',
        '/recover_account.php' => '<path d="M8 14a6 6 0 1 0 0-12 6 6 0 0 0 0 12z M13 12l9 9 M17 16l3-3"/>',
        '/settings.php' => '<path d="M3 6h18 M3 12h18 M3 18h18 M7 3v6 M17 9v6 M10 15v6"/>',
        '/about.php' => '<path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z M12 11v7 M12 6v2"/>',
        '/logout.php' => '<path d="M9 3H3v18h6 M7 12h15 M17 7l5 5-5 5"/>',
    ];
    if (!isset($paths[$route])) { return ''; }
    return '<svg xmlns="http://www.w3.org/2000/svg" class="portal-icon" viewBox="0 0 26 26" aria-hidden="true" focusable="false">' . $paths[$route] . '</svg>';
}

function portal_summary_html(array $summaries, string $heading, string $scope): string
{
    $html = '<section class="summary-panel" aria-label="' . h($heading) . '"><div class="summary-heading"><span>' . h($heading)
        . '</span><span>' . h($scope) . '</span></div><div class="summary-grid" role="group" aria-label="' . h($heading) . '">';
    foreach ($summaries as [$label,$count,$url]) {
        $path = parse_url($url, PHP_URL_PATH);
        if (str_contains($url, 'status=awaiting_pickup')) { $path = '/pickups.php'; }
        if (str_contains($url, 'status=active')) { $path = '/returns.php'; }
        $html .= '<section class="panel action-card count-card"><h2>' . portal_icon((string)$path) . '<span>' . h($label)
            . '</span></h2><p><strong>' . h((string)$count) . '</strong></p><a href="' . h($url) . '">View '
            . h(strtolower($label)) . ' <span aria-hidden="true">→</span></a></section>';
    }
    return $html . '</div></section>';
}
