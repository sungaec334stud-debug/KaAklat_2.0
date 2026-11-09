<?php

declare(strict_types=1);

// Static decorative artwork. Route names come from existing navigation, never SVG input.
function portal_icon(string $route): string
{
    $paths = [
        '/logout.php' => '<path d="M9 4H4v16h5m5-12 4 4-4 4m-6-4h12"/>',
        '/about.php' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v1"/>',
        '/' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        '/catalog.php' => '<path d="M12 5C8 2 3 3 3 3v16s5-1 9 2c4-3 9-2 9-2V3s-5-1-9 2v16M6 7l3 1m-3 3 3 1m6-4 3-1m-3 5 3-1"/>',
        '/requests.php' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2M9 10h6M9 14h6M9 18h3"/>',
        '/profile.php' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        '/review_requests.php' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="m8 9 2 2 5-5M8 15h8M8 18h5"/>',
        '/pickups.php' => '<path d="M4 7h16v13H4zM8 7V4h8v3m-8 6 3 3 5-6"/>',
        '/returns.php' => '<path d="M5 3h14v18H5zM9 3v8l3-2 3 2V3"/>',
        '/manage_books.php' => '<path d="M4 4h5v17H4zm6 0h5v17h-5zm6 1 4-1 3 16-4 1zM5 8h3m3 1h3"/>',
        '/students.php' => '<circle cx="9" cy="7" r="3"/><path d="M3 21v-4a6 6 0 0 1 12 0v4m1-17a3 3 0 0 1 0 6m2 4a6 6 0 0 1 3 5v2"/>',
        '/staff.php' => '<circle cx="9" cy="7" r="3"/><path d="M3 21v-4a6 6 0 0 1 9-5m5 0 4 2v4l-4 3-4-3v-4z"/>',
        '/audit.php' => '<path d="M5 3h14v18H5zM8 7h8M8 11h8M8 15h5M8 18h8"/>',
        '/recover_account.php' => '<circle cx="8" cy="9" r="5"/><path d="m12 13 8 8m-4-4 3-3m-6 0 3-3M6 9h.1"/>',
    ];
    if (!isset($paths[$route])) { return ''; }
    return '<svg xmlns="http://www.w3.org/2000/svg" class="portal-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $paths[$route] . '</svg>';
}

function portal_summary_html(array $summaries, string $heading, string $scope): string
{
    $html = '<section class="summary-panel" aria-label="' . h($heading) . '"><div class="summary-heading"><span>' . h($heading)
        . '</span><span>' . h($scope) . '</span></div><div class="summary-grid" role="group" aria-label="Borrowing summary">';
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
