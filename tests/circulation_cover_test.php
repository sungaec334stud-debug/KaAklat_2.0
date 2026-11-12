<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/views/circulation_ui.php';
$row=['book_id'=>7,'cover_path'=>'upload-private-key.jpg','book_archived_at'=>null];
$html=circulation_cover_html($row);
if(!str_contains($html,'src="/cover.php?id=7"') || str_contains($html,'upload-private-key'))throw new RuntimeException('Cover must use authenticated endpoint without storage path');
foreach([['book_id'=>'7" onerror="bad','cover_path'=>'x'],['book_id'=>7,'cover_path'=>null],$row+['unused'=>1]] as $index=>$input){
    if($index===2){$input['book_archived_at']='2026-01-01';}
    $value=circulation_cover_html($input);
    if(str_contains($value,'<img') || !str_contains($value,'No cover'))throw new RuntimeException('Missing, invalid or archived cover must have honest fallback');
}
echo "Protected circulation cover URLs, private-path exclusion, invalid IDs and missing/archived fallbacks PASS.\n";
