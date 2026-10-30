<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

start_application_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Invalid request.');
}

clear_login_session();
flash_set('success', 'You are now logged out.');
redirect('/login.php');
