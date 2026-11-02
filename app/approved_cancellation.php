<?php
declare(strict_types=1);
require_once __DIR__.'/pickups.php';

function cancel_approved_request(PDO $db, int $requestId, mixed $token, mixed $note = null): string
{
    if(!csrf_is_valid($token)) { throw new InvalidArgumentException('Your form expired. Refresh and try again.'); }
    if($requestId<1) { throw new InvalidArgumentException('Choose a valid allocation.'); }
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
            $user=$users[$actor]?:null;
            if (!$user || !in_array($user['role'],['student','librarian'],true) || !in_array($user['account_status'], $user['role']==='student'?['pending','active']:['active'],true)) { throw new DomainException('Cancellation is not permitted for this account.'); }
            if ($user['role']==='student' && $actor!==$student) { throw new DomainException('You can cancel only your own allocation.'); }
            $kind=$user['role']==='student'?'student':'librarian';
            $explanation=null;
            if ($kind==='librarian') { if(!is_string($note) || mb_strlen(trim($note))<1 || mb_strlen(trim($note))>500) { throw new InvalidArgumentException('Enter a cancellation explanation of 1–500 characters.'); } $explanation=trim($note); }
            elseif($note!==null) { throw new InvalidArgumentException('Unexpected cancellation explanation.'); }
            if((int)$users[$actor]['session_version']!==($_SESSION['session_version']??null) || !is_int($_SESSION['last_activity']??null) || time()-$_SESSION['last_activity']>=1800) { throw new DomainException('Please sign in again.'); }
            $s=$db->prepare('SELECT * FROM books WHERE book_id=? FOR UPDATE'); $s->execute([$ids['book_id']]); $book=$s->fetch();
            $s=$db->prepare('SELECT * FROM book_copies WHERE copy_id=? FOR UPDATE'); $s->execute([$ids['copy_id']]); $copy=$s->fetch();
            $s=$db->prepare('SELECT * FROM borrow_requests WHERE request_id=? FOR UPDATE'); $s->execute([$requestId]); $request=$s->fetch();
            $s=$db->prepare('SELECT * FROM loans WHERE loan_id=? FOR UPDATE'); $s->execute([$ids['loan_id']]); $loan=$s->fetch();
            if(!$book || !$request || !$loan || !$copy || (int)$request['student_id']!==$student || (int)$request['book_id']!==(int)$ids['book_id'] || (int)$copy['book_id']!==(int)$request['book_id'] || (int)$loan['request_id']!==$requestId || (int)$loan['copy_id']!==(int)$ids['copy_id'] || $request['decision']!=='approved') { throw new InvalidArgumentException('The allocation changed. Refresh the list.'); }
            if($loan['cancelled_at']!==null) { $db->commit(); return $loan['cancellation_kind']==='expired'?'expired':'already_cancelled'; }
            if($loan['checked_out_at']!==null || $loan['returned_at']!==null) { throw new InvalidArgumentException('Pickup has already occurred. This allocation cannot be cancelled.'); }
            $now=(string)$db->query('SELECT UTC_TIMESTAMP()')->fetchColumn();
            if(pickup_deadline_expired($request['decided_at'],$now)) {
                $db->prepare("UPDATE loans SET cancelled_at=?,cancellation_kind='expired',cancelled_by=NULL WHERE loan_id=?")->execute([$now,$loan['loan_id']]);
                record_audit_event($db,null,'loan.expired','loan',(int)$loan['loan_id'],'Uncollected pickup allocation expired.');
                $db->commit(); return 'expired';
            }
            $db->prepare('UPDATE loans SET cancelled_at=?,cancellation_kind=?,cancelled_by=?,cancellation_note=? WHERE loan_id=?')->execute([$now,$kind,$actor,$explanation,$loan['loan_id']]);
            record_audit_event($db,$actor,'loan.cancelled','loan',(int)$loan['loan_id'],'Uncollected approved allocation cancelled.');
            $db->commit(); return 'cancelled';
        } catch(Throwable $e) {
            if($db->inTransaction()) { $db->rollBack(); }
            if($e instanceof PDOException && in_array((int)($e->errorInfo[1]??0),[1205,1213],true)) { if($attempt<2) { usleep(random_int(10000,50000)); continue; } throw new DomainException('The allocation is busy. Refresh and try again.'); }
            throw $e;
        }
    }
}

