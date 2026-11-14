<?php

// Explicit operator command, excluded from the regression runner. Retains its scratch database.
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { exit(1); }
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog.php';

function performance_assert(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException($message); }
}

function performance_write_json(string $path, array $value): void
{
    performance_assert(file_put_contents($path, json_encode($value, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)) !== false,
        'Could not write benchmark evidence.');
}

function performance_import(PDO $db, string $path): void
{
    $sql = preg_replace('/^--.*$/m', '', (string) file_get_contents($path));
    foreach (explode(';', $sql) as $statement) {
        if (trim($statement) !== '') { $db->exec($statement); }
    }
}

function performance_fixture(PDO $db, string $password): void
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $user = $db->prepare('INSERT INTO users (user_id, username, display_name, password_hash, role, account_status, school_student_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)');
    for ($id = 1; $id <= 33; $id++) {
        $role = $id <= 30 ? 'student' : ($id === 33 ? 'administrator' : 'librarian');
        $user->execute([$id, 'perf_user_' . $id, 'Fictional Performance User ' . $id, $hash, $role, 'active',
            $id <= 30 ? sprintf('00-0000-%03d', $id) : null]);
    }
    for ($id = 1; $id <= 5; $id++) {
        $db->prepare('INSERT INTO categories (category_id, category_name) VALUES (?, ?)')
            ->execute([$id, 'Fictional Course ' . $id]);
        $db->prepare('INSERT INTO authors (author_id, author_name) VALUES (?, ?)')
            ->execute([$id, 'Fictional Author ' . $id]);
    }
    for ($id = 1; $id <= 100; $id++) {
        $category = (($id - 1) % 5) + 1;
        $db->prepare('INSERT INTO books (book_id, title, description, category_id, publication_year) VALUES (?, ?, ?, ?, ?)')
            ->execute([$id, sprintf('Fictional Textbook %03d', $id),
                str_repeat('Fictional performance reading for catalog verification. ', 5), $category, 2020 + $category]);
        $db->prepare('INSERT INTO book_authors (book_id, author_id, credit_order) VALUES (?, ?, 1)')
            ->execute([$id, $category]);
        if ($id % 2 === 0) {
            $db->prepare('INSERT INTO book_authors (book_id, author_id, credit_order) VALUES (?, ?, 2)')
                ->execute([$id, ($category % 5) + 1]);
        }
        for ($copy = 1; $copy <= 3; $copy++) {
            $db->prepare('INSERT INTO book_copies (copy_id, book_id, copy_code) VALUES (?, ?, ?)')
                ->execute([($id - 1) * 3 + $copy, $id, sprintf('PERF-%03d-%d', $id, $copy)]);
        }
    }
    $request = $db->prepare("INSERT INTO borrow_requests (student_id, book_id, requested_days, request_reason,
        requested_at, decision, approved_days, decided_at, decided_by, decision_note)
        VALUES (?, ?, 7, 'Fictional benchmark reading assignment.', UTC_TIMESTAMP() - INTERVAL 20 DAY,
            ?, ?, ?, ?, ?)");
    for ($student = 1; $student <= 30; $student++) {
        foreach (['pending', 'pickup', 'active', 'returned', 'expired', 'rejected'] as $state) {
            $book = match ($state) {
                'pending', 'returned' => $student,
                'pickup', 'expired' => 30 + $student,
                'active' => 60 + $student,
                default => 70 + $student,
            };
            $decision = match ($state) { 'pending' => 'pending', 'rejected' => 'rejected', default => 'approved' };
            $decidedAt = $decision === 'pending' ? null : gmdate('Y-m-d H:i:s',
                time() - (in_array($state, ['active', 'returned', 'expired'], true) ? 10 * 86400 : 3600));
            $request->execute([$student, $book, $decision, $decision === 'approved' ? 7 : null, $decidedAt,
                $decision === 'pending' ? null : 31, $state === 'rejected' ? 'Fictional review explanation.' : null]);
            $requestId = (int) $db->lastInsertId();
            if (in_array($state, ['pending', 'rejected'], true)) { continue; }
            $copyId = ($book - 1) * 3 + ($state === 'expired' ? 2 : 1);
            $db->prepare('INSERT INTO loans (request_id, copy_id) VALUES (?, ?)')->execute([$requestId, $copyId]);
            $loanId = (int) $db->lastInsertId();
            if (in_array($state, ['active', 'returned'], true)) {
                $daysAgo = $state === 'returned' || $student % 3 === 0 ? 8 : 2;
                $db->prepare("UPDATE loans SET checked_out_at = UTC_TIMESTAMP() - INTERVAL ? DAY, checked_out_by = 31,
                    due_date = DATE(UTC_TIMESTAMP() + INTERVAL 8 HOUR) + INTERVAL ? DAY WHERE loan_id = ?")
                    ->execute([$daysAgo, 7 - $daysAgo, $loanId]);
                if ($state === 'returned') {
                    $db->prepare('UPDATE loans SET returned_at = UTC_TIMESTAMP() - INTERVAL 1 DAY, returned_by = 32 WHERE loan_id = ?')
                        ->execute([$loanId]);
                }
            }
        }
    }
    // An unknown year exercises null-last ordering without changing the required dataset size.
    $db->exec('UPDATE books SET publication_year=NULL WHERE book_id=100');
    // A fully allocated title makes the availability scenario meaningfully different.
    for ($student = 1; $student <= 3; $student++) {
        $request->execute([$student, 100, 'approved', 7, gmdate('Y-m-d H:i:s', time() - 3600), 31, null]);
        $db->prepare('INSERT INTO loans (request_id, copy_id) VALUES (?, ?)')->execute([(int) $db->lastInsertId(), 297 + $student]);
    }
}

