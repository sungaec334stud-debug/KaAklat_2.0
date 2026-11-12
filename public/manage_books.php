<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog_management.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/views/managed_catalog.php';
start_application_session();
header('Cache-Control: no-store');
try {
    $pdo = database_connection(application_config()['database']);
    $user = current_user($pdo);
    if ($user === null) { redirect('/login.php'); }
    require_active_librarian($user);
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) { http_response_code(405); header('Allow: GET, POST'); exit; }
    [$query, $page] = catalog_parameters($_GET);
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $savedId = catalog_manage($pdo, $_POST, $_POST['csrf_token'] ?? null, isset($_FILES['cover']) && is_array($_FILES['cover']) ? $_FILES['cover'] : null);
            flash_set('success', 'Catalog updated successfully.');
            redirect(managed_book_url(($_POST['action'] ?? '') === 'archive_book' ? null : $savedId, $query, $page));
        } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
    }
    $idInput = $_GET['id'] ?? ($_POST['book_id'] ?? null);
    $id = $idInput === null ? null : filter_var($idInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $book = $id ? catalog_book($pdo, $id) : null;
    if ($idInput !== null && !$book) { http_response_code(404); render_page('Book not found', '<p>This book is archived or unavailable.</p><a href="/manage_books.php">Manage books</a>'); exit; }
    $token = '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
    $bookField = $book ? '<input type="hidden" name="book_id" value="' . h($id) . '">' : '';
    $content = '<section class="panel"><h1>Manage books</h1><p>Add titles, keep details up to date, and manage physical copies.</p><p><a href="/catalog.php">Browse catalog</a> · <a href="/manage_books.php?new=1">Add a new book</a></p>';
    $results = catalog_books($pdo, $query, $page);
    $page = $results['page'];
    $editor = $error !== '' ? '<p class="flash flash-error" role="alert">' . h($error) . '</p>' : '';
    if ($book || isset($_GET['new']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
        $authorText = '';
        if ($book) {
            $authors = $pdo->prepare('SELECT a.author_name FROM book_authors ba JOIN authors a ON a.author_id = ba.author_id WHERE ba.book_id = ? ORDER BY ba.credit_order');
            $authors->execute([$id]);
            $authorText = implode("\n", $authors->fetchAll(PDO::FETCH_COLUMN));
        }
        $values = ['title' => $book['title'] ?? '', 'description' => $book['description'] ?? '', 'authors' => $authorText, 'quantity' => '1', 'category_name' => $book['category_name'] ?? '', 'publication_year' => (string) ($book['publication_year'] ?? '')];
        if ($error !== '' && in_array($_POST['action'] ?? null, ['create', 'edit'], true)) {
            foreach ($values as $key => $value) { if (is_string($_POST[$key] ?? null)) { $values[$key] = $_POST[$key]; } }
        }
        $editor .= '<h2>' . ($book ? 'Book details' : 'Add a book') . '</h2><form method="post" enctype="multipart/form-data">' . $token . $bookField
            . '<input type="hidden" name="action" value="' . ($book ? 'edit' : 'create') . '">'
            . '<label for="title">Title</label><input id="title" name="title" maxlength="255" required value="' . h($values['title']) . '">'
            . '<label for="authors">Authors — one per line</label><textarea id="authors" name="authors" rows="3" required>' . h($values['authors']) . '</textarea>';
        $editor .= '<label for="category_name">Genre / category</label><input id="category_name" name="category_name" maxlength="60" list="category-options" value="' . h($values['category_name']) . '"><datalist id="category-options">';
        foreach ($pdo->query('SELECT category_name FROM categories ORDER BY category_name')->fetchAll(PDO::FETCH_COLUMN) as $name) { $editor .= '<option value="' . h($name) . '">'; }
        $editor .= '</datalist><label for="description">Description</label><textarea id="description" name="description" rows="5" maxlength="2000" required>' . h($values['description']) . '</textarea><label for="publication_year">Year first published</label><input id="publication_year" name="publication_year" type="number" min="1" max="' . date('Y') . '" value="' . h($values['publication_year']) . '"><p>Leave blank if unknown. Use the first book-publication year, rather than a reprint year.</p>';
        if (!$book) {
            $editor .= '<label for="quantity">Initial copies (1–100)</label><input id="quantity" name="quantity" type="number" min="1" max="100" required value="' . h($values['quantity']) . '">'
                . '<label for="cover">Cover (optional)</label><input id="cover" type="file" name="cover" accept="image/jpeg,image/png"><p>JPEG or PNG, up to 2 MB and 4000 × 4000 pixels.</p>';
        }
        $editor .= '<button>Save book details</button></form>';
        if ($book) {
            $editor .= '<section class="management-section"><h2>Copies</h2><p>' . h($book['available_copies']) . ' available of ' . h($book['total_copies']) . ' active copies.</p><form method="post">' . $token . $bookField . '<input type="hidden" name="action" value="add_copies"><label for="extra">Add copies (1–100)</label><input id="extra" type="number" name="quantity" min="1" max="100" value="1" required><button>Add copies</button></form>';
            $copyPage = filter_var($_GET['copies_page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000]]) ?: 1;
            $rows = pagination_rows($pdo, 'SELECT copy_id, copy_code, archived_at FROM book_copies WHERE book_id = ?', [$id], 'copy_id', $copyPage, 50, $copyPagination);
            $copyPage = $copyPagination['page'];
            foreach (array_slice($rows, 0, 50) as $copy) {
                $editor .= '<article class="student-card"><p>Copy ' . h($copy['copy_code']) . ($copy['archived_at'] ? ' · Archived' : '') . '</p>';
                if (!$copy['archived_at']) { $editor .= '<form method="post">' . $token . $bookField . '<input type="hidden" name="action" value="archive_copy"><input type="hidden" name="copy_id" value="' . h($copy['copy_id']) . '"><p>Archiving removes this copy from circulation while preserving history.</p><button class="button-danger" aria-label="' . h('Archive copy ' . $copy['copy_code'] . ' of ' . $book['title']) . '">Archive this copy</button></form>'; }
                $editor .= '</article>';
            }
            $editor .= pagination_html($copyPagination, static fn(int $target): string => '?'.http_build_query(['id'=>$id,'q'=>$query,'page'=>$page,'copies_page'=>$target]), 'Copy pages', 'copies');
            $editor .= '</section><section class="management-section"><h2>Replace cover</h2>' . catalog_cover_html($book) . '<form method="post" enctype="multipart/form-data">' . $token . $bookField . '<input type="hidden" name="action" value="cover"><label for="replacement">New JPEG or PNG cover</label><input id="replacement" type="file" name="cover" accept="image/jpeg,image/png" required><p>Maximum 2 MB and 4000 × 4000 pixels. Your existing cover stays until the replacement saves successfully.</p><button>Replace cover</button></form></section>'
                . '<section class="management-section"><h2>Archive book</h2><p>Remove this title from browsing while preserving its copies and history. Resolve pending requests and outstanding allocations first.</p><form method="post">' . $token . $bookField . '<input type="hidden" name="action" value="archive_book"><button class="button-danger" aria-label="' . h('Archive book ' . $book['title']) . '">Archive this book</button></form></section>';
        }
    } else {
        $editor .= '<h2>Select a book</h2><p>Choose Manage beside a title to edit its details, covers and physical copies.</p>';
    }
    $content .= '<div class="managed-workspace">'.managed_catalog_html($results, $query, $book ? (int)$book['book_id'] : null)
        . '<aside class="managed-editor" id="book-editor" tabindex="-1" aria-label="Selected book details and actions">'
        . '<header class="managed-editor-heading"><p>'.($book ? 'Selected title' : (isset($_GET['new']) ? 'New title' : 'Book details')).'</p>'
        . '<a class="button button-secondary" href="'.h(managed_book_url(null,$query,$page)).'" aria-label="Close book editor">×</a></header>'
        . $editor.'</aside></div>';
    render_page('Manage books', $content . '</section>');
} catch (DomainException $exception) {
    http_response_code(403); render_page('Access denied', '<p role="alert">' . h($exception->getMessage()) . '</p>');
} catch (Throwable $exception) {
    $reference = bin2hex(random_bytes(8)); error_log('Catalog management failed [' . $reference . '] code=' . $exception->getCode());
    http_response_code(500); render_page('Unable to update catalog', '<p role="alert">Please try again later. Reference: ' . h($reference) . '</p>');
}
