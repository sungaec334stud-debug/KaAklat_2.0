<?php

declare(strict_types=1);

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function required_text(mixed $value, int $minimumLength, int $maximumLength, string $label): string
{
    $text = trim((string) $value);
    $length = mb_strlen($text, 'UTF-8');

    if ($length < $minimumLength || $length > $maximumLength) {
        throw new InvalidArgumentException("{$label} must contain {$minimumLength} to {$maximumLength} characters.");
    }

    return $text;
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new LogicException('A session is required before creating a CSRF token.');
    }

    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_is_valid(mixed $submittedToken): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE || !is_string($submittedToken)) {
        return false;
    }

    $storedToken = $_SESSION['csrf_token'] ?? null;

    return is_string($storedToken) && hash_equals($storedToken, $submittedToken);
}

function flash_set(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new LogicException('A session is required before storing a flash message.');
    }

    if (!in_array($type, ['success', 'error', 'info'], true)) {
        throw new InvalidArgumentException('Unknown flash message type.');
    }

    $_SESSION['flash'][$type] = $message;
}

function flash_pull(string $type): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new LogicException('A session is required before reading a flash message.');
    }

    $message = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);

    return is_string($message) ? $message : null;
}

function redirect(string $location): never
{
    header('Location: ' . $location, true, 303);
    exit;
}
