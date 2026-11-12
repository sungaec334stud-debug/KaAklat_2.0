<?php
declare(strict_types=1);
require_once __DIR__.'/staff_accounts.php';
function list_audit_events(PDO $db,int $page,string $event,string $actor, ?array &$pagination = null): array
{
    staff_actor($db);
    if ($page<1 || $page>10000 || ($event!=='' && !preg_match('/^[a-z][a-z0-9._-]{1,63}$/D',$event)) || mb_strlen($actor)>32) { throw new InvalidArgumentException('Choose a valid page, event code and actor username.'); }
    $sql='SELECT a.audit_id,a.occurred_at,a.event_code,a.entity_type,a.entity_id,u.username AS actor_username FROM audit_events a LEFT JOIN users u ON u.user_id=a.actor_user_id WHERE 1=1'; $args=[];
    if ($event!=='') {$sql.=' AND a.event_code=?'; $args[]=$event;}
    if ($actor!=='') {$sql.=' AND u.username=?'; $args[]=normalize_username($actor);}
    return pagination_rows($db, $sql, $args, 'a.occurred_at DESC,a.audit_id DESC', $page, 25, $pagination, 10000, func_num_args() >= 5);
}
