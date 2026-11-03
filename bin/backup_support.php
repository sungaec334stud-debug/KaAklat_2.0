<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/database.php';

// Operator-only helpers. Not loaded by application HTTP entry points.
function backup_child_path(string $parent, string $relative): string
{
    if (!preg_match('~^[a-zA-Z0-9_.-]+(?:/[a-zA-Z0-9_.-]+)*$~D', $relative)
        || in_array('..', explode('/', $relative), true) || in_array('.', explode('/', $relative), true)) {
        throw new InvalidArgumentException('Invalid backup-relative path.');
    }
    if (is_link($parent)) { throw new RuntimeException('Linked backup parent is not permitted.'); }
    $path = realpath($parent);
    if ($path === false || !is_dir($path)) { throw new RuntimeException('Backup parent directory is missing.'); }
    foreach (explode('/', $relative) as $part) {
        $path .= '/' . $part;
        if (is_link($path)) { throw new RuntimeException('Linked paths are not permitted in backups.'); }
        $resolved = realpath($path);
        if ($resolved !== false && strtolower(str_replace('\\', '/', $resolved)) !== strtolower(str_replace('\\', '/', $path))) {
            throw new RuntimeException('Redirected paths are not permitted in backups.');
        }
    }
    return $path;
}

function backup_file_inventory(string $directory): array
{
    if (!is_dir($directory) || is_link($directory)) { throw new RuntimeException('Private source directory is unavailable or linked.'); }
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isLink()) { throw new RuntimeException('Linked files are not permitted in backups.'); }
        if (!$file->isFile()) { throw new RuntimeException('Unsupported backup file type.'); }
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($directory) + 1));
        backup_child_path($directory, $relative);
        $files[$relative] = ['bytes'=>$file->getSize(), 'sha256'=>hash_file('sha256', $file->getPathname())];
    }
    ksort($files);
    return $files;
}

function backup_database_inventory(PDO $db): array
{
    $inventory = [];
    foreach ($db->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM) as [$table, $type]) {
        if ($type !== 'BASE TABLE' || !preg_match('/^[a-zA-Z0-9_]+$/D', $table)) {
            throw new RuntimeException('Only application base tables are supported.');
        }
        $definition = $db->query('SHOW CREATE TABLE `' . $table . '`')->fetch(PDO::FETCH_NUM)[1];
        if (!str_contains($definition, 'ENGINE=InnoDB')) { throw new RuntimeException('All backup tables must use InnoDB.'); }
        $inventory[$table] = ['rows'=>(int)$db->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn(), 'definition_sha256'=>hash('sha256', $definition)];
    }
    if ($inventory === []) { throw new RuntimeException('No application tables found.'); }
    if ($db->query('SHOW TRIGGERS')->fetch()) { throw new RuntimeException('Unexpected triggers require a separately reviewed backup.'); }
    ksort($inventory);
    return $inventory;
}

function backup_source_inventory(string $root): array
{
    $files = [];
    foreach (['app','public','views','bin','migrations'] as $directory) {
        if (!is_dir($root . '/' . $directory)) {
            if (in_array($directory, ['app','public','views'], true)) { throw new RuntimeException('Required application source directory is missing.'); }
            continue;
        }
        foreach (backup_file_inventory(backup_child_path($root, $directory)) as $relative=>$metadata) {
            $files[$directory . '/' . $relative] = $metadata;
        }
    }
    foreach ([...glob($root . '/*.md'), ...glob($root . '/*.sql'), $root . '/.gitignore', $root . '/.env.example'] as $file) {
        if (!is_file($file)) { continue; }
        $relative = basename($file);
        backup_child_path($root, $relative);
        $files[$relative] = ['bytes'=>filesize($file), 'sha256'=>hash_file('sha256', $file)];
    }
    ksort($files);
    return $files;
}

