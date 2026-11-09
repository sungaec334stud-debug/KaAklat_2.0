<?php
// Loopback acceptance router. NEVER deploy. Requires explicit scratch DB environment.
declare(strict_types=1);
$name=getenv('KAAKLAT_ACCEPTANCE_DB')?:'';
if(PHP_SAPI!=='cli-server' || ($_SERVER['REMOTE_ADDR']??'')!=='127.0.0.1' || !preg_match('/^kaaklat_acceptance_[a-f0-9]{12}$/D',$name)) { http_response_code(404); exit; }
require_once dirname(__DIR__).'/app/bootstrap.php';
$appConfig['database']['database']=$name;
$appConfig['session_name']='kaaklat_acceptance';
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$allowed=['/','/index.php','/login.php','/logout.php','/catalog.php','/requests.php','/pickups.php','/returns.php','/review_requests.php','/cover.php','/staff.php','/manage_books.php','/students.php','/register.php','/health.php','/recover_account.php','/reset_password.php','/audit.php','/profile.php'];
$allowed[] = '/about.php';
$allowed[] = '/settings.php';
if(str_starts_with($path,'/assets/') && preg_match('~^/assets/[a-zA-Z0-9._-]+$~D',$path)) { return false; }
if(!in_array($path,$allowed,true)) { http_response_code(404); exit; }
require dirname(__DIR__).'/public/'.($path==='/'?'index.php':basename($path));
