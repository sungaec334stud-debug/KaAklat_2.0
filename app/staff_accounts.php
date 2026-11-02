<?php

declare(strict_types=1);
require_once __DIR__.'/auth.php';

function require_staff_administrator(?array $user): void
{
    if (!$user || $user['role']!=='administrator' || $user['account_status']!=='active') {
        throw new DomainException('Only active administrators can manage staff.');
    }
}

function staff_actor(PDO $db): array
{
    if (!is_int($_SESSION['last_activity']??null) || time()-$_SESSION['last_activity']>=1800) { throw new DomainException('Please sign in again.'); }
    $user=current_user($db);
    require_staff_administrator($user);
    return $user;
}

// Acquire before any row locks, including bootstrap on an empty users table.
function staff_transaction(PDO $db, callable $operation): mixed
{
    $name='kaaklat_staff_'.substr(hash('sha256',(string)$db->query('SELECT DATABASE()')->fetchColumn()),0,40);
    $s=$db->prepare('SELECT GET_LOCK(?,10)'); $s->execute([$name]);
    if ((int)$s->fetchColumn()!==1) { throw new DomainException('Staff accounts are busy. Refresh and try again.'); }
    try {
        $db->exec("SET SESSION time_zone = '+00:00'");
        $db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $db->beginTransaction();
        // Small-demo simplification: lock all users in PK order, consistent with circulation.
        $users=$db->query('SELECT * FROM users ORDER BY user_id FOR UPDATE')->fetchAll();
        $result=$operation($users);
        $db->commit();
        return $result;
    } catch (Throwable $e) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw $e;
    } finally {
        $db->prepare('SELECT RELEASE_LOCK(?)')->execute([$name]);
    }
}

function staff_terms(mixed $username,mixed $display,mixed $role): array
{
    if (!is_string($display) || !is_string($role) || !in_array($role,['librarian','administrator'],true)) {
        throw new InvalidArgumentException('Choose a staff role and valid display name.');
    }
    return [normalize_username($username),required_text($display,1,100,'Display name'),$role];
}

function staff_insert(PDO $db,array $users,string $username,string $display,string $role,?string $hash,?int $actor): int
{
    foreach ($users as $user) {
        if ($user['username']===$username) { throw new InvalidArgumentException('That username is already in use.'); }
    }
    try {
        $db->prepare("INSERT INTO users(username,display_name,role,account_status,password_hash) VALUES(?,?,?,'active',?)")->execute([$username,$display,$role,$hash]);
    } catch (PDOException $e) {
        if ((int)($e->errorInfo[1]??0)===1062 && str_contains((string)($e->errorInfo[2]??''),'uq_users_username')) {
            throw new InvalidArgumentException('That username is already in use.');
        }
        throw $e;
    }
    $id=(int)$db->lastInsertId();
    record_audit_event($db,$actor,'staff.created','users',$id,'Staff account provisioned.');
    return $id;
}

function staff_create(PDO $db,mixed $username,mixed $display,mixed $role,mixed $token): int
{
    if (!csrf_is_valid($token)) { throw new InvalidArgumentException('Your form expired. Refresh and try again.'); }
    [$username,$display,$role]=staff_terms($username,$display,$role);
    return staff_transaction($db,function(array $users) use($db,$username,$display,$role): int {
        $actor=staff_actor($db);
        return staff_insert($db,$users,$username,$display,$role,null,(int)$actor['user_id']);
    });
}

function staff_update(PDO $db,int $target,mixed $role,mixed $status,int $expectedVersion,mixed $token): string
{
    if (!csrf_is_valid($token)) { throw new InvalidArgumentException('Your form expired. Refresh and try again.'); }
    if ($target<1 || $expectedVersion<1 || !in_array($role,['librarian','administrator'],true) || !in_array($status,['active','suspended'],true)) {
        throw new InvalidArgumentException('Choose a valid staff account, role and status.');
    }
    $result=staff_transaction($db,function(array $users) use($db,$target,$role,$status,$expectedVersion): string {
        $actor=staff_actor($db);
        $rows=array_column($users,null,'user_id'); $user=$rows[$target]??null;
        if (!$user || !in_array($user['role'],['librarian','administrator'],true) || !in_array($user['account_status'],['active','suspended'],true)) {
            throw new InvalidArgumentException('Choose an existing, nonarchived staff account.');
        }
        if ($user['role']===$role && $user['account_status']===$status) { return 'unchanged'; }
        if ((int)$user['session_version']!==$expectedVersion) { throw new InvalidArgumentException('This account changed. Refresh before submitting again.'); }
        $usable=0;
        foreach ($users as $row) {
            $nextRole=(int)$row['user_id']===$target?$role:$row['role'];
            $nextStatus=(int)$row['user_id']===$target?$status:$row['account_status'];
            if ($nextRole==='administrator' && $nextStatus==='active' && is_string($row['password_hash']) && (password_get_info($row['password_hash'])['algo']??null)!==null) { $usable++; }
        }
        if ($usable<1) { throw new InvalidArgumentException('Keep at least one active administrator with a password set.'); }
        $db->prepare('UPDATE users SET role=?,account_status=?,session_version=session_version+1 WHERE user_id=?')->execute([$role,$status,$target]);
        $db->prepare('DELETE FROM password_resets WHERE user_id=?')->execute([$target]);
        record_audit_event($db,(int)$actor['user_id'],'staff.updated','users',$target,'Staff role or access status changed.');
        return 'changed';
    });
    if ($result==='changed' && $target===($_SESSION['user_id']??null)) { $_SESSION=[]; session_regenerate_id(true); }
    return $result;
}

function staff_list(PDO $db,int $page,string $search): array
{
    staff_actor($db);
    if ($page<1 || $page>10000 || mb_strlen($search)>100) { throw new InvalidArgumentException('Choose a valid page and search of at most 100 characters.'); }
    $s=$db->prepare("SELECT user_id,username,display_name,role,account_status,session_version,(password_hash IS NOT NULL) AS password_set FROM users WHERE role IN ('librarian','administrator') AND (LOCATE(?,username)>0 OR LOCATE(?,display_name)>0) ORDER BY user_id LIMIT 21 OFFSET ".(($page-1)*20));
    $s->execute([$search,$search]); return $s->fetchAll();
}

function bootstrap_administrator(PDO $db,mixed $username,mixed $display,mixed $password): int
{
    if (PHP_SAPI!=='cli') { throw new DomainException('Local operator only.'); }
    [$username,$display,$role]=staff_terms($username,$display,'administrator');
    $hash=password_hash(validate_new_password($password),PASSWORD_DEFAULT);
    return staff_transaction($db,function(array $users) use($db,$username,$display,$role,$hash): int {
        foreach ($users as $user) {
            if ($user['role']==='administrator' && $user['account_status']==='active' && is_string($user['password_hash']) && (password_get_info($user['password_hash'])['algo']??null)!==null) {
                throw new InvalidArgumentException('A usable administrator already exists. Bootstrap is unavailable.');
            }
        }
        return staff_insert($db,$users,$username,$display,$role,$hash,null);
    });
}
