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

function dashboard_preview(PDO $db): array
{
    $user = current_user($db);
    if ($user === null || ($user['role'] !== 'student' && $user['account_status'] !== 'active')) {
        throw new DomainException('Dashboard preview requires an eligible account.');
    }
    $openLoans = "FROM loans l JOIN borrow_requests r ON r.request_id = l.request_id
        JOIN books b ON b.book_id = r.book_id";
    $livePickup = "l.checked_out_at IS NULL AND r.decided_at > UTC_TIMESTAMP() - INTERVAL 48 HOUR";
    $outstanding = "r.decision = 'approved' AND l.returned_at IS NULL AND l.cancelled_at IS NULL";
    if ($user['role'] === 'student') {
        // A Manila due date ends at midnight the next day, or 16:00 UTC on the due date.
        $statement = $db->prepare("SELECT b.title, l.due_date,
            CASE WHEN l.checked_out_at IS NULL THEN DATE_ADD(r.decided_at, INTERVAL 48 HOUR)
                ELSE TIMESTAMP(l.due_date) + INTERVAL 16 HOUR END AS deadline_utc
            $openLoans WHERE r.student_id = ? AND $outstanding
            AND (($livePickup) OR l.checked_out_at IS NOT NULL)
            ORDER BY deadline_utc, l.loan_id LIMIT 1");
        $statement->execute([(int) $user['user_id']]);
        return $statement->fetchAll();
    }
    if ($user['role'] === 'librarian') {
        $pending = $db->query("SELECT b.title, u.display_name AS student_name, r.requested_at AS event_time
            FROM borrow_requests r JOIN books b ON b.book_id = r.book_id
            JOIN users u ON u.user_id = r.student_id WHERE r.decision = 'pending'
            ORDER BY r.requested_at, r.request_id LIMIT 2")->fetchAll();
        $pickups = $db->query("SELECT b.title, u.display_name AS student_name,
            DATE_ADD(r.decided_at, INTERVAL 48 HOUR) AS event_time
            $openLoans JOIN users u ON u.user_id = r.student_id WHERE $outstanding AND $livePickup
            ORDER BY r.decided_at, r.request_id LIMIT 2")->fetchAll();
        return ['pending' => $pending, 'pickups' => $pickups];
    }
    if ($user['role'] === 'administrator') {
        return $db->query("SELECT a.event_code, a.occurred_at, u.username AS actor_username
            FROM audit_events a LEFT JOIN users u ON u.user_id = a.actor_user_id
            WHERE a.entity_type = 'users' AND a.event_code IN
                ('staff.created', 'staff.updated', 'student.activate', 'student.suspend', 'student.reactivate')
            ORDER BY a.occurred_at DESC, a.audit_id DESC LIMIT 3")->fetchAll();
    }
    throw new DomainException('Dashboard preview is unavailable for this account.');
}
