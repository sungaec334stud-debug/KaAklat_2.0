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
[$code,$html] = accept_http($anonymous, '/login.php');
accept_check($code === 200 && !str_contains($html, 'portal-topbar') && str_contains($html, 'name="csrf_token"'), 'Campus signed-out shell retains login form without account chrome');
curl_close($portalStudent);
