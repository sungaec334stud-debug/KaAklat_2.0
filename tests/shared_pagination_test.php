<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
require_once dirname(__DIR__) . '/app/helpers.php';
require_once dirname(__DIR__) . '/app/pagination.php';
require_once dirname(__DIR__) . '/views/pagination.php';
function shared_page_check(bool $ok, string $message): void {
    if (!$ok) { throw new RuntimeException($message); }
}
foreach ([[0,1,20,1,1],[40,2,20,2,2],[41,999,20,3,3],[2500001,10000,25,10000,10000]] as [$total,$page,$size,$expected,$pages]) {
    $state = pagination_state($total,$page,$size);
    shared_page_check($state['page']===$expected && $state['total_pages']===$pages && $state['total']===$total, 'Exact, empty, stale and bounded page states');
}
foreach ([[1,1],[1,3],[2,3],[3,3],[500,10000],[10000,10000]] as [$page,$pages]) {
    $html = pagination_html(['page'=>$page,'total_pages'=>$pages,'total'=>$pages*20],
        static fn(int $target): string => '/returns.php?'.http_build_query(['q'=>'<tag>&','page'=>$target]), 'Return pages', 'loans');
    $dom = new DOMDocument(); @$dom->loadHTML($html); $xp = new DOMXPath($dom);
    shared_page_check($xp->query('//*[@aria-current="page"]')->length===1, 'One current page');
    shared_page_check($xp->query('//a')->length<=11, 'Bounded links for long lists');
    foreach ($xp->query('//a') as $link) {
        parse_str(parse_url($link->getAttribute('href'),PHP_URL_QUERY),$params);
        shared_page_check($params['q']==='<tag>&' && (int)$params['page']>=1 && (int)$params['page']<=$pages, 'Escaped filter and valid page destinations');
    }
    shared_page_check(!str_contains($html,'<tag>') && str_contains($html,'catalog-pagination') && str_contains($html,'Return pages'), 'Reuse theme and semantic nav');
}
echo "PASS: Shared numbered pagination bounds, escaping, filters and semantics.\n";
