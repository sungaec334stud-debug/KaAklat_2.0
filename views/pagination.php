<?php
declare(strict_types=1);

function pagination_html(array $result, callable $url, string $label, string $items): string
{
    $page = $result['page'];
    $pages = $result['total_pages'];
    $html = '<nav class="catalog-pagination" aria-label="' . h($label) . '"><p class="pagination-summary">'
        . h($result['total']) . ' ' . h($items) . ' · Page ' . h($page) . ' of ' . h($pages) . '</p>';
    if ($page > 1) {
        $html .= '<a rel="prev" aria-label="Previous page" href="' . h($url($page - 1)) . '">Previous</a>';
    }
    $numbers = $pages <= 7 ? range(1, $pages) : array_unique(array_merge([1], range(max(1, $page - 2), min($pages, $page + 2)), [$pages]));
    $previous = 0;
    foreach ($numbers as $number) {
        if ($previous > 0 && $number > $previous + 1) {
            $html .= '<span class="pagination-gap" aria-hidden="true">…</span>';
        }
        $html .= $number === $page
            ? '<span class="pagination-current" aria-current="page" aria-label="Page ' . h($number) . ', current page">' . h($number) . '</span>'
            : '<a aria-label="Go to page ' . h($number) . '" href="' . h($url($number)) . '">' . h($number) . '</a>';
        $previous = $number;
    }
    if ($page < $pages) {
        $html .= '<a rel="next" aria-label="Next page" href="' . h($url($page + 1)) . '">Next</a>';
    }
    return $html . '</nav>';
}
