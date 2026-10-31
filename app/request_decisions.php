<?php
declare(strict_types=1);
require_once __DIR__ . '/borrow_requests.php';

function require_request_librarian(?array $user): void
{
    if ($user === null || $user['role'] !== 'librarian' || $user['account_status'] !== 'active') {
        throw new DomainException('Only active librarians can review borrowing requests.');
    }
}

function decision_terms(mixed $days, mixed $note): array
{
    $days = is_scalar($days) && !is_bool($days) ? filter_var($days, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>30]]) : false;
    if ($days === false || !is_string($note)) { throw new InvalidArgumentException('Choose whole days from 1 to 30 and a valid explanation.'); }
    $note = trim($note);
    if (mb_strlen($note) > 500) { throw new InvalidArgumentException('Explanation must contain at most 500 characters.'); }
    return [$days, $note];
}

function decide_borrow_request(PDO $db, int $requestId, string $action, mixed $days, mixed $note, mixed $token): void
{
    if (!csrf_is_valid($token)) { throw new InvalidArgumentException('Your form expired. Refresh and try again.'); }
    if ($requestId < 1 || !in_array($action, ['approve','reject'], true)) { throw new InvalidArgumentException('Choose a valid request and decision.'); }
    [$approvedDays,$note] = decision_terms($action === 'approve' ? $days : 1, $note);
    if ($action === 'reject' && $note === '') { throw new InvalidArgumentException('Explain why this request is rejected.'); }
    for ($attempt=0; ;$attempt++) {
        $db->exec("SET SESSION time_zone = '+00:00'");
        $db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $db->beginTransaction();
        try {
            $actor = $_SESSION['user_id'] ?? null;
            if (!is_int($actor)) { throw new DomainException('Please sign in again.'); }
            // This preliminary read obtains immutable ancestor IDs only; revalidate below.
            $s=$db->prepare('SELECT student_id,book_id FROM borrow_requests WHERE request_id=?');
            $s->execute([$requestId]); $ancestors=$s->fetch();
            if (!$ancestors) { throw new InvalidArgumentException('Request not found.'); }
            $studentId=(int)$ancestors['student_id']; $bookId=(int)$ancestors['book_id'];
            $ids=array_unique([$actor,$studentId]); sort($ids,SORT_NUMERIC); $users=[];
            foreach ($ids as $id) {
                $s=$db->prepare('SELECT * FROM users WHERE user_id=? FOR UPDATE'); $s->execute([$id]); $users[$id]=$s->fetch();
            }
            require_request_librarian($users[$actor] ?: null);
            if ((int)$users[$actor]['session_version'] !== ($_SESSION['session_version'] ?? null)
                || !is_int($_SESSION['last_activity'] ?? null) || time()-$_SESSION['last_activity'] >= 1800) {
                throw new DomainException('Please sign in again.');
            }
            $s=$db->prepare('SELECT * FROM books WHERE book_id=? FOR UPDATE'); $s->execute([$bookId]); $book=$s->fetch();
            // Book lock serializes allocation/archive. Lock every copy before any request.
            $s=$db->prepare('SELECT * FROM book_copies WHERE book_id=? ORDER BY copy_id FOR UPDATE'); $s->execute([$bookId]); $copies=$s->fetchAll();
            $s=$db->prepare('SELECT l.loan_id,l.request_id FROM loans l JOIN book_copies c ON c.copy_id=l.copy_id WHERE c.book_id=? AND l.returned_at IS NULL AND l.cancelled_at IS NULL');
            $s->execute([$bookId]); $open=$s->fetchAll();
            $requestIds=array_unique(array_merge([$requestId],array_map('intval',array_column($open,'request_id')))); sort($requestIds,SORT_NUMERIC); $requests=[];
            foreach($requestIds as $id) { $s=$db->prepare('SELECT * FROM borrow_requests WHERE request_id=? FOR UPDATE'); $s->execute([$id]); $requests[$id]=$s->fetch(); }
            $loanIds=array_map('intval',array_column($open,'loan_id')); sort($loanIds,SORT_NUMERIC); $loans=[];
            foreach($loanIds as $id) { $s=$db->prepare('SELECT * FROM loans WHERE loan_id=? FOR UPDATE'); $s->execute([$id]); $loan=$s->fetch(); $loans[(int)$loan['copy_id']]=$loan; }
            $request=$requests[$requestId];
            if (!$request || (int)$request['student_id']!==$studentId || (int)$request['book_id']!==$bookId || $request['decision']!=='pending') {
                throw new InvalidArgumentException('This request is no longer pending. Refresh the list.');
            }
            $now=(string)$db->query('SELECT UTC_TIMESTAMP()')->fetchColumn();
            if ($action==='approve') {
                if (!$users[$studentId] || $users[$studentId]['role']!=='student' || $users[$studentId]['account_status']!=='active') { throw new InvalidArgumentException('The student must have an active account.'); }
                if (!$book || $book['archived_at']!==null) { throw new InvalidArgumentException('This book is archived or unavailable.'); }
                if ($approvedDays!==(int)$request['requested_days'] && $note==='') { throw new InvalidArgumentException('Explain the change to the requested duration.'); }
                $s=$db->prepare("SELECT r.book_id,l.checked_out_at,l.due_date FROM loans l JOIN borrow_requests r ON r.request_id=l.request_id WHERE r.student_id=? AND l.returned_at IS NULL AND l.cancelled_at IS NULL AND (l.checked_out_at IS NOT NULL OR r.decided_at>DATE_SUB(?,INTERVAL 48 HOUR))");
                $s->execute([$studentId,$now]); $live=$s->fetchAll();
                $today=(new DateTimeImmutable($now,new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Manila'))->format('Y-m-d');
                foreach($live as $allocation) {
                    if ($allocation['checked_out_at']!==null && $allocation['due_date']<$today) { throw new InvalidArgumentException('The student has an overdue loan.'); }
                    if ((int)$allocation['book_id']===$bookId) { throw new InvalidArgumentException('The student already has this title allocated or borrowed.'); }
                }
                if (count($live)>=5) { throw new InvalidArgumentException('The student has reached the five-book allowance.'); }
                $s=$db->prepare("SELECT request_id FROM borrow_requests WHERE student_id=? AND book_id=? AND decision='pending' AND request_id<>?"); $s->execute([$studentId,$bookId,$requestId]);
                if($s->fetch()) { throw new InvalidArgumentException('The student already has another request for this title.'); }
                $cutoff=(new DateTimeImmutable($now,new DateTimeZone('UTC')))->modify('-48 hours')->format('Y-m-d H:i:s');
                $chosen=null; $expired=null;
                foreach($copies as $copy) {
                    if($copy['archived_at']!==null) { continue; }
                    $loan=$loans[(int)$copy['copy_id']] ?? null;
                    if($loan!==null && ($loan['checked_out_at']!==null || $requests[(int)$loan['request_id']]['decided_at']>$cutoff)) { continue; }
                    $chosen=(int)$copy['copy_id']; $expired=$loan; break;
                }
                if($chosen===null) { throw new InvalidArgumentException('No eligible copies are available. The request remains pending.'); }
                if($expired!==null) {
                    $db->prepare("UPDATE loans SET cancelled_at=?,cancellation_kind='expired',cancelled_by=NULL WHERE loan_id=?")->execute([$now,$expired['loan_id']]);
                    record_audit_event($db,null,'loan.expired','loan',(int)$expired['loan_id'],'Uncollected pickup allocation expired.');
                }
                $db->prepare("UPDATE borrow_requests SET decision='approved',approved_days=?,decided_at=?,decided_by=?,decision_note=? WHERE request_id=?")->execute([$approvedDays,$now,$actor,$note===''?null:$note,$requestId]);
                $db->prepare('INSERT INTO loans(request_id,copy_id) VALUES(?,?)')->execute([$requestId,$chosen]);
            } else {
                $db->prepare("UPDATE borrow_requests SET decision='rejected',decided_at=?,decided_by=?,decision_note=? WHERE request_id=?")->execute([$now,$actor,$note,$requestId]);
            }
            record_audit_event($db,$actor,$action==='approve'?'request.approved':'request.rejected','borrow_request',$requestId,$action==='approve'?'Borrowing request approved for collection.':'Borrowing request rejected.');
            $db->commit(); return;
        } catch(Throwable $e) {
            if($db->inTransaction()) { $db->rollBack(); }
            if($e instanceof PDOException && in_array((int)($e->errorInfo[1]??0),[1205,1213],true)) {
                if($attempt<2) { usleep(random_int(10000,50000)); continue; }
                throw new DomainException('The request is busy. Refresh and try again.');
            }
            throw $e;
        }
    }
}

function list_review_requests(PDO $db, int $page, string $search=''): array
{
    require_request_librarian(current_user($db));
    if($page<1 || $page>10000 || mb_strlen($search)>100) { throw new InvalidArgumentException('Choose a valid page and search of at most 100 characters.'); }
    $sql="SELECT r.*,b.title,u.display_name AS student_name,u.username,u.account_status,
        (SELECT COUNT(*) FROM book_copies c WHERE c.book_id=b.book_id AND c.archived_at IS NULL AND b.archived_at IS NULL AND NOT EXISTS(SELECT 1 FROM loans l JOIN borrow_requests a ON a.request_id=l.request_id WHERE l.copy_id=c.copy_id AND l.returned_at IS NULL AND l.cancelled_at IS NULL AND (l.checked_out_at IS NOT NULL OR a.decided_at>UTC_TIMESTAMP()-INTERVAL 48 HOUR))) AS available_copies,
        (SELECT COUNT(*) FROM loans l JOIN borrow_requests a ON a.request_id=l.request_id WHERE a.student_id=u.user_id AND l.returned_at IS NULL AND l.cancelled_at IS NULL AND (l.checked_out_at IS NOT NULL OR a.decided_at>UTC_TIMESTAMP()-INTERVAL 48 HOUR)) AS live_allowance,
        EXISTS(SELECT 1 FROM loans l JOIN borrow_requests a ON a.request_id=l.request_id WHERE a.student_id=u.user_id AND l.checked_out_at IS NOT NULL AND l.returned_at IS NULL AND l.cancelled_at IS NULL AND l.due_date<DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)) AS has_overdue
        FROM borrow_requests r JOIN books b ON b.book_id=r.book_id JOIN users u ON u.user_id=r.student_id WHERE r.decision='pending'";
    $args=[];
    if(trim($search)!=='') { $sql.=' AND (LOCATE(?,b.title)>0 OR LOCATE(?,u.display_name)>0 OR LOCATE(?,u.username)>0)'; $args=array_fill(0,3,trim($search)); }
    $s=$db->prepare($sql.' ORDER BY r.requested_at,r.request_id LIMIT 21 OFFSET '.(($page-1)*20)); $s->execute($args); return $s->fetchAll();
}
