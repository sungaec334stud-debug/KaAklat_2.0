<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
$tests = ['foundation_test.php','auth_unit_test.php','student_accounts_test.php','catalog_test.php','catalog_filters_test.php','catalog_management_test.php','auth_regression_test.php','student_accounts_integration_test.php','catalog_integration_test.php','catalog_management_integration_test.php'];
$tests[] = 'auth_hardening_test.php';
$tests[] = 'borrow_requests_test.php';
$tests[] = 'request_decisions_test.php';
$tests[] = 'pickups_test.php';
$tests[] = 'dashboard_test.php';
$tests[] = 'returns_test.php';
$tests[] = 'request_ui_test.php';
$tests[] = 'interface_refinement_test.php';
$tests[] = 'approved_cancellation_test.php';
$tests[] = 'staff_accounts_test.php';
$tests[] = 'account_access_test.php';
$tests[] = 'local_backup_test.php';
$tests[] = 'student_profile_test.php';
$tests[] = 'portal_ui_test.php';
$tests[] = 'catalog_pagination_test.php';
$tests[] = 'catalog_status_test.php';
foreach ($tests as $test) {
    $process = proc_open([PHP_BINARY, __DIR__ . '/' . $test], [0=>STDIN,1=>STDOUT,2=>STDERR], $pipes);
    if (!is_resource($process) || proc_close($process) !== 0) { fwrite(STDERR, "FAIL: $test\n"); exit(1); }
}
echo count($tests) . " regression scripts passed. Database writes used temporary fixtures only.\n";
