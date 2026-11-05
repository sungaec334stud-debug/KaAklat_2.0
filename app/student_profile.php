<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/dashboard.php';

function student_profile(PDO $db): array
{
    $user = current_user($db);
    if ($user === null || $user['role'] !== 'student' || !in_array($user['account_status'], ['pending','active'], true)) {
        throw new DomainException('My Profile is available only to student accounts.');
    }
    // Identity comes exclusively from the current, database-validated session.
    // Select only fields the signed-in owner may see.
    $statement = $db->prepare("SELECT display_name,username,school_student_id,account_status,created_at FROM users
        WHERE user_id=? AND session_version=? AND role='student' AND account_status IN ('pending','active')");
    $statement->execute([(int)$user['user_id'], (int)$user['session_version']]);
    $profile = $statement->fetch();
    if (!is_array($profile)) { throw new DomainException('Your account access changed. Please sign in again.'); }
    $profile['counts'] = dashboard_counts($db);
    return $profile;
}