function backup_option_value(string $value): string
{
    if (str_contains($value, "\0")) { throw new InvalidArgumentException('Invalid client option.'); }
    return '"' . strtr($value, ['\\'=>'\\\\', '"'=>'\\"', "\n"=>'\\n', "\r"=>'\\r', "\t"=>'\\t']) . '"';
}

function run_private_database_tool(string $executable, array $database, string $privateDirectory, array $arguments, ?string $input = null): void
{
    if (!is_file($executable)) { throw new RuntimeException('Database client executable was not found.'); }
    // Validate host/name/port without putting credentials in command-line arguments.
    database_dsn($database);
    $options = $privateDirectory . '/client-' . bin2hex(random_bytes(8)) . '.cnf';
    $handle = fopen($options, 'x');
    if ($handle === false) { throw new RuntimeException('Cannot create private client options.'); }
    try {
        chmod($options, 0600); // Windows access is controlled by the parent folder's ACL, documented separately.
        $contents = "[client]\nprotocol=tcp\n";
        foreach (['host', 'port', 'username', 'password'] as $key) {
            $contents .= ($key === 'username' ? 'user' : $key) . '=' . backup_option_value((string)$database[$key]) . "\n";
        }
        if (fwrite($handle, $contents) !== strlen($contents)) { throw new RuntimeException('Cannot write private client options.'); }
        fclose($handle);
        $handle = null;
        $descriptors = [0=>$input === null ? ['pipe','r'] : ['file',$input,'r'],
            1=>['file',$privateDirectory . '/client.stdout.log','a'], 2=>['file',$privateDirectory . '/client.stderr.log','a']];
        $process = @proc_open([$executable, '--defaults-file=' . $options, ...$arguments], $descriptors, $pipes, null, null, ['bypass_shell'=>true]);
        if (!is_resource($process)) { throw new RuntimeException('Database client could not start.'); }
        if ($input === null) { fclose($pipes[0]); }
        if (proc_close($process) !== 0) { throw new RuntimeException('Database client failed. Inspect private client.stderr.log; do not rely on this bundle.'); }
    } finally {
        if (is_resource($handle)) { fclose($handle); }
        if (is_file($options) && !unlink($options)) { throw new RuntimeException('Private client options cleanup failed; secure/remove that file before continuing.'); }
    }
}

function verify_backup_files(string $bundle, bool $allowIncomplete = false): array
{
    if (!$allowIncomplete && is_file($bundle . '/INCOMPLETE.txt')) { throw new RuntimeException('Backup is incomplete.'); }
    $manifest = json_decode((string)file_get_contents(backup_child_path($bundle, 'manifest.json')), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($manifest['files'] ?? null) || $manifest['files'] === []) { throw new RuntimeException('Backup file manifest is missing.'); }
    foreach ($manifest['files'] as $relative=>$expected) {
        $path = backup_child_path($bundle, $relative);
        if (!is_file($path) || filesize($path) !== $expected['bytes'] || hash_file('sha256', $path) !== $expected['sha256']) {
            throw new RuntimeException('Backup file integrity verification failed.');
        }
    }
    return $manifest;
}

