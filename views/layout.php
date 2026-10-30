<?php

declare(strict_types=1);

function render_page(string $title, string $content): void
{
    $safeTitle = h($title);
    $appName = h((string) application_config()['app_name']);
    $flashes = '';

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
        <a class="brand" href="/">{$appName}</a>
    </header>
    <main class="container">
        {$flashes}
        {$content}
    </main>
</body>
</html>
HTML;
}
