<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

function normalize_username(mixed $username): string
{
    $normalized = strtolower(trim((string) $username));

    if (!preg_match('/^[a-z0-9_]{3,32}$/', $normalized)) {
        throw new InvalidArgumentException('Username must use 3-32 lowercase letters, numbers, or underscores.');
    }

    return $normalized;
}

function validate_new_password(mixed $password): string
{
    $password = (string) $password;
    $length = strlen($password);

    if ($length < 12 || $length > 72) {
        throw new InvalidArgumentException('Password must contain 12 to 72 characters.');
    }

    return $password;
}

function register_student(PDO $connection, string $username, string $displayName, string $password): int
{
    $username = normalize_username($username);
    $displayName = required_text($displayName, 1, 100, 'Display name');
    $passwordHash = password_hash(validate_new_password($password), PASSWORD_DEFAULT);

    $connection->beginTransaction();

    try {
        $statement = $connection->prepare(
            'INSERT INTO users (username, display_name, password_hash, role, account_status) '
            . "VALUES (:username, :display_name, :password_hash, 'student', 'pending')"
        );
        $statement->execute([
            'username' => $username,
            'display_name' => $displayName,
            'password_hash' => $passwordHash,
        ]);
        $userId = (int) $connection->lastInsertId();

        record_audit_event($connection, null, 'account.registered', 'users', $userId, 'Student registration created.');
        $connection->commit();

        return $userId;
    } catch (PDOException $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }

        if ($exception->getCode() === '23000') {
            throw new InvalidArgumentException('That username is already in use.');
        }

        throw $exception;
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }

        throw $exception;
    }
}

function set_user_password(
    PDO $connection,
    mixed $usernameInput,
    mixed $passwordInput,
    ?int $actorUserId = null
): void {
    $username = normalize_username($usernameInput);
    $password = validate_new_password($passwordInput);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $connection->beginTransaction();

    try {
        $statement = $connection->prepare(
            'SELECT user_id, username, account_status FROM users WHERE username = :username LIMIT 1 FOR UPDATE'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        if (!is_array($user)) {
            throw new InvalidArgumentException("User '{$username}' does not exist.");
        }

        $userId = (int) $user['user_id'];

        $update = $connection->prepare(
            'UPDATE users SET password_hash = :password_hash, session_version = session_version + 1, updated_at = UTC_TIMESTAMP() WHERE user_id = :user_id'
        );
        $update->execute([
            'password_hash' => $passwordHash,
            'user_id' => $userId,
        ]);

        $connection->prepare('DELETE FROM login_failures WHERE username_key = :username')
            ->execute(['username' => $username]);

        record_audit_event(
            $connection,
            $actorUserId ?? $userId,
            'account.password_set',
            'users',
            $userId,
            "Password set for {$username} via local bootstrap."
        );

        $connection->commit();
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }

        throw $exception;
    }
}

function authenticate_user(PDO $connection, mixed $usernameInput, mixed $password): ?array
{
    try {
        $username = normalize_username($usernameInput);
    } catch (InvalidArgumentException) {
        return null;
    }

    $connection->beginTransaction();

    try {
        $connection->prepare(
            'INSERT INTO login_throttle (username_key) VALUES (:username) '
            . 'ON DUPLICATE KEY UPDATE username_key = VALUES(username_key)'
        )->execute(['username' => $username]);

        $connection->prepare(
            'SELECT username_key FROM login_throttle WHERE username_key = :username FOR UPDATE'
        )->execute(['username' => $username]);

        $cutoff = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('-15 minutes')->format('Y-m-d H:i:s');
        $connection->prepare('DELETE FROM login_failures WHERE username_key = :username AND attempted_at < :cutoff')
            ->execute(['username' => $username, 'cutoff' => $cutoff]);

        $failureStatement = $connection->prepare(
            'SELECT attempt_id FROM login_failures WHERE username_key = :username FOR UPDATE'
        );
        $failureStatement->execute(['username' => $username]);
        $failureCount = count($failureStatement->fetchAll());

        if ($failureCount >= 5) {
            $connection->commit();

            return null;
        }

        $statement = $connection->prepare(
            'SELECT user_id, username, display_name, password_hash, role, account_status, session_version '
            . 'FROM users WHERE username = :username LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        $isAllowed = is_array($user)
            && is_string($user['password_hash'])
            && password_verify((string) $password, $user['password_hash'])
            && in_array($user['account_status'], ['pending', 'active'], true);

        if (!$isAllowed) {
            $connection->prepare('INSERT INTO login_failures (username_key) VALUES (:username)')
                ->execute(['username' => $username]);
            $connection->commit();

            return null;
        }

        $connection->prepare('DELETE FROM login_failures WHERE username_key = :username')
            ->execute(['username' => $username]);
        $connection->commit();

        return $user;
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }

        throw $exception;
    }
}

function record_audit_event(
    PDO $connection,
    ?int $actorUserId,
    string $eventCode,
    string $entityType,
    ?int $entityId,
    string $summary
): void {
    $connection->prepare(
        'INSERT INTO audit_events (actor_user_id, event_code, entity_type, entity_id, event_summary) '
        . 'VALUES (:actor_user_id, :event_code, :entity_type, :entity_id, :event_summary)'
    )->execute([
        'actor_user_id' => $actorUserId,
        'event_code' => $eventCode,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'event_summary' => $summary,
    ]);
}

function establish_login_session(array $user): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new LogicException('A session is required before signing in.');
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['user_id'];
    $_SESSION['session_version'] = (int) $user['session_version'];
}

function current_user(PDO $connection): ?array
{
    $userId = $_SESSION['user_id'] ?? null;
    $sessionVersion = $_SESSION['session_version'] ?? null;

    if (!is_int($userId) || !is_int($sessionVersion)) {
        return null;
    }

    $statement = $connection->prepare(
        'SELECT user_id, username, display_name, role, account_status, session_version '
        . 'FROM users WHERE user_id = :user_id AND session_version = :session_version LIMIT 1'
    );
    $statement->execute(['user_id' => $userId, 'session_version' => $sessionVersion]);
    $user = $statement->fetch();

    if (!is_array($user) || in_array($user['account_status'], ['suspended', 'archived'], true)) {
        clear_login_session();

        return null;
    }

    return $user;
}

function clear_login_session(): void
{
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
