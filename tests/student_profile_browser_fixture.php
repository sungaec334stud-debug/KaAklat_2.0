<?php

// Operator-only fictional browser fixtures. Never deploy. Creates a NEW scratch DB; retains it.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
require_once dirname(__DIR__) . '/app/bootstrap.php';
$config = application_config()['database'];
$name = 'kaaklat_acceptance_' . bin2hex(random_bytes(6));
$db = database_connection($config);
$db->exec('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$config['database'] = $name;
$db = database_connection($config);
foreach ([dirname(__DIR__) . '/schema.sql', dirname(__DIR__) . '/migrations/001_book_categories_year.sql'] as $file) {
    $sql = preg_replace('/^--.*$/m', '', (string)file_get_contents($file));
    foreach (explode(';', $sql) as $statement) { if (trim($statement) !== '') { $db->exec($statement); } }
}
$password = bin2hex(random_bytes(18));
$hash = password_hash($password, PASSWORD_DEFAULT);
$s = $db->prepare('INSERT INTO users(user_id,username,display_name,password_hash,role,account_status,created_at) VALUES(?,?,?,?,?,?,?)');
foreach ([[1,'profile_browser_active','Fictional Student With A Long Display Name For Narrow Screen Wrapping Review','student','active'],
    [2,'profile_browser_pending','Fictional Pending Student','student','pending'],
    [3,'profile_browser_lib','Fictional Librarian','librarian','active']] as [$id,$username,$display,$role,$status]) {
    $s->execute([$id,$username,$display,$hash,$role,$status,'2026-11-03 17:30:00']);
}
$root = dirname(__DIR__) . '/tmp/student-profile-browser-' . substr($name, -12);
mkdir($root, 0700);
file_put_contents($root . '/fixture.json', json_encode(['database'=>$name,'password'=>$password,'active'=>'profile_browser_active','pending'=>'profile_browser_pending','directory'=>$root], JSON_PRETTY_PRINT));
echo "New fictional browser database: $name\nPrivate fixture file: $root/fixture.json\n";
