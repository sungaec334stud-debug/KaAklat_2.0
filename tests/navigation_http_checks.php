<?php
// Included only by the fictional HTTP harness. Navigation must not grant permissions.
foreach ($clients as $navRole => $navClient) {
    [$navCode, $navHtml] = accept_http($navClient, '/about.php');
    accept_check($navCode === 200 && substr_count($navHtml, 'id="navigation-toggle"') === 1 && substr_count($navHtml, 'id="navigation-drawer"') === 1, "$navRole has one accessible drawer and trigger");
    accept_check(str_contains($navHtml, 'aria-label="Open navigation"') && str_contains($navHtml, 'aria-expanded="false"') && str_contains($navHtml, 'aria-controls="navigation-drawer"'), "$navRole icon-only trigger has accessible state");
    accept_check(substr_count($navHtml, '<nav class="header-nav"') === 1 && str_contains($navHtml, 'href="/about.php" aria-current="page"'), "$navRole retains one role-aware current navigation");
    accept_check(str_contains($navHtml, 'class="mobile-current-page">About KaAklat</strong>') && !str_contains($navHtml, 'Design preview'), "$navRole receives actual current-page title");
    if ($navRole === 'student' || $navRole === 'other') {
        accept_check(!str_contains($navHtml, 'href="/staff.php"') && !str_contains($navHtml, 'href="/pickups.php"'), 'Student drawer excludes staff destinations');
    } elseif ($navRole === 'admin') {
        accept_check(!str_contains($navHtml, 'href="/pickups.php"') && str_contains($navHtml, 'href="/staff.php"'), 'Administrator drawer retains role boundary');
    } else {
        accept_check(!str_contains($navHtml, 'href="/staff.php"') && str_contains($navHtml, 'href="/pickups.php"'), 'Librarian drawer retains role boundary');
    }
}
$navGuest = curl_init();
curl_setopt_array($navGuest, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);
[$navCode,$navHtml] = accept_http($navGuest, '/login.php');
accept_check($navCode === 200 && !str_contains($navHtml, 'id="navigation-toggle"') && !str_contains($navHtml, 'id="navigation-drawer"'), 'Guest entry navigation is unchanged');
curl_close($navGuest);
