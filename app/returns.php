<?php
declare(strict_types=1);
require_once __DIR__.'/request_decisions.php';

function record_return(PDO $db, int $requestId, mixed $token): string
{
    if(!csrf_is_valid($token)) { throw new InvalidArgumentException('Your form expired. Refresh and try again.'); }
    if($requestId<1) { throw new InvalidArgumentException('Choose a valid return.'); }
    for($attempt=0;;$attempt++) {
        $db->exec("SET SESSION time_zone = '+00:00'");
        $db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED'); $db->beginTransaction();
        try {
            $actor=$_SESSION['user_id']??null;
            if(!is_int($actor)) { throw new DomainException('Please sign in again.'); }
            $s=$db->prepare('SELECT r.student_id,r.book_id,l.copy_id,l.loan_id FROM borrow_requests r JOIN loans l ON l.request_id=r.request_id WHERE r.request_id=?'); $s->execute([$requestId]); $ids=$s->fetch();
            if(!$ids) { throw new InvalidArgumentException('Return allocation not found.'); }
            $student=(int)$ids['student_id']; $users=[]; $userIds=array_unique([$actor,$student]); sort($userIds,SORT_NUMERIC);
            foreach($userIds as $id) { $s=$db->prepare('SELECT * FROM users WHERE user_id=? FOR UPDATE'); $s->execute([$id]); $users[$id]=$s->fetch(); }
            require_request_librarian($users[$actor]?:null);
            if((int)$users[$actor]['session_version']!==($_SESSION['session_version']??null) || !is_int($_SESSION['last_activity']??null) || time()-$_SESSION['last_activity']>=1800) { throw new DomainException('Please sign in again.'); }
            $s=$db->prepare('SELECT * FROM books WHERE book_id=? FOR UPDATE'); $s->execute([$ids['book_id']]); $book=$s->fetch();
            $s=$db->prepare('SELECT * FROM book_copies WHERE copy_id=? FOR UPDATE'); $s->execute([$ids['copy_id']]); $copy=$s->fetch();
            $s=$db->prepare('SELECT * FROM borrow_requests WHERE request_id=? FOR UPDATE'); $s->execute([$requestId]); $request=$s->fetch();
            $s=$db->prepare('SELECT * FROM loans WHERE loan_id=? FOR UPDATE'); $s->execute([$ids['loan_id']]); $loan=$s->fetch();
            if(!$book || !$request || !$loan || !$copy || (int)$request['student_id']!==$student || (int)$request['book_id']!==(int)$ids['book_id'] || (int)$copy['book_id']!==(int)$request['book_id'] || (int)$loan['request_id']!==$requestId || (int)$loan['copy_id']!==(int)$ids['copy_id'] || $request['decision']!=='approved') { throw new InvalidArgumentException('The allocation changed. Refresh the list.'); }
            if($loan['cancelled_at']!==null || $loan['checked_out_at']===null) { throw new InvalidArgumentException('Only collected loans can be returned.'); }
            if($loan['returned_at']!==null) { $db->commit(); return 'already_returned'; }
            $now=(string)$db->query('SELECT UTC_TIMESTAMP()')->fetchColumn();
            $db->prepare('UPDATE loans SET returned_at=?,returned_by=? WHERE loan_id=?')->execute([$now,$actor,$loan['loan_id']]);
            record_audit_event($db,$actor,'loan.returned','loan',(int)$loan['loan_id'],'Physical book return recorded.');
            $db->commit(); return 'returned';
        } catch(Throwable $e) {
            if($db->inTransaction()) { $db->rollBack(); }
            if($e instanceof PDOException && in_array((int)($e->errorInfo[1]??0),[1205,1213],true)) { if($attempt<2) { usleep(random_int(10000,50000)); continue; } throw new DomainException('The return is busy. Refresh and try again.'); }
            throw $e;
        }
    }
}

function list_returns(PDO $db,int $page,string $search='', ?array &$pagination = null): array
{
    require_request_librarian(current_user($db));
    if($page<1 || $page>10000 || mb_strlen($search)>100) { throw new InvalidArgumentException('Choose a valid page and search of at most 100 characters.'); }
    $sql="SELECT r.*,l.loan_id,l.copy_id,l.checked_out_at,l.checked_out_by,l.due_date,l.returned_at,l.returned_by,u.username,u.display_name AS student_name,u.account_status,b.title,c.copy_code,(l.due_date<DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)) AS overdue FROM borrow_requests r JOIN loans l ON l.request_id=r.request_id JOIN users u ON u.user_id=r.student_id JOIN books b ON b.book_id=r.book_id JOIN book_copies c ON c.copy_id=l.copy_id WHERE r.decision='approved' AND l.checked_out_at IS NOT NULL AND l.returned_at IS NULL AND l.cancelled_at IS NULL";
    $args=[];
    if(trim($search)!=='') { $sql.=' AND (LOCATE(?,b.title)>0 OR LOCATE(?,u.username)>0 OR LOCATE(?,u.display_name)>0 OR LOCATE(?,c.copy_code)>0)'; $args=array_fill(0,4,trim($search)); }
    return pagination_rows($db, $sql, $args, 'l.due_date,r.request_id', $page, 20, $pagination, 10000, func_num_args() >= 4);
}
