<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/student_accounts.php';
start_application_session();
$pdo = database_connection(application_config()['database']);
// Temporary tables shadow real names on this connection only and disappear on disconnect.
$definition = $pdo->query('SHOW CREATE TABLE users')->fetch(PDO::FETCH_NUM)[1];
$pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $definition));
$pdo->exec('CREATE TEMPORARY TABLE audit_events (audit_id INT AUTO_INCREMENT PRIMARY KEY, actor_user_id BIGINT, event_code VARCHAR(64) UNIQUE, entity_type VARCHAR(32), entity_id BIGINT, event_summary VARCHAR(255)) ENGINE=InnoDB');
$pdo->exec("INSERT INTO users (user_id, username, display_name, role, account_status) VALUES
    (1, 'fixture_librarian', 'Fictional librarian', 'librarian', 'active'),
    (2, 'fixture_student', 'Fictional student', 'student', 'pending'),
    (3, 'fixture_admin', 'Fictional admin', 'administrator', 'active')");
$_SESSION = ['user_id' => 1, 'session_version' => 1, 'csrf_token' => str_repeat('a', 64)];
$token = $_SESSION['csrf_token'];
function expect_failure(callable $operation): void {
    try { $operation(); } catch (InvalidArgumentException|DomainException|PDOException) { return; }
    throw new RuntimeException('Expected rejection');
}
function assert_value(mixed $expected, mixed $actual): void {
    if ($expected !== $actual) { throw new RuntimeException('Unexpected persisted result'); }
}
expect_failure(fn () => change_student_status($pdo, 2, 'activate', 'forged'));
expect_failure(fn () => change_student_status($pdo, 3, 'suspend', $token));
expect_failure(fn () => change_student_status($pdo, 2, 'administrator', $token));
change_student_status($pdo, 2, 'activate', $token);
assert_value('active', $pdo->query('SELECT account_status FROM users WHERE user_id=2')->fetchColumn());
assert_value(2, (int) $pdo->query('SELECT session_version FROM users WHERE user_id=2')->fetchColumn());
expect_failure(fn () => change_student_status($pdo, 2, 'activate', $token));
change_student_status($pdo, 2, 'suspend', $token);
$_SESSION['user_id'] = 2;
$_SESSION['session_version'] = 2;
assert_value(null, current_user($pdo));
$_SESSION = ['user_id' => 1, 'session_version' => 1, 'csrf_token' => $token];
change_student_status($pdo, 2, 'reactivate', $token);
assert_value(3, (int) $pdo->query('SELECT COUNT(*) FROM audit_events')->fetchColumn());
$_SESSION['user_id'] = 3;
expect_failure(fn () => change_student_status($pdo, 2, 'suspend', $token));
$_SESSION['user_id'] = 2;
$_SESSION['session_version'] = 4;
expect_failure(fn () => change_student_status($pdo, 2, 'suspend', $token));
$_SESSION['user_id'] = 1;
$_SESSION['session_version'] = 0;
expect_failure(fn () => change_student_status($pdo, 2, 'suspend', $token));
$_SESSION['session_version'] = 1;
// Force audit failure in a disposable table, after the user update has executed.
// The fixture-only UNIQUE event_code rejects the second suspension audit.
// No ALTER, DROP or persistent-table mutation is used.
expect_failure(fn () => change_student_status($pdo, 2, 'suspend', $token));
assert_value('active', $pdo->query('SELECT account_status FROM users WHERE user_id=2')->fetchColumn());
assert_value(4, (int) $pdo->query('SELECT session_version FROM users WHERE user_id=2')->fetchColumn());
assert_value(3, (int) $pdo->query('SELECT COUNT(*) FROM audit_events')->fetchColumn());
echo "Student account integration tests passed (temporary tables only).\n";