function performance_client(): CurlHandle
{
    $client = curl_init();
    curl_setopt_array($client, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => '',
        CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 15, CURLOPT_FOLLOWLOCATION => false, CURLOPT_PROXY => '']);
    return $client;
}

function performance_request(CurlHandle $client, string $url, ?array $post = null): string
{
    curl_setopt_array($client, [CURLOPT_URL => $url, CURLOPT_POSTFIELDS => $post === null ? null : http_build_query($post),
        CURLOPT_POST => $post !== null, CURLOPT_HTTPGET => $post === null]);
    $body = curl_exec($client);
    performance_assert($body !== false, 'HTTP request failed with cURL code ' . curl_errno($client));
    return $body;
}

function performance_validate(string|false $body, int $status, array $expected, int $userId = 0): bool
{
    if ($status !== 200 || $body === false || ($userId > 0
        && !str_contains($body, 'Fictional Performance User ' . $userId . '<'))) { return false; }
    $document = new DOMDocument();
    @$document->loadHTML($body);
    $xpath = new DOMXPath($document);
    $ids = [];
    foreach ($xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " book-card ")]/h2/a') as $link) {
        parse_str((string) parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
        $ids[] = (int) ($query['id'] ?? 0);
    }
    $summary = $xpath->query('//p[@class="pagination-summary"]');
    return $ids === $expected['ids'] && $summary->length === 1
        && trim($summary->item(0)->textContent) === $expected['summary']
        && str_contains($body, '<h1>Explore the library</h1>');
}

