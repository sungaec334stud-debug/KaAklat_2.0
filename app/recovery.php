<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';

function require_recovery_staff(?array $user): void
{
    if (!$user || !in_array($user['role'],['librarian','administrator'],true) || $user['account_status']!=='active') {
        throw new DomainException('Only active librarians and administrators can issue recovery secrets.');
    }
}

function issue_recovery(PDO $db,mixed $username,mixed $token,bool $identityConfirmed): string
{
    if (!csrf_is_valid($token)) { throw new InvalidArgumentException('Your form expired. Refresh and try again.'); }
    if (!$identityConfirmed) { throw new InvalidArgumentException('Confirm the account holder’s identity in person first.'); }
    $username=normalize_username($username);
    $actor=$_SESSION['user_id']??null;
    if (!is_int($actor) || !is_int($_SESSION['last_activity']??null) || time()-$_SESSION['last_activity']>=1800) { throw new DomainException('Please sign in again.'); }
    $db->exec("SET SESSION time_zone='+00:00'"); $db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED'); $db->beginTransaction();
    try {
        $s=$db->prepare('SELECT user_id FROM users WHERE username=?'); $s->execute([$username]); $target=$s->fetchColumn();
        if ($target===false) { throw new InvalidArgumentException('Account not found. Check the username.'); }
        $target=(int)$target; $ids=array_unique([$actor,$target]); sort($ids,SORT_NUMERIC); $users=[];
        foreach ($ids as $id) { $s=$db->prepare('SELECT * FROM users WHERE user_id=? FOR UPDATE'); $s->execute([$id]); $users[$id]=$s->fetch(); }
        $staff=$users[$actor]?:null; require_recovery_staff($staff);
        if ((int)$staff['session_version']!==($_SESSION['session_version']??null)) { throw new DomainException('Please sign in again.'); }
        $user=$users[$target];
        if (!$user || $user['username']!==$username || $user['account_status']==='archived' || ($staff['role']==='librarian' && $user['role']!=='student')) { throw new DomainException('Recovery is not permitted for this account.'); }
        $raw=random_bytes(32); $secret=bin2hex($raw); $digest=hash('sha256',$raw,true);
        $db->prepare('INSERT INTO password_resets(user_id,token_hash,issued_by,issued_at,expires_at) VALUES(?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()+INTERVAL 30 MINUTE) ON DUPLICATE KEY UPDATE token_hash=VALUES(token_hash),issued_by=VALUES(issued_by),issued_at=VALUES(issued_at),expires_at=VALUES(expires_at)')->execute([$target,$digest,$actor]);
        record_audit_event($db,$actor,'account.reset_issued','users',$target,'In-person recovery secret issued.');
        $db->commit(); return $secret;
    } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }
}

function redeem_recovery(PDO $db,mixed $secret,mixed $password,mixed $token): void
{
    if (!csrf_is_valid($token)) { throw new InvalidArgumentException('Your form expired. Refresh and try again.'); }
    if (!is_string($secret) || !preg_match('/^[a-f0-9]{64}$/D',$secret)) { throw new InvalidArgumentException('This recovery secret is invalid or expired. Ask staff for a new one.'); }
    $newHash=password_hash(validate_new_password($password),PASSWORD_DEFAULT);
    $digest=hash('sha256',hex2bin($secret),true);
    $db->exec("SET SESSION time_zone='+00:00'"); $db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED'); $db->beginTransaction();
    try {
        $s=$db->prepare('SELECT user_id FROM password_resets WHERE token_hash=?'); $s->execute([$digest]); $id=$s->fetchColumn();
        if ($id===false) { throw new InvalidArgumentException('This recovery secret is invalid or expired. Ask staff for a new one.'); }
        $s=$db->prepare('SELECT * FROM users WHERE user_id=? FOR UPDATE'); $s->execute([$id]); $user=$s->fetch();
        $s=$db->prepare('SELECT *,expires_at<=UTC_TIMESTAMP() AS expired FROM password_resets WHERE user_id=? FOR UPDATE'); $s->execute([$id]); $reset=$s->fetch();
        if (!$user || $user['account_status']==='archived' || !$reset || (bool)$reset['expired'] || !hash_equals($reset['token_hash'],$digest)) { throw new InvalidArgumentException('This recovery secret is invalid or expired. Ask staff for a new one.'); }
        $db->prepare('UPDATE users SET password_hash=?,session_version=session_version+1 WHERE user_id=?')->execute([$newHash,$id]);
        $db->prepare('DELETE FROM password_resets WHERE user_id=?')->execute([$id]);
        record_audit_event($db,(int)$id,'account.reset_redeemed','users',(int)$id,'Recovery password changed; prior sessions revoked.');
        $db->commit(); clear_login_session();
    } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); throw $e; }
}
