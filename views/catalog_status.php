<?php

declare(strict_types=1);

function catalog_request_status_html(?string $state): string
{
    $labels = [
        'pending' => ['pending', 'Your request: Pending'],
        'awaiting pickup' => ['awaiting-pickup', 'Your book: Awaiting pickup'],
        'active' => ['active', 'Your loan: Active'],
        'overdue' => ['overdue', 'Your loan: Overdue'],
    ];
    if (!isset($labels[$state ?? ''])) { return ''; }
    [$class, $label] = $labels[$state];
    return '<p class="catalog-request-status"><span class="status-badge status-' . h($class) . '">'
        . h($label) . '</span></p>';
}
