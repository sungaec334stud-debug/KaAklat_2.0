<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/audit.php';
require_once dirname(__DIR__).'/views/layout.php';
require_once dirname(__DIR__).'/views/time.php';
start_application_session(); header('Cache-Control: no-store');
try {
    $db=database_connection(application_config()['database']); if(current_user($db)===null) redirect('/login.php'); staff_actor($db);
    if($_SERVER['REQUEST_METHOD']!=='GET') {http_response_code(405); header('Allow: GET'); exit;}
    $page=filter_var($_GET['page']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>10000]]); $event=$_GET['event']??''; $actor=$_GET['actor']??'';
    if($page===false || !is_string($event) || !is_string($actor)) throw new InvalidArgumentException('Choose valid filters.');
    $rows=list_audit_events($db,$page,$event,$actor,$pagination); $page=$pagination['page'];
    $html='<section class="panel"><h1>Audit records</h1><p>Read-only security and workflow metadata. Borrowing reasons, decision explanations, passwords and recovery secrets are not displayed. Times below are Manila time.</p><form method="get"><label for="event">Exact event code (optional)</label><input id="event" name="event" maxlength="64" value="'.h($event).'"><label for="actor">Actor username (optional)</label><input id="actor" name="actor" maxlength="32" value="'.h($actor).'"><button>Filter events</button> <a href="/audit.php">Clear</a></form>';
    if(!$rows) $html.='<p>No audit events on this page. Clear filters or return to the first page.</p>';
    foreach(array_slice($rows,0,25) as $row) {
        $time=display_manila_time($row['occurred_at'], 'M j, Y g:i:s a');
        $html.='<article class="management-section audit-record"><h2>'.h($row['event_code']).'</h2><p>'.h($time).' (Manila)</p><p>Actor: '.h($row['actor_username']??'System / local operator').'</p><p>Affected record: '.h($row['entity_type']).' '.h($row['entity_id']??'—').'</p></article>';
    }
    $html .= pagination_html($pagination, static fn(int $target): string => '?'.http_build_query(['event'=>$event,'actor'=>$actor,'page'=>$target]), 'Audit pages', 'events');
    render_page('Audit records',$html.'</section>');
} catch(DomainException $e) {http_response_code(403); render_page('Access denied','<p role="alert">'.h($e->getMessage()).'</p>');}
catch(InvalidArgumentException $e) {http_response_code(400); render_page('Check audit filters','<p role="alert">'.h($e->getMessage()).'</p><a href="/audit.php">Audit records</a>');}
catch(Throwable $e) {$ref=bin2hex(random_bytes(8)); error_log('Audit read ['.$ref.'] code='.$e->getCode()); http_response_code(500); render_page('Audit unavailable','<p role="alert">Unable to load audit records. Reference: '.h($ref).'</p>');}
