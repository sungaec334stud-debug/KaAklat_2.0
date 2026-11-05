<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { exit(1); }
$file = dirname(__DIR__) . '/app/student_profile.php';
if (!is_file($file)) { fwrite(STDERR, "FAIL: student profile read model is not implemented.\n"); exit(1); }
ob_start();
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once $file;
require_once dirname(__DIR__) . '/views/student_profile.php';
start_application_session();
$checks = 0;
$db = database_connection(application_config()['database']);
profile_check($db->query('SELECT TIMEDIFF(NOW(),UTC_TIMESTAMP())')->fetchColumn() === '00:00:00', 'Application connections use UTC before timestamp defaults');
$schema = preg_replace('/^--.*$/m', '', (string)file_get_contents(dirname(__DIR__) . '/schema.sql'));
$schema = preg_replace('/SET SESSION sql_mode[^;]+;/', '', $schema);
$schema = preg_replace('/^\s*CONSTRAINT fk_.*\n/m', '', $schema);
$schema = preg_replace('/,\s*\)/', "\n)", $schema);
$schema = str_replace('CREATE TABLE ', 'CREATE TEMPORARY TABLE ', $schema);
foreach (explode(';', $schema) as $sql) { if (trim($sql) !== '') { $db->exec($sql); } }
$db->exec("SET time_zone = '+00:00'");
$db->exec('ALTER TABLE users ADD school_student_id CHAR(11) NULL');
$insert = $db->prepare('INSERT INTO users(user_id,username,display_name,role,account_status,created_at,password_hash) VALUES(?,?,?,?,?,?,?)');
foreach ([[1,'profile_one','<script>Fictional & Student</script>','student','active'], [2,'profile_two','Other private profile','student','active'],
    [3,'profile_pending','Fictional Pending','student','pending'], [4,'profile_staff','Fictional Staff','librarian','active'],
    [5,'profile_admin','Fictional Admin','administrator','active'], [6,'profile_suspended','Fictional Suspended','student','suspended'],
    [7,'profile_archived','Fictional Archived','student','archived']] as [$id,$name,$display,$role,$status]) {
    $insert->execute([$id,$name,$display,$role,$status,'2026-11-03 17:30:00','NEVER-SELECT-OR-DISPLAY-THIS-HASH']);
}
$db->exec("UPDATE users SET school_student_id='06-0001-001' WHERE user_id=1");
$db->exec("UPDATE users SET school_student_id='06-0001-002' WHERE user_id=2");
$db->exec("INSERT INTO books(book_id,title,description) VALUES(1,'Fictional Profile Title','Fictional profile counts')");
for ($id = 1; $id <= 5; $id++) {
    $db->exec("INSERT INTO book_copies(copy_id,book_id,copy_code) VALUES($id,1,'PROFILE-$id')");
    $db->exec("INSERT INTO borrow_requests(request_id,student_id,book_id,requested_days,request_reason,decision,approved_days,requested_at,decided_at,decided_by) VALUES($id,1,1,7,'Fictional profile count reason','approved',7,UTC_TIMESTAMP()-INTERVAL 20 DAY,UTC_TIMESTAMP(),4)");
    $db->exec("INSERT INTO loans(loan_id,request_id,copy_id) VALUES($id,$id,$id)");
}
$db->exec('UPDATE borrow_requests SET decided_at=UTC_TIMESTAMP()-INTERVAL 48 HOUR WHERE request_id=2');
$db->exec('UPDATE loans SET checked_out_at=UTC_TIMESTAMP()-INTERVAL 8 DAY,checked_out_by=4,due_date=DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)-INTERVAL 1 DAY WHERE loan_id=3');
$db->exec('UPDATE loans SET checked_out_at=UTC_TIMESTAMP(),checked_out_by=4,due_date=DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)+INTERVAL 7 DAY,returned_at=UTC_TIMESTAMP(),returned_by=4 WHERE loan_id=4');
$db->exec("UPDATE loans SET cancelled_at=UTC_TIMESTAMP(),cancellation_kind='expired' WHERE loan_id=5");
$db->exec("INSERT INTO borrow_requests(student_id,book_id,requested_days,request_reason) VALUES(1,1,7,'Fictional own pending request'),(2,1,7,'Fictional other pending request')");
function profile_check(bool $ok, string $label): void
{
    global $checks;
    if (!$ok) { throw new RuntimeException($label); }
    $checks++;
}
function profile_session(?int $id, int $version = 1): void
{
    $_SESSION = $id === null ? [] : ['user_id'=>$id,'session_version'=>$version,'last_activity'=>time(),'role'=>'administrator'];
}
function profile_denied(PDO $db, string $label): void
{
    try { student_profile($db); } catch (DomainException) { profile_check(true, $label); return; }
    throw new RuntimeException($label);
}
profile_session(1);
$profile = student_profile($db);
profile_check(array_keys($profile) === ['display_name','username','school_student_id','account_status','created_at','counts'], 'Only allowlisted profile fields returned');
profile_check($profile['counts'] === ['pending'=>1,'awaiting'=>1,'active'=>1,'overdue'=>1], 'Own counts exclude expired/closed and other student');
profile_check($profile['username'] === 'profile_one' && $profile['account_status'] === 'active', 'Fresh identity ignores session role hint');
$html = student_profile_html($profile);
profile_check(str_contains($html, '06-0001-001') && !str_contains($html, '06-0001-002'), 'Only own school ID is shown with leading zeros');
profile_check(str_contains($html, '&lt;script&gt;Fictional &amp; Student&lt;/script&gt;') && !str_contains($html, '<script>'), 'Profile values escaped');
profile_check(str_contains($html, '4 November 2026, 1:30 AM') && str_contains($html, '2026-11-04T01:30:00+08:00'), 'UTC registration rolls into correct Manila date');
foreach (['pending','awaiting_pickup','active'] as $filter) {
    profile_check(str_contains($html, 'href="/requests.php?status=' . $filter . '"'), 'Existing history tab link: ' . $filter);
}
foreach (['NEVER-SELECT-OR-DISPLAY-THIS-HASH','Other private profile','session_version','password_hash','token_hash'] as $secret) {
    profile_check(!str_contains($html, $secret), 'Sensitive or other-user value absent');
}
profile_session(3);
$pending = student_profile($db);
profile_check($pending['counts'] === ['pending'=>0,'awaiting'=>0,'active'=>0,'overdue'=>0], 'Pending student zero counts');
$html = student_profile_html($pending);
profile_check(str_contains($html, 'No school ID on file'), 'Legacy account with no school ID shows clear guidance');
profile_check(str_contains($html, 'Waiting for librarian activation') && str_contains($html, 'No current borrowing activity') && str_contains($html, 'href="/catalog.php"'), 'Pending explanation and useful empty state');
foreach ([null,4,5,6,7,99999] as $denied) { profile_session($denied); profile_denied($db, 'Guest/wrong role/inactive/missing account denied'); }
profile_session(1, 99);
profile_denied($db, 'Stale session version denied');
profile_session(1);
$_SESSION['last_activity'] = time() - 1800;
profile_denied($db, 'Idle session denied');
profile_session(1);
$db->exec("UPDATE users SET account_status='suspended' WHERE user_id=1");
profile_denied($db, 'Fresh suspension denies old profile session');
echo "PASS: $checks student profile checks (temporary fictional fixtures only).\n";
ob_end_flush();
