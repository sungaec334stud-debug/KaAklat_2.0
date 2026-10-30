<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
$pdo = database_connection(application_config()['database']);
$ddl = $pdo->query('SHOW CREATE TABLE users')->fetch(PDO::FETCH_NUM)[1];
$pdo->exec(str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$ddl));
$pdo->exec('CREATE TEMPORARY TABLE audit_events (actor_user_id INT NULL, event_code VARCHAR(64) UNIQUE, entity_type VARCHAR(32), entity_id INT, event_summary VARCHAR(255)) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE login_throttle (username_key VARCHAR(32) PRIMARY KEY) ENGINE=InnoDB');
$pdo->exec('CREATE TEMPORARY TABLE login_failures (attempt_id INT AUTO_INCREMENT PRIMARY KEY, username_key VARCHAR(32), attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
$password = bin2hex(random_bytes(16));
register_student($pdo,'fixture_new','Fictional learner',$password);
try { register_student($pdo,'fixture_new','Duplicate',$password); throw new RuntimeException('Duplicate accepted'); }
catch (InvalidArgumentException $e) { if ($e->getMessage() !== 'That username is already in use.') { throw $e; } }
try { register_student($pdo,'fixture_other','Audit failure',$password); throw new RuntimeException('Audit failure accepted'); }
catch (PDOException) {}
if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() !== 1) { throw new RuntimeException('Registration rollback failed'); }
if (authenticate_user($pdo,'unknown_fixture',$password) !== null) { throw new RuntimeException('Unknown authenticated'); }
$pdo->exec('UPDATE users SET password_hash=NULL');
if (authenticate_user($pdo,'fixture_new',$password) !== null) { throw new RuntimeException('Disabled authenticated'); }
echo "Authentication hardening: duplicate classification, unrelated audit failure, rollback, unknown and disabled accounts PASS.\n";
