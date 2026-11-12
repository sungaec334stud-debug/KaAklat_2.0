<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/helpers.php';
require_once dirname(__DIR__) . '/views/portal_ui.php';
require_once dirname(__DIR__) . '/views/about.php';
require_once dirname(__DIR__) . '/views/audit_ui.php';
require_once dirname(__DIR__) . '/app/catalog.php';
require_once dirname(__DIR__) . '/views/dashboard.php';

function design_check(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException($message); }
}

// Preserve user-requested factual information, not a shorter mock About substitute.
$about = about_html();
foreach (['Arellano Street', 'Pantal', 'Mission and vision', 'Competence, Unity, Dynamism',
    'not a statement of official university library policy', 'Emmanuel C. Sunga',
    'Third-year Computer Science', 'emmanuelsunga200527@gmail.com', 'emmanuel-sunga.jpg',
    'https://udd.edu.ph/', 'Create your account', 'Return it on time'] as $fact) {
    design_check(str_contains($about, $fact), 'About information retained: ' . $fact);
}
design_check(substr_count($about, '<li>') === 6, 'All six borrowing steps retained');
design_check(audit_event_label('student.activate') === 'Student account activated', 'Readable event label');
design_check(audit_event_label('unrecognized.event') === 'Recorded activity', 'Unknown codes never invent a decision');
design_check(!str_contains(audit_event_label('<script>'), '<script>'), 'Unknown event cannot inject markup');
foreach (['/returns.php'=>'M8 7H3', '/settings.php'=>'M3 6h18', '/catalog.php'=>'M12 5C8 2'] as $route=>$path) {
    design_check(str_contains(portal_icon($route), $path), 'Accepted mock icon: ' . $route);
}
$books = [];
for ($id = 1; $id <= 5; $id++) {
    $books[] = ['book_id'=>$id, 'title'=>'<Unsafe title>', 'authors'=>'<Unsafe author>',
        'category_name'=>'<Unsafe category>', 'publication_year'=>2020,
        'cover_path'=>'private-name-must-not-be-output.jpg', 'available_copies'=>2];
}
$preview = dashboard_catalog_html($books);
design_check(substr_count($preview, '<article>') === 4, 'Discovery is bounded to four titles');
design_check(!str_contains($preview, '<Unsafe') && str_contains($preview, '&lt;Unsafe title&gt;'), 'Discovery metadata escaped');
design_check(!str_contains($preview, 'private-name') && str_contains($preview, '/cover.php?id=1'), 'Discovery retains protected cover endpoint');
design_check(str_contains(dashboard_catalog_html([]), 'No catalog books'), 'Truthful empty catalog preview');
$staffPanel = dashboard_account_panel_html('administrator', ['staff'=>[
    ['display_name'=>'<Unsafe staff>', 'username'=>'staff"&name', 'role'=>'librarian', 'account_status'=>'active'],
]]);
design_check(!str_contains($staffPanel, '<Unsafe') && str_contains($staffPanel, '&lt;Unsafe staff&gt;'), 'Staff access names escaped');
design_check(str_contains($staffPanel, '/staff.php?q=staff%22%26name'), 'Staff directory link uses encoded username');
$studentPanel = dashboard_account_panel_html('librarian', ['pending_count'=>0, 'students'=>[]]);
design_check(str_contains($studentPanel, 'No students are waiting') && !str_contains($studentPanel, 'Registered'), 'Empty verification panel never fabricates student information');
design_check(str_contains(dashboard_desk_activity_html([]), 'No recent circulation actions'), 'Own-activity empty state');
echo "PASS: Accepted navigation icons, audit labels and complete About information.\n";
