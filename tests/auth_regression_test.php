<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
start_application_session();
$pdo = database_connection(application_config()['database']);
$pdo->exec('CREATE TEMPORARY TABLE users (user_id INT PRIMARY KEY, username VARCHAR(32) UNIQUE, display_name VARCHAR(100), password_hash VARCHAR(255), role VARCHAR(20), account_status VARCHAR(20), session_version INT) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE login_throttle (username_key VARCHAR(32) PRIMARY KEY) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE login_failures (attempt_id INT AUTO_INCREMENT PRIMARY KEY, username_key VARCHAR(32), attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
$pdo->exec("SET time_zone='+00:00'");
$password = bin2hex(random_bytes(16));
$pdo->prepare("INSERT INTO users VALUES (1,'fixture_login','Fictional learner',?,'student','active',1)")->execute([password_hash($password, PASSWORD_DEFAULT)]);
function auth_reg_check(bool $ok, string $name): void { if (!$ok) { throw new RuntimeException($name); } }
auth_reg_check(authenticate_user($pdo,'fixture_login','wrong') === null, 'Wrong password must fail');
auth_reg_check(authenticate_user($pdo,'fixture_login',$password) !== null, 'Valid password must work');
auth_reg_check((int) $pdo->query('SELECT COUNT(*) FROM login_failures')->fetchColumn() === 1, 'Successful login must preserve rolling failure history');
for ($i=0;$i<4;$i++) { authenticate_user($pdo,'fixture_login','wrong'); }
auth_reg_check(authenticate_user($pdo,'fixture_login',$password) === null, 'Five failures must block correct password');
$pdo->exec('UPDATE login_failures SET attempted_at=UTC_TIMESTAMP()-INTERVAL 16 MINUTE');
auth_reg_check(authenticate_user($pdo,'fixture_login',$password) !== null, 'Expired failures must release cooldown');
$_SESSION=['user_id'=>1,'session_version'=>1,'last_activity'=>time()-1801];
auth_reg_check(current_user($pdo) === null, 'Idle session must expire');
$_SESSION=['user_id'=>1,'session_version'=>1,'last_activity'=>time()];
auth_reg_check(current_user($pdo) !== null, 'Fresh session must work');
$pdo->exec("UPDATE users SET account_status='suspended'");
auth_reg_check(current_user($pdo) === null, 'Suspension must revoke session');
auth_reg_check(authenticate_user($pdo, [], $password) === null, 'Malformed username must fail safely');
echo "Authentication regression tests passed (temporary fixtures only).\n";
