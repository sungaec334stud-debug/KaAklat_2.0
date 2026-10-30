<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/student_accounts.php';

function check(bool $condition): void {
    if (!$condition) { throw new RuntimeException('Assertion failed'); }
}
foreach ([['pending', 'activate', 'active'], ['active', 'suspend', 'suspended'], ['suspended', 'reactivate', 'active']] as [$from, $action, $to]) {
    check(student_status_transition($from, $action) === $to);
}
foreach ([['active', 'activate'], ['pending', 'reactivate'], ['archived', 'activate'], ['pending', 'administrator'], ['suspended', 'suspend']] as [$from, $action]) {
    try { student_status_transition($from, $action); throw new RuntimeException('Invalid transition accepted'); }
    catch (InvalidArgumentException) {}
}
foreach ([null, ['role' => 'student', 'account_status' => 'active'], ['role' => 'administrator', 'account_status' => 'active'], ['role' => 'librarian', 'account_status' => 'suspended']] as $actor) {
    try { require_active_librarian($actor); throw new RuntimeException('Unauthorized actor accepted'); }
    catch (DomainException) {}
}
require_active_librarian(['role' => 'librarian', 'account_status' => 'active']);
echo "Student account policy tests passed.\n";
