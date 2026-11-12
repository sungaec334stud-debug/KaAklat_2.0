<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/catalog.php';
function discovery_check(bool $ok,string $label):void{if(!$ok)throw new RuntimeException($label);}
function discovery_invalid(array $input):void{try{catalog_filters($input);}catch(InvalidArgumentException){return;}throw new RuntimeException('Invalid filters accepted');}
discovery_check(catalog_filters(['available'=>'0'])['available']==='0','Unavailable filter accepted');
foreach(['title_asc','title_desc','year_desc','year_asc'] as $sort)discovery_check(catalog_filters(['sort'=>$sort])['sort']===$sort,'Sort allowlist');
foreach([['sort'=>'title; DELETE'],['sort'=>[]],['year_from'=>true],['category'=>true],['year_from'=>[]],['year_to'=>'0'],['year_from'=>2001,'year_to'=>1999],['year'=>1999,'year_from'=>2000],['available'=>'2']] as $input)discovery_invalid($input);
$legacy=catalog_filters(['year'=>'1813']);discovery_check($legacy['year_from']===1813&&$legacy['year_to']===1813,'Legacy exact-year maps to bounds');
$filters=catalog_filters(['year_from'=>2000,'available'=>'0','sort'=>'year_desc','author'=>'O\'Brien','title'=>'A&B','category'=>2]);
$url=catalog_url('100%_!',3,$filters,7);parse_str(parse_url($url,PHP_URL_QUERY),$params);
discovery_check(str_starts_with($url,'/catalog.php?')&&$params['id']==='7'&&$params['page']==='3'&&$params['sort']==='year_desc'&&$params['q']==='100%_!','Allowlisted detail context');
parse_str(parse_url(catalog_remove_filter_url('100%_!',3,$filters,'author'),PHP_URL_QUERY),$params);
discovery_check(!isset($params['author'])&&$params['page']==='1'&&$params['sort']==='year_desc','Removing one filter resets only page/context');
try{catalog_remove_filter_url('',1,[],'return');throw new RuntimeException('Unknown removal accepted');}catch(InvalidArgumentException){}
echo "PASS: Catalog discovery validation and local context URLs.\n";
