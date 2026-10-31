<?php

declare(strict_types=1);

function render_page(string $title, string $content): void
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
    if(($navigationUser['role']??null)==='student') { $links['/requests.php']='My requests & loans'; }
    if(($navigationUser['role']??null)==='librarian' && $navigationUser['account_status']==='active') {
        $links += ['/review_requests.php'=>'Review requests','/pickups.php'=>'Pickups','/manage_books.php'=>'Manage books','/students.php'=>'Students'];
    }
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $navigation = '';
    foreach ($links as $url => $label) {
        $current = $path === $url || ($url === '/' && $path === '/index.php');
        $navigation .= '<a href="' . h($url) . '"' . ($current ? ' aria-current="page"' : '') . '>' . h($label) . '</a>';
    }

    foreach (['success', 'error', 'info'] as $type) {
        $message = flash_pull($type);

        if ($message !== null) {
            $flashes .= '<p class="flash flash-' . h($type) . '" role="status">' . h($message) . '</p>';
        }
    }

    echo <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle} | {$appName}</title>
    <link rel="icon" href="/assets/kaaklat-logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/app.css?v=20261101-journey">
    <script src="/assets/forms.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="site-header">
        <div class="header-inner">
        <a class="brand" href="/"><img class="brand-mark" src="/assets/kaaklat-logo.svg" width="48" height="48" alt=""><span>{$appName}<small>Your school library</small></span></a>
        <nav class="header-nav" aria-label="Main navigation">{$navigation}</nav>
        </div>
    </header>
    <main class="container" id="main-content" tabindex="-1">
        <div class="page-eyebrow">Library / {$safeTitle}</div>
        {$flashes}
        {$content}
    </main>
    <footer class="site-footer">KaAklat · A place for books, learning, and discovery.</footer>
</body>
</html>
HTML;
}
