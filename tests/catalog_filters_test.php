<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/catalog_management.php';
function filters_assert(bool $ok): void { if (!$ok) { throw new RuntimeException('Filter assertion failed'); } }
filters_assert(catalog_filters(['letter'=>'a','year'=>'1813','author'=>' Austen ','category'=>'2']) === ['letter'=>'A','year'=>1813,'author'=>'Austen','category'=>2,'title'=>'']);
foreach ([['letter'=>'AA'],['year'=>'1 OR 1=1'],['category'=>[]],['author'=>[]],['year'=>'0']] as $input) {
    try { catalog_filters($input); throw new RuntimeException('Invalid filter accepted'); } catch (InvalidArgumentException) {}
}
filters_assert(catalog_publication_year('') === null);
filters_assert(catalog_publication_year('1813') === 1813);
try { catalog_publication_year((string) ((int) date('Y') + 1)); throw new RuntimeException('Future year accepted'); } catch (InvalidArgumentException) {}
echo "Category/year filter validation tests passed.\n";
