<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/catalog_management.php';
function management_check(bool $condition): void { if (!$condition) { throw new RuntimeException('Management assertion failed'); } }
function management_reject(callable $operation): void {
    try { $operation(); } catch (InvalidArgumentException) { return; }
    throw new RuntimeException('Invalid input accepted');
}
$valid = catalog_book_input(['title' => ' Example ', 'description' => ' A description ', 'authors' => "First Author\nSecond Author", 'quantity' => '3'], true);
management_check($valid['title'] === 'Example' && count($valid['authors']) === 2 && $valid['quantity'] === 3);
foreach (['0', '-1', '101', '1.5', [], 'abc'] as $quantity) { management_reject(fn () => catalog_quantity($quantity)); }
foreach ([['title' => ''], ['authors' => ''], ['description' => str_repeat('x', 2001)], ['title' => []]] as $override) {
    management_reject(fn () => catalog_book_input(array_replace(['title' => 'Title', 'description' => 'Description', 'authors' => 'Author', 'quantity' => '1'], $override), true));
}
foreach (['<svg/>', '<?php echo 1;', str_repeat('x', 2097153)] as $bytes) { management_reject(fn () => catalog_decode_cover($bytes)); }
$image = imagecreatetruecolor(12, 18);
ob_start(); imagepng($image); $png = ob_get_clean();
$decoded = catalog_decode_cover($png);
management_check(imagesx($decoded) === 12 && imagesy($decoded) === 18);
$wide = imagecreatetruecolor(4001, 1); ob_start(); imagepng($wide); $bad = ob_get_clean();
management_reject(fn () => catalog_decode_cover($bad));
management_reject(fn () => catalog_uploaded_cover(['error' => UPLOAD_ERR_PARTIAL]));
management_reject(fn () => catalog_uploaded_cover(['error' => UPLOAD_ERR_OK, 'tmp_name' => __FILE__]));
echo "Catalog management validation/image tests passed.\n";
