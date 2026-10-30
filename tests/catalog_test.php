<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/catalog.php';
function catalog_assert(bool $ok): void { if (!$ok) { throw new RuntimeException('Catalog assertion failed'); } }
foreach (['student', 'librarian', 'administrator'] as $role) {
    catalog_assert(catalog_access_allowed(['role' => $role, 'account_status' => 'active']));
}
catalog_assert(catalog_access_allowed(['role' => 'student', 'account_status' => 'pending']));
foreach ([null, ['role' => 'student', 'account_status' => 'suspended'], ['role' => 'student', 'account_status' => 'archived']] as $user) {
    catalog_assert(!catalog_access_allowed($user));
}
catalog_assert(catalog_parameters(['q' => "  O'Brien  ", 'page' => '2']) === ["O'Brien", 2]);
foreach ([['q' => []], ['q' => str_repeat('a', 101)], ['page' => '0'], ['page' => []]] as $input) {
    try { catalog_parameters($input); throw new RuntimeException('Invalid input accepted'); } catch (InvalidArgumentException) {}
}
echo "Catalog policy/input tests passed.\n";
