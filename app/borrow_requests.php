<?php
declare(strict_types=1);
require_once __DIR__ . '/catalog.php';

function request_terms(mixed $days, mixed $reason): array
{
    $days = is_scalar($days) && !is_bool($days) ? filter_var($days, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>30]]) : false;
    if ($days === false || !is_string($reason)) {
        throw new InvalidArgumentException('Enter whole days from 1 to 30 and a borrowing reason.');
    }
    return [$days, required_text($reason, 10, 500, 'Borrowing reason')];
}

function require_request_student(?array $user): void
{
    if ($user === null || $user['role'] !== 'student' || $user['account_status'] !== 'active') {
        throw new DomainException('Borrowing requests require an active student account.');
    }
}

// Every mutation starts with the same user -> book -> request lock order.
function request_transaction(PDO $db, mixed $token, callable $operation): mixed
{
    if (!csrf_is_valid($token)) { throw new InvalidArgumentException('Your form expired. Refresh and try again.'); }
    for ($attempt = 0; ; $attempt++) {
        $db->exec("SET SESSION time_zone = '+00:00'");
        $db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $db->beginTransaction();
        try {
            $id = $_SESSION['user_id'] ?? null;
            if (!is_int($id)) { throw new DomainException('Please sign in as a student.'); }
            $s = $db->prepare('SELECT * FROM users WHERE user_id=? FOR UPDATE');
            $s->execute([$id]);
            $user = $s->fetch() ?: null;
            require_request_student($user);
            if ((int)$user['session_version'] !== ($_SESSION['session_version'] ?? null)
                || time() - ($_SESSION['last_activity'] ?? 0) >= 1800) {
                throw new DomainException('Your session expired. Please sign in again.');
            }
            $result = $operation($id);
            $db->commit();
            return $result;
        } catch (Throwable $e) {
            if ($db->inTransaction()) { $db->rollBack(); }
            if ($e instanceof PDOException && in_array((int)($e->errorInfo[1] ?? 0), [1205,1213], true)) {
                if ($attempt < 2) { usleep(random_int(10000,50000)); continue; }
                throw new DomainException('Another action is in progress. Refresh and try again.');
            }
            throw $e;
        }
    }
}

