<?php

declare(strict_types=1);

function render_page(string $title, string $content): void
{
    $safeTitle = h($title);
    $appName = h((string) application_config()['app_name']);
    $flashes = '';
    $navigation = isset($_SESSION['user_id']) ? '<a href="/">Dashboard</a><a href="/catalog.php">Book catalog</a>' : '<a href="/login.php">Log in</a><a href="/register.php">Create an account</a>';

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
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="/">{$appName}<small>Your school library</small></a>
        <nav class="header-nav" aria-label="Main navigation">{$navigation}</nav>
    </header>
    <main class="container">
        <div class="page-eyebrow">Library / {$safeTitle}</div>
        {$flashes}
        {$content}
    </main>
    <footer class="site-footer">KaAklat · A place for books, learning, and discovery.</footer>
</body>
</html>
HTML;
}
