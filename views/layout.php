<?php

declare(strict_types=1);

require_once __DIR__ . '/portal_ui.php';
require_once __DIR__ . '/pagination.php';

function render_page(string $title, string $content, bool $landing = false): void
{
    $safeTitle = h($title);
    $appName = h((string) application_config()['app_name']);
    $flashes = '';
    // Presentation uses a fresh database-backed identity, never a session role hint.
    $navigationUser=null;
    if(isset($_SESSION['user_id'],$_SESSION['session_version'])) {
        try { $navigationUser=current_user(database_connection(application_config()['database'])); }
        catch(Throwable) { /* Fail closed if identity cannot be verified. */ }
    }
    $links = $navigationUser!==null ? ['/' => 'Dashboard', '/catalog.php' => 'Book catalog'] : ['/login.php' => 'Log in', '/register.php' => 'Create an account'];
    if(($navigationUser['role']??null)==='student') { $links['/requests.php']='My requests & loans'; $links['/profile.php']='My Profile'; }
    if(($navigationUser['role']??null)==='librarian' && $navigationUser['account_status']==='active') {
        $links += ['/review_requests.php'=>'Review requests','/pickups.php'=>'Pickups','/returns.php'=>'Returns','/manage_books.php'=>'Manage books','/students.php'=>'Students'];
    }
    if (($navigationUser['role']??null)==='administrator' && $navigationUser['account_status']==='active') { $links += ['/staff.php'=>'Staff accounts','/students.php'=>'Students','/audit.php'=>'Audit records']; }
    if (in_array($navigationUser['role']??null,['librarian','administrator'],true) && $navigationUser['account_status']==='active') { $links['/recover_account.php']='Account recovery'; }
    $links['/about.php'] = 'About';
    if ($navigationUser !== null) { $links['/logout.php'] = 'Log out'; }
    // Presentation grouping never adds destinations or grants role permissions.
    if ($navigationUser !== null) {
        $order = $navigationUser['role'] === 'librarian'
            ? ['/', '/catalog.php', '/manage_books.php', '/review_requests.php', '/pickups.php', '/returns.php', '/students.php', '/recover_account.php', '/about.php', '/logout.php']
            : array_keys($links);
        $links = array_replace(array_intersect_key(array_fill_keys($order, ''), $links), $links);
    }
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $navigation = '';
    $lastGroup = '';
    foreach ($links as $url => $label) {
        if ($navigationUser !== null) {
            $group = in_array($url, ['/review_requests.php', '/pickups.php', '/returns.php'], true) ? 'Circulation'
                : (in_array($url, ['/students.php', '/staff.php', '/audit.php', '/recover_account.php'], true) ? 'Accounts & security'
                : (in_array($url, ['/about.php', '/logout.php'], true) ? 'Help & account' : 'Library'));
            if ($group !== $lastGroup) { $navigation .= '<span class="nav-group-label">' . h($group) . '</span>'; $lastGroup = $group; }
        }
        $current = $path === $url || ($url === '/' && $path === '/index.php');
        $currentMarker = $current ? '<span class="nav-current-marker" aria-hidden="true"></span>' : '';
        $navigation .= '<a href="' . h($url) . '"' . ($current ? ' aria-current="page"' : '') . '>'
            . $currentMarker . ($navigationUser !== null ? portal_icon($url) : '') . '<span>' . h($label) . '</span></a>';
    }

    foreach (['success', 'error', 'info'] as $type) {
        $message = flash_pull($type);

        if ($message !== null) {
            $flashes .= '<p class="flash flash-' . h($type) . '" role="status">' . h($message) . '</p>';
        }
    }

    $bodyClass=$navigationUser!==null?'is-authenticated':'is-guest';
    $workspaceLabel = '';
    $identity = '';
    $portalBar = '';
    $mobileNavigation = '';
    $navigationDialog = '';
    $navigationAssets = '';
    $themeToggle = appearance_toggle_html();
    $guestThemeToggle = $navigationUser === null ? $themeToggle : '';
    $pageNavigationAssets = '<link rel="stylesheet" href="/assets/page-navigation.css?v=20261123-system"><script src="/assets/page-navigation.js?v=20261123-system"></script>';
    if ($navigationUser !== null) {
        $role = match ($navigationUser['role']) { 'student'=>'Student', 'librarian'=>'Librarian', 'administrator'=>'Administrator', default=>'Account' };
        $status = $navigationUser['account_status'] === 'pending' ? 'Pending activation' : ucfirst($navigationUser['account_status']);
        $workspaceLabel = '<p class="workspace-label">' . h($role) . ' workspace</p>';
        $identity = '<div class="sidebar-identity"><strong>' . h($navigationUser['display_name']) . '</strong><span>' . h($role) . ' · ' . h($status) . '</span></div>';
        $portalBar = '<div class="portal-topbar"><div class="portal-location"><span>' . h($role) . ' portal</span><span aria-hidden="true">/</span><strong>' . $safeTitle . '</strong></div><div class="portal-header-actions"><span class="portal-account-status">' . h($status) . '</span>' . $themeToggle . '</div></div>';
        $mobileNavigation = '<div class="mobile-navigation-bar" hidden><div class="mobile-navigation-row">'
            . '<button id="navigation-toggle" type="button" aria-label="Open navigation" aria-controls="navigation-drawer" aria-expanded="false">'
            . '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 6h18 M3 12h18 M3 18h18"/></svg></button>'
            . '<a class="mobile-brand" href="/"><img src="/assets/kaaklat-logo.svg" width="36" height="36" alt=""><span>' . $appName . '</span></a>' . $themeToggle . '</div>'
            . '<div class="mobile-navigation-location"><div><span class="mobile-portal-label">' . h($role) . ' portal</span>'
            . '<strong class="mobile-current-page">' . $safeTitle . '</strong></div><span class="mobile-account-status">' . h($status) . '</span></div></div>';
        $navigationDialog = '<dialog id="navigation-drawer" aria-label="Main navigation"><button type="button" class="navigation-close" aria-label="Close navigation">'
            . '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m6 6 12 12 M6 18 18 6"/></svg></button></dialog>';
        $navigationAssets = '<link rel="stylesheet" href="/assets/navigation.css?v=20261115"><script src="/assets/navigation.js?v=20261115" defer></script>';

    }
    $entryAssets = '';
    if ($landing || in_array($path, ['/login.php', '/register.php', '/reset_password.php'], true)) {
        $bodyClass .= ' entry-page';
        $entryAssets = '<link rel="stylesheet" href="/assets/entry.css?v=20261123-focus"><script src="/assets/entry.js?v=20261123-system" defer></script>';
    }
    $workspaceRoutes = ['/staff.php', '/students.php', '/audit.php', '/recover_account.php',
        '/about.php', '/logout.php', '/review_requests.php', '/pickups.php', '/returns.php',
        '/manage_books.php', '/requests.php', '/profile.php'];
    $workspaceAssets = '';
    if (in_array($path, $workspaceRoutes, true)) {
        $bodyClass .= ' workspace-screen';
        $workspaceAssets = '<link rel="stylesheet" href="/assets/workspace.css?v=20261124-queue-filters">'
            . '<script src="/assets/workspace.js?v=20261124-queue-filters" defer></script>';
    }
    echo <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle} | {$appName}</title>
    <link rel="icon" href="/assets/kaaklat-logo.svg" type="image/svg+xml">
    <script src="/assets/theme.js?v=20261121-header"></script>
    <link rel="stylesheet" href="/assets/app.css?v=20261123-focus">
    {$workspaceAssets}
    {$entryAssets}
    {$navigationAssets}
    {$pageNavigationAssets}
    <noscript><link rel="stylesheet" href="/assets/native-navigation.css"></noscript>
    <link rel="stylesheet" href="/assets/motion.css?v=20261123-system">
    <script src="/assets/forms.js" defer></script>
    <script src="/assets/disclosure.js?v=20261123-system" defer></script>
</head>
<body class="{$bodyClass}">
    <a class="skip-link" href="#main-content">Skip to content</a>
    {$mobileNavigation}
    <header class="site-header">
        <div class="header-inner">
        <div class="site-branding">
            <a class="brand" href="/">
                <img class="brand-mark" src="/assets/kaaklat-logo.svg" width="48" height="48" alt="">
                <span class="brand-wordmark">{$appName}</span>
            </a>
            <div class="brand-university">
                <img class="university-seal" src="/assets/universidad-de-dagupan-seal.png" width="22" height="22" alt="">
                <span>Universidad de Dagupan</span>
            </div>
        </div>
        {$workspaceLabel}
        <nav class="header-nav" aria-label="Main navigation">{$navigation}</nav>
        {$identity}
        {$guestThemeToggle}
        </div>
    </header>
    {$navigationDialog}
    <p id="appearance-status" class="visually-hidden" role="status" aria-live="polite"></p>
    <main class="container" id="main-content" tabindex="-1">
        {$portalBar}
        {$flashes}
        {$content}
    </main>
    <footer class="site-footer">KaAklat · A place for books, learning, and discovery.</footer>
</body>
</html>
HTML;
}