function performance_statistics(array $samples): array
{
    $times = array_column($samples, 'total_ms');
    sort($times, SORT_NUMERIC);
    return ['samples' => count($samples), 'failures' => count(array_filter($samples, static fn (array $row): bool => !$row['valid'])),
        'average_ms' => round(array_sum($times) / count($times), 3),
        'p95_ms' => round($times[(int) ceil(count($times) * .95) - 1], 3), 'maximum_ms' => round(max($times), 3),
        'p95_ttfb_ms' => performance_percentile(array_column($samples, 'ttfb_ms')),
        'http_errors' => count(array_filter($samples, static fn(array $row): bool => $row['status'] >= 400)),
        'redirect_responses' => count(array_filter($samples, static fn(array $row): bool => $row['status'] >= 300 && $row['status'] < 400)),
        'transport_errors' => count(array_filter($samples, static fn(array $row): bool => $row['curl_error'] !== 0)),
        'timeouts' => count(array_filter($samples, static fn(array $row): bool => $row['curl_error'] === CURLE_OPERATION_TIMEDOUT))];
}

function performance_percentile(array $times): float
{
    sort($times, SORT_NUMERIC);
    return round($times[(int) ceil(count($times) * .95) - 1], 3);
}

// Check that the harness itself cannot credit redirects or login/error pages as catalog responses.
performance_assert(!performance_validate('<h1>Log in</h1>', 200, ['ids' => [], 'summary' => '0 books · Page 1 of 1'])
    && !performance_validate(false, 0, ['ids' => [], 'summary' => ''])
    && !performance_validate('<h1>Explore the library</h1>', 303, ['ids' => [], 'summary' => '']),
    'Harness failure classification failed.');
performance_assert(performance_percentile(range(1, 30)) === 29.0, 'Nearest-rank p95 self-check failed.');
$validatorExample = '<meta charset="UTF-8"><h1>Explore the library</h1><span>Fictional Performance User 1</span><article class="book-card"><h2><a href="/catalog.php?id=7">Fictional</a></h2></article><p class="pagination-summary">1 books · Page 1 of 1</p>';
$validatorExpected = ['ids'=>[7], 'summary'=>'1 books · Page 1 of 1'];
performance_assert(performance_validate($validatorExample,200,$validatorExpected,1)
    && !performance_validate($validatorExample,200,$validatorExpected,2)
    && !performance_validate($validatorExample,200,['ids'=>[8],'summary'=>$validatorExpected['summary']],1),
    'Harness exact-content and session identity self-check failed.');
unset($validatorExample, $validatorExpected);


$root = dirname(__DIR__);
$config = application_config();
performance_assert($config['app_env'] === 'local'
    && in_array($config['database']['host'], ['127.0.0.1', 'localhost'], true),
    'Run only with a local application and loopback database.');
foreach (['curl', 'dom', 'pdo_mysql'] as $extension) {
    performance_assert(extension_loaded($extension), 'Missing extension ' . $extension);
}
$run = bin2hex(random_bytes(6));
$name = 'kaaklat_perf_' . $run;
$directory = $root . '/tmp/performance-' . $run;
performance_assert(mkdir($directory, 0700, true), 'Cannot create private evidence directory.');
$manifest = ['database' => $name, 'directory' => $directory, 'status' => 'preparing',
    'created_at_utc' => gmdate(DateTimeInterface::ATOM)];
