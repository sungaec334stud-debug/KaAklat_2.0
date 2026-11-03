<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { exit(1); }
ob_start(); // Session-regeneration checks run before emitting test output.
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/bin/backup_support.php';
require_once dirname(__DIR__) . '/app/pickups.php';
require_once dirname(__DIR__) . '/app/returns.php';
require_once dirname(__DIR__) . '/app/recovery.php';
start_application_session();

$checks = 0;
function restore_check(bool $condition, string $label): void
{
    global $checks;
    if (!$condition) { throw new RuntimeException($label); }
    $checks++;
    echo "PASS: $label\n";
}
function restore_constraint(PDO $db, string $sql, int $code, string $label): void
{
    $db->beginTransaction();
    try {
        $db->exec($sql);
    } catch (PDOException $error) {
        $db->rollBack();
        restore_check((int)($error->errorInfo[1] ?? 0) === $code, $label);
        return;
    }
    $db->rollBack();
    throw new RuntimeException($label . ': invalid write was accepted');
}
function restore_session(int $id, int $version = 1): string
{
    $_SESSION = ['user_id'=>$id, 'session_version'=>$version, 'last_activity'=>time()];
    return csrf_token();
}
function restore_copy_tree(string $source, string $target): void
{
    if (file_exists($target) || !mkdir($target, 0700, true)) { throw new RuntimeException('Restore directory must be new.'); }
    foreach (backup_file_inventory($source) as $relative=>$metadata) {
        $destination = backup_child_path($target, $relative);
        if (!is_dir(dirname($destination))) { mkdir(dirname($destination), 0700, true); }
        if (!copy(backup_child_path($source, $relative), $destination)) { throw new RuntimeException('Isolated file copy failed.'); }
    }
}
function restore_rows(PDO $db, string $table): array
{
    $rows = $db->query('SELECT * FROM `' . $table . '`')->fetchAll();
    $serialized = array_map('serialize', $rows);
    sort($serialized);
    return $serialized;
}

