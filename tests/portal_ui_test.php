<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { exit(1); }
require_once dirname(__DIR__) . '/app/helpers.php';
$helper = dirname(__DIR__) . '/views/portal_ui.php';
if (!is_file($helper)) { fwrite(STDERR, "FAIL: campus portal presentation helper is not implemented.\n"); exit(1); }
require_once $helper;
$checks = 0;
function portal_check(bool $ok, string $label): void
{
    global $checks;
    if (!$ok) { throw new RuntimeException($label); }
    $checks++;
}
foreach (['/','/catalog.php','/requests.php','/profile.php','/review_requests.php','/pickups.php','/returns.php','/manage_books.php','/students.php','/staff.php','/audit.php','/recover_account.php'] as $route) {
    $icon = portal_icon($route);
    $dom = new DOMDocument();
    $dom->loadXML($icon);
    portal_check($dom->documentElement->tagName === 'svg' && $dom->documentElement->getAttribute('aria-hidden') === 'true'
        && $dom->documentElement->getAttribute('focusable') === 'false', 'Decorative nonfocusable icon: ' . $route);
    portal_check(!str_contains($icon, '<script') && !str_contains($icon, '<title') && !str_contains($icon, '<a '), 'Icon cannot change link name or add actions');
}
portal_check(portal_icon('/<script>alert(1)</script>') === '', 'Unknown route cannot inject icon content');
$summary = portal_summary_html([['<Unsafe title>',3,'/requests.php?status=pending']], '<Unsafe heading>', 'Own records');
portal_check(str_contains($summary, '&lt;Unsafe title&gt;') && str_contains($summary, '&lt;Unsafe heading&gt;') && !str_contains($summary, '<Unsafe'), 'Summary labels escaped');
portal_check(str_contains($summary, 'href="/requests.php?status=pending"') && str_contains($summary, '<strong>3</strong>'), 'Original count/destination retained');
portal_check(!str_contains($summary, '<form') && !str_contains($summary, 'onclick'), 'Summary introduces no mutation or JS dependency');
echo "PASS: $checks campus portal presentation/accessibility checks.\n";