performance_write_json($directory . '/run.json', $manifest);
$server = null;
$clients = [];
$csv = null;
try {
    $operator = database_connection($config['database']);
    $operator->exec('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $scratchConfig = $config['database'];
    $scratchConfig['database'] = $name;
    $db = database_connection($scratchConfig);
    foreach (['schema.sql', 'migrations/001_book_categories_year.sql', 'migrations/002_student_school_id.sql'] as $file) {
        performance_import($db, $root . '/' . $file);
    }
    $password = bin2hex(random_bytes(24));
    $db->beginTransaction();
    performance_fixture($db, $password);
    $db->commit();
    $manifest['dataset'] = [];
    foreach (['books', 'book_copies', 'users', 'borrow_requests', 'loans'] as $table) {
        $manifest['dataset'][$table] = (int) $db->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn();
    }
    performance_assert($manifest['dataset']['books'] === 100 && $manifest['dataset']['book_copies'] === 300
        && $manifest['dataset']['users'] === 33, 'Fixture size mismatch.');
    $manifest['dataset']['unknown_year_titles'] = 1;
    $manifest['dataset']['students'] = 30;
    $manifest['dataset']['staff'] = ['librarians' => 2, 'administrators' => 1];
    $manifest['dataset']['account_roles'] = $db->query('SELECT role, COUNT(*) AS records FROM users GROUP BY role ORDER BY role')->fetchAll();
    $manifest['dataset']['request_decisions'] = $db->query('SELECT decision, COUNT(*) AS records FROM borrow_requests GROUP BY decision ORDER BY decision')->fetchAll();
    $manifest['environment'] = ['php_version' => PHP_VERSION, 'php_binary' => PHP_BINARY,
        'database_version' => $db->query('SELECT VERSION()')->fetchColumn(), 'os' => PHP_OS_FAMILY,
        'server' => 'PHP built-in development server, one process', 'transport' => 'HTTP on IPv4 loopback',
        'opcache_enabled_cli' => ini_get('opcache.enable_cli'), 'memory_limit' => ini_get('memory_limit'),
        'curl' => curl_version()['version'], 'php_extensions' => get_loaded_extensions()];
    $manifest['source_sha256'] = [];
    foreach (['app','public','views'] as $sourceDirectory) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $sourceDirectory, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file->isFile()) { $manifest['source_sha256'][str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1))] = hash_file('sha256', $file->getPathname()); }
        }
    }
    foreach (['tests/catalog_performance.php','tests/performance_router.php'] as $file) { $manifest['source_sha256'][$file] = hash_file('sha256', $root . '/' . $file); }
    ksort($manifest['source_sha256']);
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
    performance_assert(is_resource($socket), 'Cannot allocate a loopback port.');
    $address = stream_socket_get_name($socket, false);
    fclose($socket);
    $manifest['address'] = 'http://' . $address;
    performance_assert(mkdir($directory . '/sessions', 0700), 'Cannot create isolated sessions directory.');
    putenv('KAAKLAT_PERFORMANCE_DB=' . $name);
    putenv('KAAKLAT_PERFORMANCE_RUN=' . $run);
    $server = proc_open([PHP_BINARY, '-d', 'session.save_path=' . $directory . '/sessions', '-S', $address, '-t', $root . '/public', __DIR__ . '/performance_router.php'],
        [0 => ['pipe', 'r'], 1 => ['file', $directory . '/server.log', 'a'], 2 => ['file', $directory . '/server.log', 'a']], $pipes);
    performance_assert(is_resource($server), 'Benchmark server failed to start.');
    fclose($pipes[0]);
    $probe = performance_client();
    $deadline = microtime(true) + 20;
    $ready = false;
    do {
        $headers = [];
        curl_setopt($probe, CURLOPT_HEADERFUNCTION, static function ($handle, string $line) use (&$headers): int {
            $headers[] = trim($line);
            return strlen($line);
        });
        $body = curl_exec_for_performance_probe($probe, $manifest['address'] . '/health.php');
        $ready = $body === '{"status":"ready"}' && in_array('X-Kaaklat-Performance-Run: ' . $run, $headers, true);
        if (!$ready) { usleep(100000); }
    } while (!$ready && microtime(true) < $deadline);
    performance_assert($ready, 'Isolated benchmark server did not become ready.');
    curl_close($probe);
    $sessionFingerprints = [];
    for ($userId = 1; $userId <= 5; $userId++) {
        $client = performance_client();
        $login = performance_request($client, $manifest['address'] . '/login.php');
        performance_assert(preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $login, $token) === 1, 'Missing login CSRF.');
        performance_request($client, $manifest['address'] . '/login.php',
            ['username' => 'perf_user_' . $userId, 'password' => $password, 'csrf_token' => $token[1]]);
        performance_assert(curl_getinfo($client, CURLINFO_RESPONSE_CODE) === 303, 'Fixture sign-in failed.');
        $catalog = performance_request($client, $manifest['address'] . '/catalog.php');
        performance_assert(curl_getinfo($client, CURLINFO_RESPONSE_CODE) === 200
            && str_contains($catalog, 'Fictional Performance User ' . $userId . '<'),
            'Authenticated catalog identity mismatch.');
        $cookies = curl_getinfo($client, CURLINFO_COOKIELIST);
        $sessionFingerprints[] = hash('sha256', implode('\n', $cookies));
        $clients[] = $client;
    }
    performance_assert(count(array_unique($sessionFingerprints)) === 5, 'Expected five independent cookie sessions.');
    unset($password, $cookies, $sessionFingerprints);
    $scenarios = [
        'normal' => ['query' => [], 'ids' => range(1, 12), 'summary' => '100 books · Page 1 of 9'],
        'search' => ['query' => ['q' => 'Textbook 02'], 'ids' => range(20, 29), 'summary' => '10 books · Page 1 of 1'],
        'combined_filters' => ['query' => ['letter' => 'F', 'author' => 'Fictional Author 1', 'category' => 1,
            'year' => 2021, 'title' => 'Textbook'], 'ids' => range(1, 56, 5), 'summary' => '20 books · Page 1 of 2'],
        'available_only' => ['query' => ['available' => '1', 'page' => 9], 'ids' => [97, 98, 99], 'summary' => '99 books · Page 9 of 9'],
        'pagination' => ['query' => ['page' => 5], 'ids' => range(49, 60), 'summary' => '100 books · Page 5 of 9'],
        'author_search' => ['query' => ['q' => 'Fictional Author 1'],
            'ids' => [1,6,10,11,16,20,21,26,30,31,36,40], 'summary' => '30 books · Page 1 of 3'],
        'title_z_a' => ['query' => ['sort' => 'title_desc'], 'ids' => range(100,89), 'summary' => '100 books · Page 1 of 9'],
        'year_newest' => ['query' => ['sort' => 'year_desc'], 'ids' => range(5,60,5), 'summary' => '100 books · Page 1 of 9'],
        'year_oldest' => ['query' => ['sort' => 'year_asc'], 'ids' => range(1,56,5), 'summary' => '100 books · Page 1 of 9'],
        'unknown_year_last' => ['query' => ['sort' => 'year_desc','page' => 9], 'ids' => [86,91,96,100], 'summary' => '100 books · Page 9 of 9'],
        'year_range' => ['query' => ['year_from'=>2022,'year_to'=>2023],
            'ids' => [2,3,7,8,12,13,17,18,22,23,27,28], 'summary' => '40 books · Page 1 of 4'],
        'filtered_page_2' => ['query' => ['q'=>'Textbook','letter'=>'F','title'=>'Textbook','author'=>'Fictional Author 1',
            'category'=>1,'year_from'=>2021,'year_to'=>2021,'available'=>'1','sort'=>'title_desc','page'=>2],
            'ids' => [36,31,26,21,16,11,6,1], 'summary' => '20 books · Page 2 of 2'],
        'unavailable_only' => ['query' => ['available'=>'0'], 'ids' => [100], 'summary' => '1 books · Page 1 of 1'],
        'empty_search' => ['query' => ['q'=>'NoMatchingFictionalTitle'], 'ids' => [], 'summary' => '0 books · Page 1 of 1'],
    ];
    $manifest['scenarios'] = $scenarios;
    $plans = [];
    foreach (['listing' => catalog_select_sql() . ' ORDER BY b.title, b.book_id LIMIT 12',
        'available_count' => 'SELECT COUNT(*) FROM (' . catalog_select_sql() . ') p WHERE available_copies > 0',
        'combined' => catalog_select_sql() . " AND b.category_id = 1 AND b.publication_year = 2021
            AND b.title LIKE '%Textbook%' AND EXISTS(SELECT 1 FROM book_authors ba JOIN authors a ON a.author_id=ba.author_id
            WHERE ba.book_id=b.book_id AND a.author_name LIKE '%Fictional Author 1%') ORDER BY b.title,b.book_id LIMIT 12",
        'year_newest' => 'SELECT * FROM (' . catalog_select_sql() . ') p ORDER BY publication_year IS NULL ASC, publication_year DESC, title ASC, book_id ASC LIMIT 12',
        'unavailable_count' => 'SELECT COUNT(*) FROM (' . catalog_select_sql() . ') p WHERE available_copies=0',
        'author_search' => catalog_select_sql() . " AND (b.title LIKE '%Fictional Author 1%' OR EXISTS(SELECT 1 FROM book_authors sx JOIN authors ax ON ax.author_id=sx.author_id WHERE sx.book_id=b.book_id AND ax.author_name LIKE '%Fictional Author 1%')) ORDER BY b.title,b.book_id LIMIT 12",
        'year_range' => catalog_select_sql() . ' AND b.publication_year>=2022 AND b.publication_year<=2023 ORDER BY b.title,b.book_id LIMIT 12',
        'own_status' => "SELECT r.book_id, r.decision, l.checked_out_at FROM borrow_requests r
            LEFT JOIN loans l ON l.request_id=r.request_id WHERE r.student_id=1 AND r.book_id IN (1,2,3,4,5,6,7,8,9,10,11,12)
            AND (r.decision='pending' OR (r.decision='approved' AND l.request_id IS NOT NULL
                AND l.returned_at IS NULL AND l.cancelled_at IS NULL
                AND (l.checked_out_at IS NOT NULL OR r.decided_at>UTC_TIMESTAMP()-INTERVAL 48 HOUR)))"] as $label => $sql) {
        $plans[$label] = ['sql' => $sql, 'explain' => $db->query('EXPLAIN ' . $sql)->fetchAll()];
    }
    performance_write_json($directory . '/query-plans.json', $plans);
    $csv = fopen($directory . '/samples.csv', 'xb');
    performance_assert($csv !== false, 'Cannot write raw timing CSV.');
    fputcsv($csv, ['mode', 'scenario', 'round', 'session', 'status', 'curl_error', 'redirects', 'bytes', 'ttfb_ms', 'total_ms', 'valid'], ',', '"', '');
    $results = [];
    foreach (['sequential', 'concurrent_5'] as $mode) {
        foreach ($scenarios as $scenario => $expected) {
            $url = $manifest['address'] . '/catalog.php?' . http_build_query($expected['query']);
            // Three validated warmup requests per participating session, all reported separately.
            $participants = $mode === 'sequential' ? [$clients[0]] : $clients;
            foreach ($participants as $index => $client) {
                for ($warm = 0; $warm < 3; $warm++) {
                    $body = performance_request($client, $url);
                    performance_assert(performance_validate($body, (int) curl_getinfo($client, CURLINFO_RESPONSE_CODE), $expected, $index + 1),
                        'Warmup failed for ' . $mode . '/' . $scenario);
                }
            }
            $samples = [];
            for ($round = 1; $round <= 30; $round++) {
                $multi = curl_multi_init();
                foreach ($participants as $client) {
                    curl_setopt_array($client, [CURLOPT_URL => $url, CURLOPT_HTTPGET => true]);
                    curl_multi_add_handle($multi, $client);
                }
                do {
                    $multiCode = curl_multi_exec($multi, $running);
                    if ($running) { curl_multi_select($multi, 1.0); }
                } while ($running && $multiCode === CURLM_OK);
                performance_assert($multiCode === CURLM_OK, 'cURL multi scheduling failed.');
                foreach ($participants as $index => $client) {
                    $body = curl_multi_getcontent($client) ?? false;
                    $status = (int) curl_getinfo($client, CURLINFO_RESPONSE_CODE);
                    $sample = ['mode' => $mode, 'scenario' => $scenario, 'round' => $round, 'session' => $index + 1,
                        'status' => $status, 'curl_error' => curl_errno($client),
                        'redirects' => (int) curl_getinfo($client, CURLINFO_REDIRECT_COUNT),
                        'bytes' => strlen((string)$body), 'ttfb_ms' => round(curl_getinfo($client, CURLINFO_STARTTRANSFER_TIME) * 1000, 3),
                        'total_ms' => round(curl_getinfo($client, CURLINFO_TOTAL_TIME) * 1000, 3),
                        'valid' => curl_errno($client) === 0 && performance_validate($body, $status, $expected, $index + 1)];
                    $samples[] = $sample;
                    fputcsv($csv, array_values($sample), ',', '"', '');
                    curl_multi_remove_handle($multi, $client);
                }
                curl_multi_close($multi);
            }
            $stats = performance_statistics($samples);
            $stats['passes_target'] = $stats['failures'] === 0 && $stats['p95_ms'] <= 2000;
            $stats['warmups'] = count($participants) * 3;
            $results[$mode][$scenario] = $stats;
            printf("%s/%s: n=%d, p95=%.3fms, avg=%.3fms, max=%.3fms, failures=%d\n",
                $mode, $scenario, $stats['samples'], $stats['p95_ms'], $stats['average_ms'], $stats['maximum_ms'], $stats['failures']);
        }
    }
    $manifest['status'] = 'completed';
    $manifest['completed_at_utc'] = gmdate(DateTimeInterface::ATOM);
    $manifest['results'] = $results;
    $manifest['passes_target'] = true;
    foreach ($results as $modeResults) {
        foreach ($modeResults as $stats) { $manifest['passes_target'] = $manifest['passes_target'] && $stats['passes_target']; }
    }
    $manifest['limitations'] = [
        'HTML response TTFB and total transfer over loopback, not browser rendering or asset loading.',
        'Five independent HTTP cookie sessions with simultaneously scheduled requests, not five interactive browsers.',
        'Windows PHP built-in server uses one process; concurrent response times include its request queue.',
        'Localhost measurements do not establish production capacity or hosted visible-page performance.',
        'All covers use the existing missing-cover placeholder; no upload or image-processing benchmark.',
        'Dataset is fixed and scenarios run in the documented order, not an endurance or arrival-rate load test.',
    ];
    performance_write_json($directory . '/run.json', $manifest);
    echo 'Evidence retained: ' . $directory . "\nDatabase retained: " . $name . "\n";
    $exitCode = $manifest['passes_target'] ? 0 : 1;
} catch (Throwable $exception) {
    if (isset($db) && $db->inTransaction()) { $db->rollBack(); }
    $manifest['status'] = 'failed';
    $manifest['failure_type'] = get_class($exception);
    // Avoid serializing PDO messages which can contain connection or fixture data.
    $manifest['failure'] = $exception instanceof PDOException ? 'Database operation failed; inspect local configuration and schema.' : $exception->getMessage();
    performance_write_json($directory . '/run.json', $manifest);
    fwrite(STDERR, $manifest['failure'] . "\nEvidence retained: " . $directory . "\n");
    $exitCode = 1;
} finally {
    if (is_resource($csv)) { fclose($csv); }
    foreach ($clients as $client) { curl_close($client); }
    if (is_resource($server)) {
        proc_terminate($server);
        proc_close($server);
        $manifest['server_stopped'] = true;
        performance_write_json($directory . '/run.json', $manifest);
    }
    putenv('KAAKLAT_PERFORMANCE_DB');
    putenv('KAAKLAT_PERFORMANCE_RUN');
}
exit($exitCode);

function curl_exec_for_performance_probe(CurlHandle $client, string $url): string|false
{
    curl_setopt_array($client, [CURLOPT_URL => $url, CURLOPT_HTTPGET => true]);
    return curl_exec($client);
}
