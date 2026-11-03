<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { exit(1); }
$implementation = dirname(__DIR__) . '/bin/backup_support.php';
if (!is_file($implementation)) {
    fwrite(STDERR, "FAIL: safe local backup helper is not implemented.\n");
    exit(1);
}
require $implementation;

$checks = 0;
function backup_test(bool $ok, string $label): void
{
    global $checks;
    if (!$ok) { throw new RuntimeException($label); }
    $checks++;
}
function backup_refused(callable $operation, string $label): void
{
    try { $operation(); } catch (RuntimeException | InvalidArgumentException) { backup_test(true, $label); return; }
    throw new RuntimeException($label);
}

$root = dirname(__DIR__) . '/tmp/backup-unit-' . bin2hex(random_bytes(6));
mkdir($root . '/private/covers', 0700, true);
mkdir($root . '/public', 0700, true);
file_put_contents($root . '/private/config.local.php', '<?php return [];');
file_put_contents($root . '/private/covers/fixture.jpg', 'fictional fixture bytes');
$db = ['host'=>'127.0.0.1', 'port'=>3306, 'database'=>'fictional_backup', 'username'=>'fictional', 'password'=>'never-print-this'];
backup_refused(fn()=>create_local_backup($db, $root, '../public/leak', 'missing-tool'), 'Traversal refused');
backup_test(!file_exists($root . '/public/leak'), 'No public output created');
mkdir($root . '/private/backups/existing', 0700, true);
file_put_contents($root . '/private/backups/existing/sentinel.txt', 'preserve');
backup_refused(fn()=>create_local_backup($db, $root, 'existing', 'missing-tool'), 'Existing destination refused');
backup_test(file_get_contents($root . '/private/backups/existing/sentinel.txt') === 'preserve', 'Existing file unchanged');
backup_refused(fn()=>create_local_backup($db, $root, 'failed-export', 'C:/not-a-real-tool/mysqldump.exe'), 'Missing export tool fails');
backup_test(!file_exists($root . '/private/backups/failed-export/manifest.json'), 'Failed backup never complete');
backup_test(glob($root . '/private/backups/failed-export/client-*.cnf') === [], 'Ephemeral credential file removed');
backup_refused(fn()=>run_private_database_tool(PHP_BINARY, $db, $root . '/private/backups/failed-export', []), 'Nonzero client exit fails');
backup_test(glob($root . '/private/backups/failed-export/client-*.cnf') === [], 'Credentials removed after failed process');
backup_test(!str_contains((string)file_get_contents($root . '/private/backups/failed-export/client.stderr.log'), $db['password']), 'Password absent from client error');

$bundle = $root . '/private/backups/checksums';
mkdir($bundle, 0700);
file_put_contents($bundle . '/database.sql', 'fixture SQL');
$manifest = ['files'=>['database.sql'=>['bytes'=>11, 'sha256'=>hash_file('sha256', $bundle . '/database.sql')]]];
file_put_contents($bundle . '/manifest.json', json_encode($manifest));
verify_backup_files($bundle);
backup_test(true, 'Valid checksum accepted');
verify_backup_files(substr($bundle, strlen(dirname(__DIR__)) + 1));
backup_test(true, 'Relative bundle directory accepted');
file_put_contents($bundle . '/database.sql', 'tampered');
backup_refused(fn()=>verify_backup_files($bundle), 'Tampered bundle refused');
$manifest['files'] = ['../config.local.php'=>['bytes'=>0, 'sha256'=>'']];
file_put_contents($bundle . '/manifest.json', json_encode($manifest));
backup_refused(fn()=>verify_backup_files($bundle), 'Manifest traversal refused');
file_put_contents($bundle . '/INCOMPLETE.txt', 'failed');
backup_refused(fn()=>verify_backup_files($bundle), 'Partial completion marker refused');

echo "PASS: $checks local backup safety checks. Fictional unit files retained under $root\n";
