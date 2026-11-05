<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function require_active_librarian(?array $user): void
{
    if ($user === null || $user['role'] !== 'librarian' || $user['account_status'] !== 'active') {
        throw new DomainException('Only active librarians can manage student accounts.');
    }
}

function require_student_manager(?array $user): void
{
    if (!$user || !in_array($user['role'],['librarian','administrator'],true) || $user['account_status']!=='active') {
        throw new DomainException('Only active librarians and administrators can manage students.');
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

function change_student_status(
    PDO $connection,
    int $studentId,
    mixed $action,
    mixed $token,
    mixed $identityConfirmation = null,
    ?int $reviewVersion = null,
    mixed $replacementId = null
): void
{
    if (!csrf_is_valid($token)) {
        throw new InvalidArgumentException('Your form expired. Refresh and try again.');
    }
    if ($studentId < 1 || !is_string($action)) {
        throw new InvalidArgumentException('Choose a valid student and action.');
    }
    $actorId = $_SESSION['user_id'] ?? null;
    if (!is_int($actorId) || !is_int($_SESSION['last_activity']??null) || time()-$_SESSION['last_activity']>=1800) {
        throw new DomainException('Please sign in as an authorized staff member.');
    }
    $connection->beginTransaction();
    try {
        $ids = array_unique([$actorId, $studentId]);
        sort($ids, SORT_NUMERIC);
        $users = [];
        foreach ($ids as $id) {
            $statement = $connection->prepare('SELECT user_id, role, account_status, session_version, school_student_id FROM users WHERE user_id = :id FOR UPDATE');
            $statement->execute(['id' => $id]);
            $users[$id] = $statement->fetch() ?: null;
        }
        $actor = $users[$actorId];
        require_student_manager($actor);
        if ((int) $actor['session_version'] !== ($_SESSION['session_version'] ?? null)) {
            throw new DomainException('Your session expired. Please sign in again.');
        }
        $student = $users[$studentId];
        if ($student === null || $student['role'] !== 'student') {
            throw new InvalidArgumentException('Choose an existing student account.');
        }
        if (in_array($action, ['activate', 'correct_id', 'release_id'], true)) {
            require_active_librarian($actor);
            if ($student['account_status'] !== 'pending' || $reviewVersion !== (int) $student['session_version']) {
                throw new InvalidArgumentException('The pending account changed. Refresh and check the student ID again.');
            }
            if ($identityConfirmation !== 'on') {
                throw new InvalidArgumentException('Confirm that you checked the physical school ID in person.');
            }
        }

        if ($action === 'correct_id' || $action === 'release_id') {
            $newId = $action === 'release_id' ? null : validate_school_student_id($replacementId);
            if ($newId === $student['school_student_id']) {
                throw new InvalidArgumentException('The student ID claim is already in that state.');
            }
            try {
                $connection->prepare('UPDATE users SET school_student_id = :school_id, session_version = session_version + 1 WHERE user_id = :id')
                    ->execute(['school_id' => $newId, 'id' => $studentId]);
            } catch (PDOException $exception) {
                if (is_duplicate_user_key($exception, 'uq_users_school_student_id')) {
                    throw new InvalidArgumentException('That student ID is already claimed.');
                }
                throw $exception;
            }
            $summary = 'Pending student ID claim ' . ($newId === null ? 'released' : 'corrected') . ' after an in-person check.';
        } else {
            $next = student_status_transition($student['account_status'], $action);
            if ($action === 'activate' && $student['school_student_id'] === null) {
                throw new InvalidArgumentException('Assign the verified school student ID before activation.');
            }
            $connection->prepare('UPDATE users SET account_status = :status, session_version = session_version + 1 WHERE user_id = :id')
                ->execute(['status' => $next, 'id' => $studentId]);
            $summary = $action === 'activate' ? 'Student activated after an in-person school ID check.'
                : 'Student status changed from ' . $student['account_status'] . ' to ' . $next . '.';
        }
        record_audit_event($connection, $actorId, 'student.' . $action, 'users', $studentId, $summary);
        $connection->commit();
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $exception;
    }
}
