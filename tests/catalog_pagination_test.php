<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
require_once dirname(__DIR__) . '/app/helpers.php';
require_once dirname(__DIR__) . '/views/catalog_pagination.php';

function pagination_check(bool $ok, string $label): void
{
    if (!$ok) { throw new RuntimeException($label); }
}

foreach ([[1,1],[1,3],[2,3],[3,3],[500,10000],[10000,10000]] as [$page,$pages]) {
    $html = catalog_pagination_html(['page'=>$page,'total_pages'=>$pages,'total'=>$pages*12], '<tag>&', ['title'=>'A&B','available'=>'1','year'=>2001,'category'=>2,'letter'=>'A','author'=>"O'Brien"]);
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    pagination_check($xpath->query('//*[@aria-current="page"]')->length === 1, 'One current page');
    $links = $xpath->query('//a');
    pagination_check($links->length <= 11, 'Long catalogs have bounded navigation');
    foreach ($links as $link) {
        parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
        pagination_check((int)$query['page'] >= 1 && (int)$query['page'] <= $pages, 'Only valid page destinations');
        pagination_check($query['q']==='<tag>&' && $query['title']==='A&B' && $query['author']==="O'Brien"
            && $query['available']==='1' && $query['year']==='2001' && $query['category']==='2' && $query['letter']==='A', 'Every filter and search retained');
        pagination_check($link->getAttribute('aria-label') !== '', 'Links have meaningful names');
    }
    pagination_check(!str_contains($html, '<tag>'), 'Search cannot inject markup');
    pagination_check(($xpath->query('//a[@rel="prev"]')->length === 1) === ($page > 1), 'Previous boundary');
    pagination_check(($xpath->query('//a[@rel="next"]')->length === 1) === ($page < $pages), 'Next boundary');
}
echo "PASS: Catalog pagination boundaries, bounded links, filter retention and accessible current state.\n";
