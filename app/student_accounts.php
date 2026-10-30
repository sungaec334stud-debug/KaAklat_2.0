<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function require_active_librarian(?array $user): void
{
    if ($user === null || $user['role'] !== 'librarian' || $user['account_status'] !== 'active') {
        throw new DomainException('Only active librarians can manage student accounts.');
    }
}

function student_status_transition(string $status, string $action): string
{
    return match ($status . ':' . $action) {
        'pending:activate', 'suspended:reactivate' => 'active',
        'active:suspend' => 'suspended',
        default => throw new InvalidArgumentException('This action is no longer available. Refresh and check the account status.'),
    };
}

function change_student_status(PDO $connection, int $studentId, mixed $action, mixed $token): void
{
    if (!csrf_is_valid($token)) {
        throw new InvalidArgumentException('Your form expired. Refresh and try again.');
    }
    if ($studentId < 1 || !is_string($action)) {
        throw new InvalidArgumentException('Choose a valid student and action.');
    }
    $actorId = $_SESSION['user_id'] ?? null;
    if (!is_int($actorId)) {
        throw new DomainException('Please sign in as a librarian.');
    }
    $connection->beginTransaction();
    try {
        $ids = array_unique([$actorId, $studentId]);
        sort($ids, SORT_NUMERIC);
        $users = [];
        foreach ($ids as $id) {
            $statement = $connection->prepare('SELECT user_id, role, account_status, session_version FROM users WHERE user_id = :id FOR UPDATE');
            $statement->execute(['id' => $id]);
            $users[$id] = $statement->fetch() ?: null;
        }
        $actor = $users[$actorId];
        require_active_librarian($actor);
        if ((int) $actor['session_version'] !== ($_SESSION['session_version'] ?? null)) {
            throw new DomainException('Your session expired. Please sign in again.');
        }
        $student = $users[$studentId];
        if ($student === null || $student['role'] !== 'student') {
            throw new InvalidArgumentException('Choose an existing student account.');
        }
        $next = student_status_transition($student['account_status'], $action);
        $connection->prepare('UPDATE users SET account_status = :status, session_version = session_version + 1 WHERE user_id = :id')
            ->execute(['status' => $next, 'id' => $studentId]);
        record_audit_event($connection, $actorId, 'student.' . $action, 'users', $studentId,
            'Student status changed from ' . $student['account_status'] . ' to ' . $next . '.');
        $connection->commit();
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $exception;
    }
}
