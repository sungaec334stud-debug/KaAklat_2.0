<?php
declare(strict_types=1);

function dashboard_counts(PDO $db): array
{
    $user=current_user($db);
    if($user===null || !in_array($user['role'],['student','librarian'],true)) { throw new DomainException('Dashboard counts are unavailable for this account.'); }
    $student=$user['role']==='student';
    $where=$student?' WHERE r.student_id=?':'';
    $params=$student?[(int)$user['user_id']]:[];
    $s=$db->prepare("SELECT COUNT(*) FROM borrow_requests r".$where.($student?' AND':' WHERE')." r.decision='pending'");
    $s->execute($params); $pending=(int)$s->fetchColumn();
    $s=$db->prepare("SELECT COALESCE(SUM(l.checked_out_at IS NULL AND r.decided_at>UTC_TIMESTAMP()-INTERVAL 48 HOUR),0) AS awaiting,
        COALESCE(SUM(l.checked_out_at IS NOT NULL),0) AS active,
        COALESCE(SUM(l.checked_out_at IS NOT NULL AND l.due_date<DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)),0) AS overdue
        FROM loans l JOIN borrow_requests r ON r.request_id=l.request_id".$where.($student?' AND':' WHERE')." l.returned_at IS NULL AND l.cancelled_at IS NULL");
    $s->execute($params); $counts=$s->fetch();
    return ['pending'=>$pending,'awaiting'=>(int)$counts['awaiting'],'active'=>(int)$counts['active'],'overdue'=>(int)$counts['overdue']];
}