function create_local_backup(array $database, string $projectRoot, string $bundleName, string $dumpExecutable): array
{
    if (PHP_SAPI !== 'cli') { throw new RuntimeException('Local backups require the command line.'); }
    if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,79}$/D', $bundleName)) { throw new InvalidArgumentException('Use a simple unique backup folder name.'); }
    $root = realpath($projectRoot);
    $private = realpath($projectRoot . '/private');
    if ($root === false || $private === false || is_link($projectRoot . '/private')
        || strtolower(str_replace('\\','/',$private)) !== strtolower(str_replace('\\','/',$root) . '/private')) {
        throw new RuntimeException('Backup storage must be the project private directory.');
    }
    $parent = backup_child_path($private, 'backups');
    if (!is_dir($parent) && !mkdir($parent, 0700)) { throw new RuntimeException('Cannot create private backup storage.'); }
    $bundle = backup_child_path($parent, $bundleName);
    if (file_exists($bundle) || !mkdir($bundle, 0700)) { throw new RuntimeException('Backup destination already exists or cannot be created. Choose a new name.'); }
    file_put_contents($bundle . '/INCOMPLETE.txt', 'Do not restore this bundle unless manifest.json exists and file verification passes.');
    if (!is_file($dumpExecutable)) { throw new RuntimeException('Database export executable was not found.'); }
    $started = gmdate('c');
    $db = database_connection($database);
    $db->exec("SET time_zone = '+00:00'");
    $tables = backup_database_inventory($db);
    $covers = backup_child_path($private, 'covers');
    $before = backup_file_inventory($covers);
    $codeBefore = backup_source_inventory($root);
    $config = backup_child_path($private, 'config.local.php');
    if (!is_file($config)) { throw new RuntimeException('Private configuration is missing.'); }
    $configHash = hash_file('sha256', $config);
    run_private_database_tool($dumpExecutable, $database, $bundle, [
        '--single-transaction', '--quick', '--hex-blob', '--default-character-set=utf8mb4',
        '--skip-add-drop-table', '--skip-add-locks', '--skip-comments', '--skip-triggers', '--no-tablespaces',
        '--result-file=' . $bundle . '/database.sql', (string)$database['database'],
    ]);
    if (!is_file($bundle . '/database.sql') || filesize($bundle . '/database.sql') === 0) { throw new RuntimeException('Database export is empty.'); }
    mkdir($bundle . '/covers', 0700);
    foreach ($before as $relative=>$metadata) {
        $source = backup_child_path($covers, $relative);
        $target = backup_child_path($bundle . '/covers', $relative);
        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0700, true)) { throw new RuntimeException('Cannot create cover backup directory.'); }
        if (!copy($source, $target)) { throw new RuntimeException('Cover backup failed.'); }
    }
    if (!copy($config, $bundle . '/config.local.php')) { throw new RuntimeException('Configuration backup failed.'); }
    mkdir($bundle . '/code', 0700);
    foreach ($codeBefore as $relative=>$metadata) {
        $target = backup_child_path($bundle . '/code', $relative);
        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0700, true)) { throw new RuntimeException('Cannot create code backup directory.'); }
        if (!copy(backup_child_path($root, $relative), $target)) { throw new RuntimeException('Code backup failed.'); }
    }
    if ($before !== backup_file_inventory($covers) || $before !== backup_file_inventory($bundle . '/covers')
        || $configHash !== hash_file('sha256', $bundle . '/config.local.php') || $configHash !== hash_file('sha256', $config)
        || $tables !== backup_database_inventory($db) || $codeBefore !== backup_source_inventory($root)
        || $codeBefore !== backup_file_inventory($bundle . '/code')) {
        throw new RuntimeException('Sources changed during backup. Keep writes paused and make a new backup.');
    }
    $manifest = ['format'=>1, 'started_utc'=>$started, 'completed_utc'=>gmdate('c'),
        'source_database'=>(string)$database['database'], 'server_version'=>$db->query('SELECT VERSION()')->fetchColumn(),
        'tables'=>$tables, 'files'=>backup_file_inventory($bundle),
        'source_requirement'=>'code/ includes current app/public/views/bin/migrations and root Markdown/SQL, .gitignore/.env.example, including uncommitted edits; no Git history, tests or research output. Retain the repository separately. This is not an off-device copy or a verified restore.'];
    unset($manifest['files']['INCOMPLETE.txt']);
    file_put_contents($bundle . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    verify_backup_files($bundle, true);
    if (!unlink($bundle . '/INCOMPLETE.txt')) { throw new RuntimeException('Backup completion marker cleanup failed.'); }
    return ['directory'=>$bundle, 'manifest'=>$manifest];
}
