<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { exit(1); }
$root = dirname(__DIR__);
$fixture = $root . '/tmp/startup-guard-' . bin2hex(random_bytes(6));
mkdir($fixture . '/bin', 0700, true);
mkdir($fixture . '/app', 0700);
mkdir($fixture . '/private', 0700);
copy($root . '/bin/check_local_pilot.php', $fixture . '/bin/check_local_pilot.php');
copy($root . '/app/config.php', $fixture . '/app/config.php');
$checks = 0;
function guard_test(string $fixture, ?array $config, int $expectedExit): void
{
    global $checks;
    if ($config !== null) {
        file_put_contents($fixture . '/private/config.local.php', '<?php return ' . var_export($config, true) . ';');
    }
    $process = proc_open([PHP_BINARY, $fixture . '/bin/check_local_pilot.php'],
        [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
    if (!is_resource($process)) { throw new RuntimeException('Guard process unavailable'); }
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    $exit = proc_close($process);
    if ($exit !== $expectedExit || !str_contains($output, $expectedExit === 0 ? 'configuration verified' : 'Startup stopped')) {
        throw new RuntimeException('Unexpected startup guard result');
    }
    if (str_contains($output, 'fictional_secret_never_echo')) { throw new RuntimeException('Guard exposed credentials'); }
    $checks++;
}
guard_test($fixture, null, 1);
$config = ['app_env'=>'local','app_name'=>'Fictional','timezone'=>'Asia/Manila','session_name'=>'fictional',
    'database'=>['database'=>'kaaklat_dev','password'=>'fictional_secret_never_echo']];
guard_test($fixture, $config, 1);
$config['database']['database'] = 'kaaklat_pilot';
guard_test($fixture, $config, 0);
$config['app_env'] = 'production';
guard_test($fixture, $config, 1);
$config['app_env'] = 'local'; $config['database']['database'] = 'other_database';
guard_test($fixture, $config, 1);
echo "PASS: $checks isolated startup guard cases; no database connection or private config used. Fictional fixtures retained.\n";
