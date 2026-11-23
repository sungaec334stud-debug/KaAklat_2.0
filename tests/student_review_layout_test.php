<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/views/student_review.php';
$student=['user_id'=>123,'session_version'=>7,'school_student_id'=>'26-0123-456'];
$html=student_identity_review_html($student,'/students.php?status=pending',str_repeat('a',64));
$dom=new DOMDocument();@$dom->loadHTML('<?xml encoding="UTF-8">'.$html);$xpath=new DOMXPath($dom);
function review_check(bool $ok,string $label):void{if(!$ok)throw new RuntimeException($label);echo "PASS: $label\n";}
review_check($xpath->query('//section[contains(@class,"student-review-action")]')->length===3,'Three distinct action sections');
review_check($xpath->query('//form')->length===3 && $xpath->query('//form//form')->length===0,'Independent non-nested native forms');
foreach($xpath->query('//form')as$form){$x=new DOMXPath($dom);review_check($x->query('.//input[@name="identity_confirmation" and @required]',$form)->length===1,'One required confirmation per action');review_check($x->query('.//input[@name="csrf_token"]',$form)->length===1 && $x->query('.//input[@name="review_version" and @value="7"]',$form)->length===1,'CSRF and stale version retained');}
$labels=[];foreach($xpath->query('//label[contains(@class,"student-review-confirmation")]')as$label){$labels[]=trim($label->textContent);}review_check(count(array_unique($labels))===3,'Action-specific confirmation labels');
review_check($xpath->query('//input[@name="replacement_id" and @required and @aria-describedby="replacement-hint-123"]')->length===1,'ID field has required state and linked format guidance');
$student['school_student_id']=null;$html=student_identity_review_html($student,'/students.php?status=pending',str_repeat('a',64));review_check(substr_count($html,'<form ')===1 && !str_contains($html,'value="activate"') && !str_contains($html,'value="release_id"'),'Unclaimed account retains correction only');
echo "Student review layout checks passed.\n";
