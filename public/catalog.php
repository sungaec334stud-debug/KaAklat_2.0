<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog.php';
require_once dirname(__DIR__) . '/views/layout.php';
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
    $content = '<section class="panel"><h1>Explore the library</h1><p>Discover your next read. Availability reflects copies currently ready to borrow.</p>';
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $book = $id === false ? null : catalog_book($connection, $id);
        if ($book === null) {
            http_response_code(404);
            $content .= '<h2>Book not found</h2><p>This book may no longer be listed.</p>';
        } else {
            $content .= catalog_metadata_html($book);
            $content .= '<div class="book-detail">' . catalog_cover_html($book) . '<div><h2>' . h($book['title']) . '</h2><p>' . h($book['authors'] ?: 'Author not listed') . '</p><p class="book-description">' . nl2br(h($book['description'])) . '</p><p><strong>' . h($book['available_copies']) . ' available</strong> of ' . h($book['total_copies']) . ' active copies</p>' . ((int) $book['available_copies'] === 0 ? '<p>No copies available right now.</p>' : '') . '</div></div>';
        }
        $content .= '<p><a href="/catalog.php">Back to catalog</a></p>';
        if ($book !== null && $user['role'] === 'student' && $user['account_status'] === 'active') {
            $content .= '<p><a class="button" href="/requests.php?book=' . h($book['book_id']) . '">Request this book</a></p>';
        }
    } else {
        [$query, $page] = catalog_parameters($_GET);
        $filters = catalog_filters($_GET);
        $result = catalog_books($connection, $query, $page, $filters);
        $content .= '<form class="catalog-filters" method="get"><div><label for="title-filter">Book title</label><input id="title-filter" name="title" maxlength="100" value="' . h($filters['title']) . '"></div><div><label for="author-filter">Author name</label><input id="author-filter" name="author" maxlength="100" value="' . h($filters['author']) . '"></div><div><label for="category-filter">Genre / category</label><select id="category-filter" name="category"><option value="">All categories</option>';
        foreach ($connection->query('SELECT category_id, category_name FROM categories ORDER BY category_name')->fetchAll() as $category) {
            $content .= '<option value="' . h($category['category_id']) . '"' . ((int) $category['category_id'] === $filters['category'] ? ' selected' : '') . '>' . h($category['category_name']) . '</option>';
        }
        $content .= '</select></div><div><label for="letter-filter">Title starts with</label><select id="letter-filter" name="letter"><option value="">Any letter</option>';
        foreach (range('A','Z') as $letter) { $content .= '<option' . ($letter === $filters['letter'] ? ' selected' : '') . '>' . $letter . '</option>'; }
        $content .= '</select></div><div><label for="year-filter">Year first published</label><input id="year-filter" name="year" type="number" min="1" max="' . date('Y') . '" value="' . h($filters['year'] ?? '') . '"></div>';
        if ($query !== '') { $content .= '<input type="hidden" name="q" value="' . h($query) . '">'; }
        $content .= '<div><label for="available-filter">Availability</label><select id="available-filter" name="available"><option value="">All books</option><option value="1"' . ($filters['available'] === '1' ? ' selected' : '') . '>Available books only</option></select></div><div class="filter-actions"><button>Apply filters</button> <a href="/catalog.php">Clear filters</a></div></form><div class="book-grid">';
        foreach ($result['books'] as $book) {
            $content .= '<article class="book-card">' . catalog_cover_html($book) . '<h2><a href="?id=' . h($book['book_id']) . '">' . h($book['title']) . '</a></h2><p>' . h($book['authors'] ?: 'Author not listed') . '</p>' . catalog_metadata_html($book) . '<p>' . h(mb_strimwidth($book['description'], 0, 150, '…', 'UTF-8')) . '</p><p class="availability">' . h($book['available_copies']) . ' copies available</p></article>';
        }
        $content .= '</div>';
        if ($result['books'] === []) { $content .= '<p>No books found. Try another search or return to the first page.</p>'; }
        $content .= '<nav class="catalog-pagination" aria-label="Catalog pages">';
        if ($page > 1) { $content .= '<a href="?' . h(http_build_query($filters + ['q' => $query, 'page' => $page - 1])) . '">Previous</a> '; }
        $content .= '<span>Page ' . h($page) . '</span> ';
        if ($result['has_next']) { $content .= '<a href="?' . h(http_build_query($filters + ['q' => $query, 'page' => $page + 1])) . '">Next</a>'; }
        $content .= '</nav>';
    }
    render_page('Book catalog', $content . '</section>');
} catch (InvalidArgumentException $exception) {
    http_response_code(400);
    render_page('Check your search', '<p role="alert">' . h($exception->getMessage()) . '</p><a href="/catalog.php">Back to catalog</a>');
} catch (Throwable $exception) {
    $reference = bin2hex(random_bytes(8));
    error_log('Catalog failure [' . $reference . '] code=' . $exception->getCode());
    http_response_code(500);
    render_page('Catalog unavailable', '<p role="alert">The catalog is temporarily unavailable. Please try again later. Reference: ' . h($reference) . '</p>');
}
