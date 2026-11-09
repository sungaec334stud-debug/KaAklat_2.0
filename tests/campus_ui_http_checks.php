<?php

// Included by the scratch HTTP runner. Checks semantics, not screenshots or keyboard behavior.
declare(strict_types=1);
$portalStudent = curl_init();
curl_setopt_array($portalStudent, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);
[$code,$html] = accept_http($portalStudent, '/login.php');
[$code] = accept_http($portalStudent, '/login.php', ['username'=>'accept_student','password'=>$password,'csrf_token'=>accept_token($html)]);
accept_check($code === 303, 'Campus student fixture sign-in');
$portalCases = [
    [$portalStudent, '/profile.php', ['/', '/catalog.php', '/requests.php', '/profile.php'], 'Student portal'],
    [$clients['librarian'], '/pickups.php', ['/', '/catalog.php', '/review_requests.php', '/pickups.php', '/returns.php', '/manage_books.php', '/students.php', '/recover_account.php'], 'Librarian portal'],
    [$clients['admin'], '/staff.php', ['/', '/catalog.php', '/staff.php', '/students.php', '/audit.php', '/recover_account.php'], 'Administrator portal'],
];
foreach ($portalCases as [$client,$path,$expectedLinks,$roleText]) {
    $expectedLinks[] = '/about.php';
    $expectedLinks[] = '/logout.php';
    [$code,$html] = accept_http($client, $path);
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $anchors = $xpath->query('//nav[@aria-label="Main navigation"]/a');
    $urls = [];
    foreach ($anchors as $anchor) {
        $urls[] = $anchor->getAttribute('href');
        accept_check(trim($anchor->textContent) !== '', 'Campus navigation retains visible link name');
        $icons = $xpath->query('.//*[local-name()="svg"]', $anchor);
        accept_check($icons->length === 1 && $icons->item(0)->getAttribute('aria-hidden') === 'true'
            && $icons->item(0)->getAttribute('focusable') === 'false', 'Campus navigation icon stays decorative');
    }
    accept_check($code === 200 && $urls === $expectedLinks, 'Campus role navigation destinations unchanged');
    accept_check($xpath->query('//nav[@aria-label="Main navigation"]/a[@aria-current="page"]')->length === 1, 'Campus current route remains unique');
    accept_check(str_contains($html, $roleText) && str_contains($html, 'href="#main-content"') && str_contains($html, 'id="main-content" tabindex="-1"'), 'Campus context and skip-to-main target retained');
}
[$code,$html] = accept_http($portalStudent, '/');
accept_check($code === 200 && str_contains($html, 'Only your records') && str_contains($html, 'summary-panel')
    && str_contains($html, '/requests.php?status=awaiting_pickup'), 'Campus dashboard uses own linked shared summary');
$dashboardCases = [
    [$portalStudent, 'Acceptance Student', ['/requests.php?status=pending', '/requests.php?status=awaiting_pickup', '/requests.php?status=active', '/catalog.php']],
    [$clients['librarian'], 'Acceptance Librarian', ['/review_requests.php', '/pickups.php', '/review_requests.php', '/pickups.php']],
    [$clients['admin'], 'Acceptance Admin', ['/staff.php', '/audit.php']],
];
foreach ($dashboardCases as [$client, $displayName, $expectedLinks]) {
    [$code, $html] = accept_http($client, '/');
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $links = [];
    foreach ($xpath->query('//div[@class="dashboard"]//a') as $link) {
        $links[] = $link->getAttribute('href');
    }
    accept_check($code === 200 && $links === $expectedLinks && str_contains($html, 'Welcome back, ' . $displayName),
        'Dashboard shows the role’s links and personalized greeting');
    accept_check($xpath->query('//nav[@aria-label="Main navigation"]/a[@href="/logout.php"]')->length === 1,
        'Dashboard provides one shared logout confirmation link');
}
$db->exec("INSERT INTO books (title, description) VALUES ('Fictional own pickup', 'Dashboard fixture'), ('Fictional other pickup', 'Dashboard fixture')");
$dashboardBooks = $db->query("SELECT book_id FROM books WHERE description = 'Dashboard fixture' ORDER BY book_id")->fetchAll(PDO::FETCH_COLUMN);
foreach ($dashboardBooks as $index => $bookId) {
    $db->prepare('INSERT INTO book_copies (book_id, copy_code) VALUES (?, ?)')->execute([$bookId, 'DASH-TEST-' . $index]);
    $copyId = $db->lastInsertId();
    $db->prepare("INSERT INTO borrow_requests (student_id, book_id, requested_days, request_reason, decision, approved_days, requested_at, decided_at, decided_by)
        VALUES (?, ?, 7, 'Private dashboard fixture reason', 'approved', 7, UTC_TIMESTAMP() - INTERVAL 1 DAY, UTC_TIMESTAMP() - INTERVAL ? HOUR, 3)")
        ->execute([$index + 1, $bookId, $index + 1]);
    $db->prepare('INSERT INTO loans (request_id, copy_id) VALUES (?, ?)')->execute([$db->lastInsertId(), $copyId]);
}
[$code, $html] = accept_http($portalStudent, '/');
accept_check($code === 200 && str_contains($html, 'Fictional own pickup') && !str_contains($html, 'Fictional other pickup')
    && !str_contains($html, 'Private dashboard fixture reason') && str_contains($html, 'Pick up by'), 'Next up shows only the student’s live pickup');
[$code, $html] = accept_http($clients['librarian'], '/');
accept_check($code === 200 && strpos($html, 'Fictional other pickup') < strpos($html, 'Fictional own pickup')
    && str_contains($html, 'Fictional other pickup') && !str_contains($html, 'Private dashboard fixture reason'), 'Librarian sees live pickups in deadline order');
record_audit_event($db, 4, 'staff.created', 'users', 3, 'Private audit fixture summary');
record_audit_event($db, 4, 'account.reset_issued', 'users', 3, 'Private recovery fixture summary');
[$code, $html] = accept_http($clients['admin'], '/');
accept_check($code === 200 && str_contains($html, 'Staff account created') && !str_contains($html, 'Fictional own pickup')
    && !str_contains($html, 'Private audit fixture summary') && !str_contains($html, 'Private recovery fixture summary')
    && !str_contains($html, 'account.reset_issued'), 'Administrator preview displays allowlisted account metadata only');
[$code,$html] = accept_http($anonymous, '/login.php');
accept_check($code === 200 && !str_contains($html, 'portal-topbar') && str_contains($html, 'name="csrf_token"'), 'Campus signed-out shell retains login form without account chrome');
curl_close($portalStudent);
