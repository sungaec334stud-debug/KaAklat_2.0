<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/views/student_account_ui.php';
$row=['school_student_id'=>'06-0001-001','created_at'=>'2026-10-31 16:30:00','activated_at'=>'2026-10-31 17:30:00','suspended_at'=>'2026-11-01 02:00:00','reactivated_at'=>null,'account_status'=>'suspended'];
$html=student_account_details_html($row,true);
if(!str_contains($html,'06-0001-001') || !str_contains($html,'Nov 1, 2026 1:30 am') || !str_contains($html,'Last suspended'))throw new RuntimeException('Identity and recorded Manila timestamps missing');
if(str_contains(student_account_details_html($row,false),'06-0001-001'))throw new RuntimeException('School ID exposed to non-librarian view');
$row['activated_at']=null;$row['suspended_at']=null;
if(substr_count(student_account_details_html($row,true),'Not recorded')!==2)throw new RuntimeException('Missing dates must not be inferred from updated_at');
$row['school_student_id']='<script>';
if(str_contains(student_account_details_html($row,true),'<script>'))throw new RuntimeException('Unescaped identity');
echo "Student detail ID scope/escaping, recorded Manila dates and unknown-date fallback PASS.\n";
