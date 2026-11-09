<?php

declare(strict_types=1);

function catalog_pagination_html(array $result, string $query, array $filters): string
{
    $page = $result['page'];
    $pages = $result['total_pages'];
    $url = static fn (int $target): string => '?' . h(http_build_query($filters + ['q'=>$query, 'page'=>$target]));
    $html = '<nav class="catalog-pagination" aria-label="Catalog pages"><p class="pagination-summary">'
        . h((string) $result['total']) . ' books · Page ' . h((string) $page) . ' of ' . h((string) $pages) . '</p>';
    if ($page > 1) {
        $html .= '<a rel="prev" aria-label="Previous page" href="' . $url($page - 1) . '">Previous</a>';
    }
    $numbers = $pages <= 7 ? range(1, $pages) : array_unique(array_merge([1], range(max(1, $page - 2), min($pages, $page + 2)), [$pages]));
    $previous = 0;
    foreach ($numbers as $number) {
        if ($previous > 0 && $number > $previous + 1) {
            $html .= '<span class="pagination-gap" aria-hidden="true">…</span>';
        }
        $html .= $number === $page
            ? '<span class="pagination-current" aria-current="page" aria-label="Page ' . h((string) $number) . ', current page">' . h((string) $number) . '</span>'
            : '<a aria-label="Go to page ' . h((string) $number) . '" href="' . $url($number) . '">' . h((string) $number) . '</a>';
        $previous = $number;
    }
    if ($page < $pages) {
        $html .= '<a rel="next" aria-label="Next page" href="' . $url($page + 1) . '">Next</a>';
    }
    return $html . '</nav>';
}
