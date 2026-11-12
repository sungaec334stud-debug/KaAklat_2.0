<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/app/catalog.php';
require_once __DIR__ . '/pagination.php';

function catalog_pagination_html(array $result, string $query, array $filters): string
{
    return pagination_html($result, static fn(int $target): string => catalog_url($query, $target, $filters), 'Catalog pages', 'books');
}
