<?php

declare(strict_types=1);

require_once __DIR__ . '/portal_ui.php';

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
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $navigation = '';
    foreach ($links as $url => $label) {
        $current = $path === $url || ($url === '/' && $path === '/index.php');
        $navigation .= '<a href="' . h($url) . '"' . ($current ? ' aria-current="page"' : '') . '>'
            . ($navigationUser !== null ? portal_icon($url) : '') . '<span>' . h($label) . '</span></a>';
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
    if ($navigationUser !== null) {
        $role = match ($navigationUser['role']) { 'student'=>'Student', 'librarian'=>'Librarian', 'administrator'=>'Administrator', default=>'Account' };
        $status = $navigationUser['account_status'] === 'pending' ? 'Pending activation' : ucfirst($navigationUser['account_status']);
        $workspaceLabel = '<p class="workspace-label">' . h($role) . ' workspace</p>';
        $identity = '<div class="sidebar-identity"><strong>' . h($navigationUser['display_name']) . '</strong><span>' . h($role) . ' · ' . h($status) . '</span></div>';
        $portalBar = '<div class="portal-topbar"><div class="portal-location"><span>' . h($role) . ' portal</span><span aria-hidden="true">/</span><strong>' . $safeTitle . '</strong></div><span class="portal-account-status">' . h($status) . '</span></div>';
    }
    $landingStyles=$landing && $navigationUser===null?'<link rel="stylesheet" href="/assets/landing.css?v=20261104-campus">':'';
    if ($landingStyles!=='') { $bodyClass.=' landing-page'; }
    echo <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle} | {$appName}</title>
    <link rel="icon" href="/assets/kaaklat-logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/app.css?v=20261104-campus">
    {$landingStyles}
    <script src="/assets/forms.js" defer></script>
</head>
<body class="{$bodyClass}">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="site-header">
        <div class="header-inner">
        <a class="brand" href="/"><img class="brand-mark" src="/assets/kaaklat-logo.svg" width="48" height="48" alt=""><span>{$appName}<small>Your school library</small></span></a>
        {$workspaceLabel}
        <nav class="header-nav" aria-label="Main navigation">{$navigation}</nav>
        {$identity}
        </div>
    </header>
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
