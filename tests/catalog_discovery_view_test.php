<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/views/catalog_filters.php';
require_once __DIR__.'/catalog_discovery_test.php';
$input=['q'=>'<script>alert(1)</script>','author'=>"O'Brien",'year_from'=>'2001','year_to'=>'1999','sort'=>'year_desc'];
$html=catalog_filter_form_html($input,[['category_id'=>1,'category_name'=>'<Category>']],'From year must be earlier than or equal to To year.');
discovery_check(!str_contains($html,'<script>')&&str_contains($html,'&lt;script&gt;'),'Raw retained inputs escaped');
$dom=new DOMDocument();@$dom->loadHTML($html);$xp=new DOMXPath($dom);
discovery_check($xp->query('//form[@method="get" and @action="/catalog.php"]')->length===1&&$xp->query('//input[@name="page"]')->length===0,'Native apply resets page');
discovery_check($xp->query('//details[@open]')->length===1&&$xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," error-summary ")]')->length===1&&$xp->query('//a[@href="#year-from"]')->length===1,'Advanced validation link targets visible field');
discovery_check($xp->query('//input[@name="year_from" and @value="2001"]')->length===1&&$xp->query('//input[@name="year_to" and @value="1999"]')->length===1,'Invalid range values retained');
$f=catalog_filters(['author'=>'<Author>','year_from'=>2000,'available'=>'0','sort'=>'year_desc']);$html=catalog_active_filters_html('Sample',3,$f,[]);@$dom->loadHTML($html);$xp=new DOMXPath($dom);
foreach($xp->query('//a') as $a){$url=$a->getAttribute('href');parse_str(parse_url($url,PHP_URL_QUERY),$p);discovery_check(str_starts_with($url,'/catalog.php?')&&$p['page']==='1','Every removal is local and resets page');}
discovery_check(!str_contains($html,'<Author>'),'Filter labels escaped');
discovery_check(str_contains(catalog_unavailable_badge_html(['available_copies'=>0]),'Currently unavailable')&&catalog_unavailable_badge_html(['available_copies'=>1])==='','Text badge only when unavailable');
echo "PASS: Catalog native form, retained validation, chips, escaping and unavailable badge.\n";
