<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/views/catalog_pagination.php';
require_once dirname(__DIR__) . '/views/catalog_status.php';
require_once dirname(__DIR__) . '/views/catalog_filters.php';
start_application_session();
header('Cache-Control: no-store');
try {
    $connection = database_connection(application_config()['database']);
    $user = current_user($connection);
    if (!catalog_access_allowed($user)) { redirect('/login.php'); }
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        header('Allow: GET');
        exit;
    }
    $categories = $connection->query('SELECT category_id, category_name FROM categories ORDER BY category_name')->fetchAll();
    [$query, $page] = catalog_parameters($_GET);
    $filters = catalog_filters($_GET);
    $content = '<section class="panel"><h1>Explore the library</h1><p>Discover your next read. Availability reflects copies currently ready to borrow.</p>';
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $book = $id === false ? null : catalog_book($connection, $id);
        if ($book === null) {
            http_response_code(404);
            $content .= '<h2>Book not found</h2><p>This book may no longer be listed.</p>';
        } else {
            $content .= catalog_metadata_html($book);
            $content .= catalog_unavailable_badge_html($book);
            $content .= '<div class="book-detail">' . catalog_cover_html($book) . '<div><h2>' . h($book['title']) . '</h2><p>' . h($book['authors'] ?: 'Author not listed') . '</p><p class="book-description">' . nl2br(h($book['description'])) . '</p><p><strong>' . h($book['available_copies']) . ' copies available</strong> of ' . h($book['total_copies']) . ' active copies</p>' . '</div></div>';
        }
        $content .= '<p><a href="' . h(catalog_url($query, $page, $filters)) . '">Back to results</a></p>';
        if ($book !== null && (int)$book['available_copies'] === 0) { $content .= '<p class="flash flash-info">No copies are currently available. Please check again later.</p>'; }
        if ($book !== null && (int)$book['available_copies'] > 0 && $user['role'] === 'student' && $user['account_status'] === 'active') {
            $content .= '<p><a class="button" href="/requests.php?book=' . h($book['book_id']) . '">Request this book</a></p>';
        }
    } else {
        $result = catalog_books($connection, $query, $page, $filters);
        $page = $result['page'];
        $ownStates = $user['role'] === 'student'
            ? catalog_own_request_states($connection, array_map('intval', array_column($result['books'], 'book_id'))) : [];
        $content .= catalog_filter_form_html($_GET, $categories);
        $content .= catalog_active_filters_html($query, $page, $filters, $categories);
        $first = $result['total'] === 0 ? 0 : ($page - 1) * 12 + 1;
        $last = min($result['total'], $page * 12);
        $content .= '<p class="catalog-result-count" role="status">Showing ' . h($first) . '–' . h($last) . ' of ' . h($result['total']) . ' books</p><div class="book-grid">';
        foreach ($result['books'] as $book) {
            $statusLabel = catalog_request_status_html($ownStates[(int)$book['book_id']] ?? null);
            $content .= '<article class="book-card">' . catalog_cover_html($book) . '<h2><a href="' . h(catalog_url($query, $page, $filters, (int) $book['book_id'])) . '">' . h($book['title']) . '</a></h2><div class="catalog-card-status">' . $statusLabel . catalog_unavailable_badge_html($book) . '</div><p>' . h($book['authors'] ?: 'Author not listed') . '</p>' . catalog_metadata_html($book) . '<p>' . h(mb_strimwidth($book['description'], 0, 150, '…', 'UTF-8')) . '</p><p class="availability">' . h($book['available_copies']) . ' copies available</p></article>';
        }
        $content .= '</div>';
        if ($result['books'] === []) { $content .= '<p>No books match these choices. Try a broader search or clear the filters.</p><a href="/catalog.php">Clear all</a>'; }
        $content .= catalog_pagination_html($result, $query, $filters);
    }
    render_page('Book catalog', $content . '</section>');
} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    render_page('Check your search', '<section class="panel"><h1>Explore the library</h1>' . catalog_filter_form_html($_GET, $categories ?? [], $exception->getMessage()) . '</section>');
} catch (Throwable $exception) {
    $reference = bin2hex(random_bytes(8));
    error_log('Catalog failure [' . $reference . '] code=' . $exception->getCode());
    http_response_code(500);
    render_page('Catalog unavailable', '<p role="alert">The catalog is temporarily unavailable. Please try again later. Reference: ' . h($reference) . '</p>');
}
