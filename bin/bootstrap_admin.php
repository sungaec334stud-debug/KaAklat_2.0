<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') { http_response_code(403); exit(1); }
require_once dirname(__DIR__).'/app/bootstrap.php';
require_once dirname(__DIR__).'/app/staff_accounts.php';
require_once __DIR__.'/operator_password.php';
if ($argc!==3) { fwrite(STDERR,"Usage: php bin/bootstrap_admin.php <new-username> <display-name>\n"); exit(1); }
try {
    $password=operator_password();
    bootstrap_administrator(database_connection(application_config()['database']),$argv[1],$argv[2],$password);
    unset($password);
    echo "Administrator created. You may sign in.\n";
} catch (InvalidArgumentException $e) { fwrite(STDERR,$e->getMessage()."\n"); exit(1); }
catch (Throwable) { fwrite(STDERR,"Administrator setup failed. No success is claimed; inspect private configuration and account state.\n"); exit(1); }
