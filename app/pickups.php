<?php
declare(strict_types=1);
require_once __DIR__.'/request_decisions.php';

function pickup_due_date(string $utc, int $days): string
{
    if($days<1 || $days>30) { throw new InvalidArgumentException('Approved days must be between 1 and 30.'); }
    return (new DateTimeImmutable($utc,new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Manila'))->modify("+$days days")->format('Y-m-d');
}

function pickup_deadline_expired(string $approvedUtc,string $nowUtc): bool
{
    return new DateTimeImmutable($nowUtc,new DateTimeZone('UTC')) >= (new DateTimeImmutable($approvedUtc,new DateTimeZone('UTC')))->modify('+48 hours');
}

function record_pickup(PDO $db, int $requestId, mixed $token): string
{
    if(!csrf_is_valid($token)) { throw new InvalidArgumentException('Your form expired. Refresh and try again.'); }
    if($requestId<1) { throw new InvalidArgumentException('Choose a valid pickup.'); }
    for($attempt=0;;$attempt++) {
        $db->exec("SET SESSION time_zone = '+00:00'");
        $db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED'); $db->beginTransaction();
        try {
            $actor=$_SESSION['user_id']??null;
            if(!is_int($actor)) { throw new DomainException('Please sign in again.'); }
            $s=$db->prepare('SELECT r.student_id,r.book_id,l.copy_id,l.loan_id FROM borrow_requests r JOIN loans l ON l.request_id=r.request_id WHERE r.request_id=?'); $s->execute([$requestId]); $ids=$s->fetch();
            if(!$ids) { throw new InvalidArgumentException('Pickup allocation not found.'); }
            $student=(int)$ids['student_id']; $users=[]; $userIds=array_unique([$actor,$student]); sort($userIds,SORT_NUMERIC);
            foreach($userIds as $id) { $s=$db->prepare('SELECT * FROM users WHERE user_id=? FOR UPDATE'); $s->execute([$id]); $users[$id]=$s->fetch(); }
            require_request_librarian($users[$actor]?:null);
            if((int)$users[$actor]['session_version']!==($_SESSION['session_version']??null) || !is_int($_SESSION['last_activity']??null) || time()-$_SESSION['last_activity']>=1800) { throw new DomainException('Please sign in again.'); }
            $s=$db->prepare('SELECT * FROM books WHERE book_id=? FOR UPDATE'); $s->execute([$ids['book_id']]); $book=$s->fetch();
            $s=$db->prepare('SELECT * FROM book_copies WHERE copy_id=? FOR UPDATE'); $s->execute([$ids['copy_id']]); $copy=$s->fetch();
            $s=$db->prepare('SELECT * FROM borrow_requests WHERE request_id=? FOR UPDATE'); $s->execute([$requestId]); $request=$s->fetch();
            $s=$db->prepare('SELECT * FROM loans WHERE loan_id=? FOR UPDATE'); $s->execute([$ids['loan_id']]); $loan=$s->fetch();
            if(!$request || !$loan || !$copy || (int)$request['student_id']!==$student || (int)$request['book_id']!==(int)$ids['book_id'] || (int)$copy['book_id']!==(int)$request['book_id'] || (int)$loan['request_id']!==$requestId || (int)$loan['copy_id']!==(int)$ids['copy_id'] || $request['decision']!=='approved') { throw new InvalidArgumentException('The allocation changed. Refresh the list.'); }
            if($loan['cancelled_at']!==null || $loan['returned_at']!==null) { throw new InvalidArgumentException('This allocation is already closed.'); }
            if($loan['checked_out_at']!==null) { $db->commit(); return 'already_collected'; }
            $now=(string)$db->query('SELECT UTC_TIMESTAMP()')->fetchColumn();
            if(pickup_deadline_expired($request['decided_at'],$now)) {
                $db->prepare("UPDATE loans SET cancelled_at=?,cancellation_kind='expired',cancelled_by=NULL WHERE loan_id=?")->execute([$now,$loan['loan_id']]);
                record_audit_event($db,null,'loan.expired','loan',(int)$loan['loan_id'],'Uncollected pickup allocation expired.');
                $db->commit(); return 'expired';
            }
            if(!$users[$student] || $users[$student]['role']!=='student' || $users[$student]['account_status']!=='active') { throw new InvalidArgumentException('The borrower must be an active student.'); }
            if(!$book || $book['archived_at']!==null || $copy['archived_at']!==null) { throw new InvalidArgumentException('The allocated book or copy is archived.'); }
            $s=$db->prepare('SELECT l.loan_id,l.checked_out_at,l.due_date,r.book_id FROM loans l JOIN borrow_requests r ON r.request_id=l.request_id WHERE r.student_id=? AND l.returned_at IS NULL AND l.cancelled_at IS NULL AND (l.checked_out_at IS NOT NULL OR r.decided_at>DATE_SUB(?,INTERVAL 48 HOUR))'); $s->execute([$student,$now]); $live=$s->fetchAll();
            $today=(new DateTimeImmutable($now,new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Manila'))->format('Y-m-d');
            foreach($live as $other) {
                if($other['checked_out_at']!==null && $other['due_date']<$today) { throw new InvalidArgumentException('The student has an overdue loan.'); }
                if((int)$other['loan_id']!==(int)$loan['loan_id'] && (int)$other['book_id']===(int)$request['book_id']) { throw new InvalidArgumentException('The student already has another allocation of this title.'); }
            }
            if(count($live)>5) { throw new InvalidArgumentException('The student exceeds the five-book allowance.'); }
            $s=$db->prepare("SELECT request_id FROM borrow_requests WHERE student_id=? AND book_id=? AND decision='pending'"); $s->execute([$student,$request['book_id']]);
            if($s->fetch()) { throw new InvalidArgumentException('Resolve the student’s other pending request for this title first.'); }
            $due=pickup_due_date($now,(int)$request['approved_days']);
            $db->prepare('UPDATE loans SET checked_out_at=?,checked_out_by=?,due_date=? WHERE loan_id=?')->execute([$now,$actor,$due,$loan['loan_id']]);
            record_audit_event($db,$actor,'loan.collected','loan',(int)$loan['loan_id'],'Physical book pickup recorded.');
            $db->commit(); return 'collected';
        } catch(Throwable $e) {
            if($db->inTransaction()) { $db->rollBack(); }
            if($e instanceof PDOException && in_array((int)($e->errorInfo[1]??0),[1205,1213],true)) { if($attempt<2) { usleep(random_int(10000,50000)); continue; } throw new DomainException('The pickup is busy. Refresh and try again.'); }
            throw $e;
        }
    }
}

function list_pickups(PDO $db,int $page,string $search=''): array
{
    require_request_librarian(current_user($db));
    if($page<1 || $page>10000 || mb_strlen($search)>100) { throw new InvalidArgumentException('Choose a valid page and search of at most 100 characters.'); }
    $sql="SELECT r.*,l.loan_id,l.copy_id,l.checked_out_at,l.checked_out_by,l.due_date,u.username,u.display_name AS student_name,u.account_status,b.title,c.copy_code,DATE_ADD(r.decided_at,INTERVAL 48 HOUR) AS pickup_deadline,(r.decided_at<=UTC_TIMESTAMP()-INTERVAL 48 HOUR) AS expired FROM borrow_requests r JOIN loans l ON l.request_id=r.request_id JOIN users u ON u.user_id=r.student_id JOIN books b ON b.book_id=r.book_id JOIN book_copies c ON c.copy_id=l.copy_id WHERE r.decision='approved' AND l.checked_out_at IS NULL AND l.returned_at IS NULL AND l.cancelled_at IS NULL";
    $args=[];
    if(trim($search)!=='') { $sql.=' AND (LOCATE(?,b.title)>0 OR LOCATE(?,u.username)>0 OR LOCATE(?,u.display_name)>0)'; $args=array_fill(0,3,trim($search)); }
    $s=$db->prepare($sql.' ORDER BY r.decided_at,r.request_id LIMIT 21 OFFSET '.(($page-1)*20)); $s->execute($args); return $s->fetchAll();
}
