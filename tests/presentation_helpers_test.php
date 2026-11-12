<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
require_once dirname(__DIR__) . '/app/helpers.php';
require_once dirname(__DIR__) . '/views/portal_ui.php';
require_once dirname(__DIR__) . '/views/time.php';
function presentation_check(bool $ok, string $message): void {
    if (!$ok) { throw new RuntimeException($message); }
}
presentation_check(display_manila_time('2024-02-29 16:00:00') === 'Mar 1, 2024 12:00 am', 'Leap-day UTC to Manila midnight');
presentation_check(display_manila_time('2026-12-31 16:00:01', 'M j, Y g:i:s a') === 'Jan 1, 2027 12:00:01 am', 'Year rollover and audit seconds');
presentation_check(display_manila_time('2026-11-01 16:00:00', 'M j, g:i A') === 'Nov 2, 12:00 AM', 'Dashboard format retained');
try { display_manila_time('not a timestamp'); throw new LogicException('Invalid date accepted'); }
catch (Exception $e) { presentation_check(!$e instanceof LogicException, 'Invalid-date exception preserved'); }
$counts=['pending'=>2,'awaiting'=>3,'active'=>5,'overdue'=>1];
presentation_check(student_summary_items($counts,'Ready to collect') === [
    ['Pending requests',2,'/requests.php?status=pending'],
    ['Ready to collect',3,'/requests.php?status=awaiting_pickup'],
    ['Active loans',4,'/requests.php?status=active'],
], 'Same own-count mapping, overdue subtraction and routes');
presentation_check(student_summary_items($counts)[1][0] === 'Ready for pickup', 'Profile wording retained');
presentation_check(student_overdue_notice_html(0) === '', 'No zero-count warning');
presentation_check(student_overdue_notice_html(1) === '<p class="flash flash-error" role="status">You have 1 <a href="/requests.php?status=overdue">overdue loan</a>. Please speak with your librarian.</p>', 'Singular notice exact markup and link');
presentation_check(str_contains(student_overdue_notice_html(2), '>overdue loans</a>'), 'Plural notice');
echo "PASS: Presentation time boundaries, original formats, summary counts/routes and exact overdue notices.\n";