$root = dirname(__DIR__);
$id = bin2hex(random_bytes(6));
$sourceName = 'kaaklat_backup_src_' . $id;
$restoreName = 'kaaklat_restore_' . $id;
$run = $root . '/private/restore-rehearsals/' . $id;
mkdir($run . '/source/private/covers', 0700, true);
$config = application_config();
$sourceConfig = $config['database'];
$sourceConfig['database'] = $sourceName;
$restoreConfig = $sourceConfig;
$restoreConfig['database'] = $restoreName;
$server = null;
$exitCode = 0;
try {
    // This configured connection is used ONLY for CREATE DATABASE. No demo reads/writes/imports.
    $operator = database_connection($config['database']);
    foreach ([$sourceName, $restoreName] as $name) {
        if (!preg_match('/^kaaklat_(?:backup_src|restore)_[a-f0-9]{12}$/D', $name)) { throw new RuntimeException('Unsafe scratch name.'); }
        $operator->exec('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }
    $source = database_connection($sourceConfig);
    foreach ([$root . '/schema.sql', $root . '/migrations/001_book_categories_year.sql'] as $file) {
        $sql = preg_replace('/^--.*$/m', '', (string)file_get_contents($file));
        foreach (explode(';', $sql) as $statement) {
            if (trim($statement) !== '') { $source->exec($statement); }
        }
    }
    $password = bin2hex(random_bytes(18)); // never printed or written in plaintext
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $source->prepare('INSERT INTO users(user_id,username,display_name,password_hash,role,account_status) VALUES(?,?,?,?,?,?)');
    foreach ([[1,'restore_student','Fictional Restore Student','student','active'], [2,'restore_lib','Fictional Restore Librarian','librarian','active'],
        [3,'restore_admin','Fictional Restore Administrator','administrator','active'], [4,'restore_suspended','Fictional Suspended Student','student','suspended']] as [$userId,$username,$display,$role,$status]) {
        $insert->execute([$userId,$username,$display,$hash,$role,$status]);
    }
    $source->exec("INSERT INTO categories(category_id,category_name) VALUES(1,'Fictional recovery category')");
    $source->exec("INSERT INTO authors(author_id,author_name) VALUES(1,'Fictional Restore Author')");
    $coverName = 'upload-' . bin2hex(random_bytes(16)) . '.jpg';
    $image = imagecreatetruecolor(80, 100);
    imagefill($image, 0, 0, imagecolorallocate($image, 20, 70, 140));
    imagejpeg($image, $run . '/source/private/covers/' . $coverName, 85);
    imagedestroy($image);
    $insert = $source->prepare('INSERT INTO books(book_id,title,description,cover_path,category_id,publication_year) VALUES(?,?,?,?,1,2020)');
    for ($book = 1; $book <= 5; $book++) {
        $insert->execute([$book,'Fictional Restore Book ' . $book,'Fictional text with UTF-8 café and punctuation.', $book === 1 ? $coverName : null]);
        $source->exec("INSERT INTO book_authors(book_id,author_id,credit_order) VALUES($book,1,1)");
        $source->exec("INSERT INTO book_copies(copy_id,book_id,copy_code) VALUES($book,$book,'RESTORE-$book')");
    }
    $activeId = create_borrow_request($source, 2, 7, 'Fictional active loan history.', restore_session(1));
    decide_borrow_request($source, $activeId, 'approve', 8, 'Fictional duration explanation.', restore_session(2));
    record_pickup($source, $activeId, restore_session(2));
    $returnedId = create_borrow_request($source, 3, 7, 'Fictional returned loan history.', restore_session(1));
    decide_borrow_request($source, $returnedId, 'approve', 7, '', restore_session(2));
    record_pickup($source, $returnedId, restore_session(2));
    record_return($source, $returnedId, restore_session(2));
    $heldId = create_borrow_request($source, 4, 7, 'Fictional approved pickup history.', restore_session(1));
    decide_borrow_request($source, $heldId, 'approve', 7, '', restore_session(2));
    $rejectedId = create_borrow_request($source, 5, 7, 'Fictional rejection history.', restore_session(1));
    decide_borrow_request($source, $rejectedId, 'reject', 7, 'Fictional rejection explanation.', restore_session(2));
    $cancelledId = create_borrow_request($source, 5, 7, 'Fictional cancelled request history.', restore_session(1));
    cancel_borrow_request($source, $cancelledId, restore_session(1));
    $recovery = issue_recovery($source, 'restore_student', restore_session(2), true);
    authenticate_user($source, 'restore_student', 'deliberately incorrect fixture password');
    $config['database'] = $sourceConfig;
    $config['session_name'] = 'kaaklat_rehearsal_source_' . $id;
    file_put_contents($run . '/source/private/config.local.php', '<?php return ' . var_export($config, true) . ';');
    foreach (['app','public','views','bin','migrations'] as $directory) {
        restore_copy_tree($root . '/' . $directory, $run . '/source/' . $directory);
    }
    copy($root . '/schema.sql', $run . '/source/schema.sql');

    $backup = create_local_backup($sourceConfig, $run . '/source', 'snapshot', 'C:/xampp/mysql/bin/mysqldump.exe');
    $bundle = $backup['directory'];
    $manifest = verify_backup_files($bundle);
    restore_check(count($manifest['tables']) === 12, 'All 12 application tables exported');
    restore_check(glob($bundle . '/client-*.cnf') === [], 'Ephemeral client credentials removed');
    restore_check(is_file($bundle . '/code/public/index.php') && is_file($bundle . '/code/schema.sql'), 'Matching working source code included in bundle');
    $dump = (string)file_get_contents($bundle . '/database.sql');
    restore_check(!preg_match('/^\s*(?:USE\s|CREATE\s+DATABASE\s|DROP\s|TRUNCATE\s)/mi', $dump), 'Export contains no database selection or destructive DDL');

    $started = microtime(true);
    $target = database_connection($restoreConfig);
    restore_check($target->query('SHOW TABLES')->fetchAll() === [], 'Restore target is a distinct new empty database');
    mkdir($run . '/import-client', 0700);
    run_private_database_tool('C:/xampp/mysql/bin/mysql.exe', $restoreConfig, $run . '/import-client',
        ['--default-character-set=utf8mb4', '--binary-mode', $restoreName], $bundle . '/database.sql');
    $target->exec("SET time_zone = '+00:00'");
    verify_backup_files($bundle); // Import diagnostics must not mutate the source backup.
    restore_check(backup_database_inventory($target) === $manifest['tables'], 'All table counts and SHOW CREATE definitions match');
    foreach (array_keys($manifest['tables']) as $table) {
        restore_check(restore_rows($source, $table) === restore_rows($target, $table), 'Exact restored rows: ' . $table);
    }
    restore_check($target->query('SELECT token_hash FROM password_resets')->fetchColumn() === hash('sha256', hex2bin($recovery), true), 'Binary recovery digest survives export/import');
    $dbName = $target->quote($restoreName);
    $checksCount = (int)$target->query("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=$dbName AND CONSTRAINT_TYPE='CHECK'")->fetchColumn();
    $fkCount = (int)$target->query("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=$dbName AND CONSTRAINT_TYPE='FOREIGN KEY'")->fetchColumn();
    restore_check($checksCount >= 20 && $fkCount >= 15, 'Restored CHECK and foreign-key definitions present');
    restore_constraint($target, "INSERT INTO users(username,display_name) VALUES('bad','')", 4025, 'CHECK rejects invalid restored row');
    restore_constraint($target, "INSERT INTO book_copies(book_id,copy_code) VALUES(99999,'FK-INVALID')", 1452, 'Restored foreign key rejects orphan');
    restore_constraint($target, "INSERT INTO users(username,display_name) VALUES('restore_student','Duplicate')", 1062, 'Restored username uniqueness enforced');
    restore_constraint($target, 'INSERT INTO loans(request_id,copy_id) VALUES(' . $rejectedId . ',2)', 1062, 'Generated active-copy uniqueness enforced');
    $rowsBefore = restore_rows($target, 'authors');
    $target->beginTransaction();
    $target->exec("INSERT INTO authors(author_name) VALUES('Fictional rollback author')");
    $target->rollBack();
    restore_check(restore_rows($target, 'authors') === $rowsBefore, 'Restored InnoDB transaction rollback');
    foreach ([1=>1,2=>0,3=>1,4=>0,5=>1] as $book=>$expected) {
        restore_check((int)catalog_book($target, $book)['available_copies'] === $expected, 'Restored derived availability book ' . $book);
    }

    $restoredApp = $run . '/restored-app';
    mkdir($restoredApp, 0700);
    foreach (['app','public','views'] as $directory) { restore_copy_tree($bundle . '/code/' . $directory, $restoredApp . '/' . $directory); }
    mkdir($restoredApp . '/private', 0700);
    restore_copy_tree($bundle . '/covers', $restoredApp . '/private/covers');
    mkdir($restoredApp . '/private/fresh-sessions', 0700);
    restore_check(backup_file_inventory($bundle . '/covers') === backup_file_inventory($restoredApp . '/private/covers'), 'All restored cover bytes/checksums match');
    restore_check(getimagesize($restoredApp . '/private/covers/' . $coverName)['mime'] === 'image/jpeg', 'Restored cover actually decodes');
    restore_check(authenticate_user($target, 'restore_student', $password) !== null, 'Restored password works');
    restore_check(authenticate_user($target, 'restore_suspended', $password) === null, 'Suspended restored account remains denied');
    restore_session(1);
    restore_check(current_user($target) !== null, 'Restored matching session version initially valid');
    $target->beginTransaction();
    $target->exec('UPDATE users SET session_version=session_version+1');
    $target->exec('DELETE FROM password_resets'); // ONLY newly restored scratch security secrets; history retained.
    $target->commit();
    restore_check(current_user($target) === null, 'Old session version rejected after restore quarantine');
    try { redeem_recovery($target, $recovery, bin2hex(random_bytes(18)), csrf_token()); throw new RuntimeException('Recovery revived'); }
    catch (InvalidArgumentException) { restore_check(true, 'Restored recovery secret cannot be redeemed after quarantine'); }
    $config['database'] = $restoreConfig;
    $config['session_name'] = 'kaaklat_restore_fresh_' . $id;
    file_put_contents($restoredApp . '/private/config.local.php', '<?php return ' . var_export($config, true) . ';');
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorNumber, $errorText);
    if ($socket === false) { throw new RuntimeException('No local test port available.'); }
    $port = (int)substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
    fclose($socket);
    $server = proc_open([PHP_BINARY, '-d', 'session.save_path=' . $restoredApp . '/private/fresh-sessions', '-S', '127.0.0.1:' . $port, '-t', $restoredApp . '/public'],
        [0=>['pipe','r'],1=>['file',$run . '/server.log','a'],2=>['file',$run . '/server.log','a']], $pipes);
    if (!is_resource($server)) { throw new RuntimeException('Isolated HTTP server failed.'); }
    fclose($pipes[0]);
    $http = curl_init();
    curl_setopt_array($http, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_COOKIEFILE=>'', CURLOPT_TIMEOUT=>3]);
    $request = function (string $path, ?array $post = null) use ($http, $port): array {
        curl_setopt_array($http, [CURLOPT_URL=>'http://127.0.0.1:' . $port . $path, CURLOPT_POSTFIELDS=>$post === null ? null : http_build_query($post), CURLOPT_POST=>$post !== null]);
        $body = curl_exec($http);
        return [(int)curl_getinfo($http, CURLINFO_RESPONSE_CODE), $body];
    };
    $deadline = microtime(true) + 30;
    do { [$status,$html] = $request('/health.php'); if ($status === 200) { break; } usleep(200000); } while (microtime(true) < $deadline);
    restore_check($status === 200 && str_contains((string)$html, 'ready'), 'Separate restored application healthy');
    curl_setopt($http, CURLOPT_COOKIE, 'kaaklat_rehearsal_source_' . $id . '=fictionaloldsession');
    [$status] = $request('/catalog.php');
    restore_check($status === 303, 'Old cookie namespace does not authenticate restored copy');
    curl_setopt($http, CURLOPT_COOKIE, '');
    [$status] = $request('/cover.php?id=1');
    restore_check($status === 403, 'Restored cover denies signed-out access');
    foreach (['/private/covers/' . $coverName, '/private/config.local.php', '/bin/local_backup.php', '/backups/snapshot/database.sql', '/../private/config.local.php'] as $path) {
        [$status,$body] = $request($path);
        $denied = $status === 404 || ($status === 200 && str_contains((string)$body, 'landing-hero'));
        restore_check($denied && !str_contains((string)$body, '<?php return')
            && hash('sha256', (string)$body) !== hash_file('sha256', $bundle . '/covers/' . $coverName)
            && !str_contains((string)$body, 'CREATE TABLE'), 'Private path returns no private content: ' . $path);
    }
    [$status,$html] = $request('/login.php');
    if (!preg_match('/name="csrf_token" value="([a-f0-9]+)"/', (string)$html, $match)) { throw new RuntimeException('Missing login CSRF'); }
    [$status] = $request('/login.php', ['username'=>'restore_student','password'=>$password,'csrf_token'=>$match[1]]);
    restore_check($status === 303, 'Fresh HTTP login succeeds after quarantine');
    [$status,$bytes] = $request('/cover.php?id=1');
    restore_check($status === 200 && hash('sha256', (string)$bytes) === hash_file('sha256', $bundle . '/covers/' . $coverName), 'Authenticated restored endpoint retrieves matching cover');
    $target->exec('UPDATE users SET session_version=session_version+1 WHERE user_id=1');
    [$status] = $request('/catalog.php');
    restore_check($status === 303, 'HTTP session revoked when restored account version changes');

    $version = (int)$target->query('SELECT session_version FROM users WHERE user_id=1')->fetchColumn();
    $newId = create_borrow_request($target, 1, 7, 'Fictional complete workflow after restore.', restore_session(1, $version));
    decide_borrow_request($target, $newId, 'approve', 7, '', restore_session(2, 2));
    restore_check((int)catalog_book($target, 1)['available_copies'] === 0, 'New restored approval allocates copy');
    record_pickup($target, $newId, restore_session(2, 2));
    record_return($target, $newId, restore_session(2, 2));
    restore_check((int)catalog_book($target, 1)['available_copies'] === 1, 'Full restored request/approval/pickup/return workflow releases copy');
    restore_session(1, $version);
    $history = own_borrow_requests($target, 1);
    restore_check(count($history) === 6 && count(array_filter($history, fn(array $row): bool => (int)$row['student_id'] !== 1)) === 0, 'Student retrieves six own restored history records');
    $elapsed = round(microtime(true) - $started, 2);
    restore_check($elapsed <= 1800, 'Fictional local restore rehearsal within 30-minute target');
    $report = ['run'=>$id, 'source_database'=>$sourceName, 'restored_database'=>$restoreName, 'bundle'=>$bundle,
        'restored_app'=>$restoredApp, 'checks'=>$checks, 'restore_seconds'=>$elapsed, 'server_version'=>$manifest['server_version'],
        'completed_utc'=>gmdate('c'), 'limits'=>'Fictional small local data only. No real demo restore, off-device copy, deployment, performance or 24-hour backup schedule evidence.'];
    file_put_contents($run . '/result.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} catch (Throwable $error) {
    file_put_contents($run . '/failure.log', get_class($error) . ': ' . $error->getMessage());
    fwrite(STDERR, "FAIL: isolated restore rehearsal. Inspect private failure.log. Resources retained: $sourceName, $restoreName, $run\n");
    $exitCode = 1;
} finally {
    if (is_resource($server)) { proc_terminate($server); proc_close($server); }
}
ob_end_flush();
exit($exitCode);
