<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/views/return_ui.php';
start_application_session();
function refinement_assert(bool $ok):void {if(!$ok)throw new RuntimeException('Refinement assertion failed');}
refinement_assert(str_starts_with(return_success_message('returned',fn()=>throw new RuntimeException('Optional failure')),'Physical return recorded.'));
refinement_assert(str_contains(return_success_message('already_returned',fn()=>false),'already recorded'));
$row=['request_id'=>8,'checked_out_at'=>'2026-11-01 12:00:00','due_date'=>'2026-11-08','overdue'=>false,'title'=>'<script>Book','student_name'=>'<img>Student','username'=>'fictional','copy_code'=>'A"8'];
$html=return_card_html($row,8,'Receipt <required>',false);
refinement_assert(str_contains($html,'id="receipt-8"') && str_contains($html,'aria-invalid="true"') && str_contains($html,'id="return-8-error"'));
refinement_assert(!str_contains($html,'<script>Book') && str_contains($html,'&lt;script&gt;Book'));
refinement_assert(str_contains($html,'Confirm return of &lt;script&gt;Book'));
$html=return_card_html($row,8,'Form expired',true);
refinement_assert(str_contains($html,' required checked') && !str_contains($html,'aria-invalid'));
refinement_assert(!str_contains(return_card_html($row,9,'Other row',true),'Other row'));
echo "Return optional-context fallback, contextual labels, escaping, row association and confirmation preservation PASS.\n";
