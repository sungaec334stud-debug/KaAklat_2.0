<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/catalog.php';
require_once dirname(__DIR__).'/views/managed_catalog.php';
$results=['books'=>[['book_id'=>7,'title'=>'<script>Book</script>','authors'=>'A & B','category_name'=>'Arts <study>','cover_path'=>null,'available_copies'=>2,'total_copies'=>3]],'page'=>2,'total'=>25,'total_pages'=>3,'has_next'=>true];
$html=managed_catalog_html($results,'A & B',7);
foreach(['&lt;script&gt;Book&lt;/script&gt;','Arts &lt;study&gt;','2 / 3','Catalog records','Managed book pages','aria-current="true"'] as $value) {
    if(!str_contains($html,$value))throw new RuntimeException('Missing escaped record/context: '.$value);
}
if(str_contains($html,'<script>'))throw new RuntimeException('Unescaped title');
$url=managed_book_url(7,'A & B',2,3);
parse_str(parse_url($url,PHP_URL_QUERY),$query);
if($query!==['q'=>'A & B','page'=>'2','id'=>'7','copies_page'=>'3'])throw new RuntimeException('Selection/copy context lost');
$empty=$results;$empty['books']=[];$empty['total']=0;$empty['page']=1;$empty['total_pages']=1;
if(!str_contains(managed_catalog_html($empty,'Missing',null),'No books found'))throw new RuntimeException('Empty state absent');
echo "Managed catalog escaping, selected state, derived counts, context URLs, pagination and empty state PASS.\n";