function create_borrow_request(PDO $db, int $bookId, mixed $days, mixed $reason, mixed $token): int
{
    [$days,$reason] = request_terms($days,$reason);
    if ($bookId < 1) { throw new InvalidArgumentException('Choose a valid book.'); }
    return request_transaction($db,$token,function(int $student) use($db,$bookId,$days,$reason): int {
        $s=$db->prepare('SELECT archived_at FROM books WHERE book_id=? FOR UPDATE');
        $s->execute([$bookId]); $book=$s->fetch();
        if (!$book || $book['archived_at'] !== null) { throw new InvalidArgumentException('This book is no longer listed.'); }
        $now=$db->query('SELECT UTC_TIMESTAMP()')->fetchColumn();
        $today=(new DateTimeImmutable($now,new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Manila'))->format('Y-m-d');
        $s=$db->prepare("SELECT COUNT(*) FROM borrow_requests WHERE student_id=? AND decision='pending'");
        $s->execute([$student]);
        if ((int)$s->fetchColumn() >= 5) { throw new InvalidArgumentException('You already have five pending requests.'); }
        $s=$db->prepare("SELECT r.book_id,l.checked_out_at,l.due_date FROM loans l JOIN borrow_requests r ON r.request_id=l.request_id
            WHERE r.student_id=? AND l.returned_at IS NULL AND l.cancelled_at IS NULL
            AND (l.checked_out_at IS NOT NULL OR r.decided_at > DATE_SUB(?,INTERVAL 48 HOUR))");
        $s->execute([$student,$now]); $live=$s->fetchAll();
        foreach($live as $loan) {
            if ($loan['checked_out_at'] !== null && $loan['due_date'] < $today) { throw new InvalidArgumentException('Return your overdue books before requesting another book.'); }
            if ((int)$loan['book_id'] === $bookId) { throw new InvalidArgumentException('You already have an outstanding borrowing record for this title.'); }
        }
        if (count($live) >= 5) { throw new InvalidArgumentException('Your five-book borrowing allowance is full.'); }
        $s=$db->prepare("SELECT request_id FROM borrow_requests WHERE student_id=? AND book_id=? AND decision='pending'");
        $s->execute([$student,$bookId]);
        if ($s->fetch()) { throw new InvalidArgumentException('You already requested this title.'); }
        $s=$db->prepare('SELECT COUNT(*) FROM book_copies c WHERE c.book_id=? AND c.archived_at IS NULL
            AND NOT EXISTS(SELECT 1 FROM loans l JOIN borrow_requests r ON r.request_id=l.request_id
            WHERE l.copy_id=c.copy_id AND l.returned_at IS NULL AND l.cancelled_at IS NULL
            AND (l.checked_out_at IS NOT NULL OR r.decided_at > DATE_SUB(?,INTERVAL 48 HOUR)))');
        $s->execute([$bookId,$now]);
        if ((int)$s->fetchColumn() === 0) { throw new InvalidArgumentException('No copies are available right now. Please check again later.'); }
        $db->prepare('INSERT INTO borrow_requests(student_id,book_id,requested_days,request_reason,requested_at) VALUES(?,?,?,?,?)')->execute([$student,$bookId,$days,$reason,$now]);
        $id=(int)$db->lastInsertId();
        record_audit_event($db,$student,'request.created','borrow_request',$id,'Pending borrowing request submitted.');
        return $id;
    });
}

function cancel_borrow_request(PDO $db, int $requestId, mixed $token): void
{
    request_transaction($db,$token,function(int $student) use($db,$requestId): void {
        $s=$db->prepare('SELECT book_id FROM borrow_requests WHERE request_id=? AND student_id=?');
        $s->execute([$requestId,$student]); $bookId=$s->fetchColumn();
        if ($bookId === false) { throw new InvalidArgumentException('Request not found.'); }
        $db->prepare('SELECT book_id FROM books WHERE book_id=? FOR UPDATE')->execute([$bookId]);
        $s=$db->prepare('SELECT decision FROM borrow_requests WHERE request_id=? AND student_id=? FOR UPDATE');
        $s->execute([$requestId,$student]); $decision=$s->fetchColumn();
        if ($decision === 'cancelled') { return; }
        if ($decision !== 'pending') { throw new InvalidArgumentException('Only pending requests can be cancelled here.'); }
        $db->prepare("UPDATE borrow_requests SET decision='cancelled',decided_at=UTC_TIMESTAMP(),decided_by=? WHERE request_id=?")->execute([$student,$requestId]);
        record_audit_event($db,$student,'request.cancelled','borrow_request',$requestId,'Student cancelled pending request.');
    });
}

function request_history_filter(mixed $filter): string
{
    if (!is_string($filter) || !in_array($filter, ['all', 'pending', 'rejected', 'awaiting_pickup', 'active'], true)) {
        throw new InvalidArgumentException('Choose a valid request tab.');
    }
    return $filter;
}

function own_borrow_requests(PDO $db, int $page, string $filter = 'all'): array
{
    $user=current_user($db);
    if ($user===null || $user['role']!=='student' || !in_array($user['account_status'],['pending','active'],true)) {
        throw new DomainException('Sign in as a student to view your requests.');
    }
    if ($page < 1 || $page > 10000) { throw new InvalidArgumentException('Choose a valid page.'); }
    $filter = request_history_filter($filter);
    $condition = match ($filter) {
        'pending' => " WHERE lifecycle='pending'",
        'rejected' => " WHERE lifecycle='rejected'",
        'awaiting_pickup' => " WHERE lifecycle='awaiting pickup'",
        'active' => " WHERE lifecycle IN ('active','overdue')",
        default => ''
    };
    $s=$db->prepare("SELECT * FROM (SELECT r.*,b.title,l.checked_out_at,l.due_date,CASE WHEN r.decision <> 'approved' THEN r.decision
        WHEN l.returned_at IS NOT NULL THEN 'returned' WHEN l.cancelled_at IS NOT NULL THEN COALESCE(l.cancellation_kind,'cancelled')
        WHEN l.checked_out_at IS NOT NULL THEN IF(l.due_date < DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR),'overdue','active')
        WHEN r.decided_at <= UTC_TIMESTAMP()-INTERVAL 48 HOUR THEN 'expired' ELSE 'awaiting pickup' END AS lifecycle
        FROM borrow_requests r JOIN books b ON b.book_id=r.book_id LEFT JOIN loans l ON l.request_id=r.request_id
        WHERE r.student_id=?) AS own_history" . $condition . " ORDER BY requested_at DESC,request_id DESC LIMIT 21 OFFSET " . (($page-1)*20));
    $s->execute([$_SESSION['user_id']]); return $s->fetchAll();
}
