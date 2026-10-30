<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/catalog_management.php';
require_once dirname(__DIR__) . '/views/layout.php';
start_application_session();
header('Cache-Control: no-store');
try {
    $pdo = database_connection(application_config()['database']);
    $user = current_user($pdo);
    if ($user === null) { redirect('/login.php'); }
    require_active_librarian($user);
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) { http_response_code(405); header('Allow: GET, POST'); exit; }
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $savedId = catalog_manage($pdo, $_POST, $_POST['csrf_token'] ?? null, isset($_FILES['cover']) && is_array($_FILES['cover']) ? $_FILES['cover'] : null);
            flash_set('success', 'Catalog updated successfully.');
            redirect(($_POST['action'] ?? '') === 'archive_book' ? '/manage_books.php' : '/manage_books.php?id=' . $savedId);
        } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
    }
    $idInput = $_GET['id'] ?? ($_POST['book_id'] ?? null);
    $id = $idInput === null ? null : filter_var($idInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $book = $id ? catalog_book($pdo, $id) : null;
    if ($idInput !== null && !$book) { http_response_code(404); render_page('Book not found', '<p>This book is archived or unavailable.</p><a href="/manage_books.php">Manage books</a>'); exit; }
    $token = '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
    $bookField = $book ? '<input type="hidden" name="book_id" value="' . h($id) . '">' : '';
    $content = '<section class="panel"><h1>Manage books</h1><p>Add titles, keep details up to date, and manage physical copies.</p><p><a href="/catalog.php">Browse catalog</a> · <a href="/manage_books.php?new=1">Add a new book</a></p>';
    if ($error !== '') { $content .= '<p class="flash flash-error" role="alert">' . h($error) . '</p>'; }
    if ($book || isset($_GET['new']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
        $authorText = '';
        if ($book) {
            $authors = $pdo->prepare('SELECT a.author_name FROM book_authors ba JOIN authors a ON a.author_id = ba.author_id WHERE ba.book_id = ? ORDER BY ba.credit_order');
            $authors->execute([$id]);
            $authorText = implode("\n", $authors->fetchAll(PDO::FETCH_COLUMN));
        }
        $values = ['title' => $book['title'] ?? '', 'description' => $book['description'] ?? '', 'authors' => $authorText, 'quantity' => '1'];
        if ($error !== '' && in_array($_POST['action'] ?? null, ['create', 'edit'], true)) {
            foreach ($values as $key => $value) { if (is_string($_POST[$key] ?? null)) { $values[$key] = $_POST[$key]; } }
        }
        $content .= '<h2>' . ($book ? 'Edit book details' : 'Add a book') . '</h2><form method="post" enctype="multipart/form-data">' . $token . $bookField
            . '<input type="hidden" name="action" value="' . ($book ? 'edit' : 'create') . '">'
            . '<label for="title">Title</label><input id="title" name="title" maxlength="255" required value="' . h($values['title']) . '">'
            . '<label for="authors">Authors — one per line</label><textarea id="authors" name="authors" rows="3" required>' . h($values['authors']) . '</textarea>'
            . '<label for="description">Description</label><textarea id="description" name="description" rows="5" maxlength="2000" required>' . h($values['description']) . '</textarea>';
        if (!$book) {
            $content .= '<label for="quantity">Initial copies (1–100)</label><input id="quantity" name="quantity" type="number" min="1" max="100" required value="' . h($values['quantity']) . '">'
                . '<label for="cover">Cover (optional)</label><input id="cover" type="file" name="cover" accept="image/jpeg,image/png"><p>JPEG or PNG, up to 2 MB and 4000 × 4000 pixels.</p>';
        }
        $content .= '<button>Save book details</button></form>';
        if ($book) {
            $content .= '<section class="management-section"><h2>Copies</h2><p>' . h($book['available_copies']) . ' available of ' . h($book['total_copies']) . ' active copies.</p><form method="post">' . $token . $bookField . '<input type="hidden" name="action" value="add_copies"><label for="extra">Add copies (1–100)</label><input id="extra" type="number" name="quantity" min="1" max="100" value="1" required><button>Add copies</button></form>';
            $copyPage = filter_var($_GET['copies_page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000]]) ?: 1;
            $copies = $pdo->prepare('SELECT copy_id, copy_code, archived_at FROM book_copies WHERE book_id = ? ORDER BY copy_id LIMIT 51 OFFSET ' . (($copyPage - 1) * 50));
            $copies->execute([$id]); $rows = $copies->fetchAll();
            foreach (array_slice($rows, 0, 50) as $copy) {
                $content .= '<article class="student-card"><p>Copy ' . h($copy['copy_code']) . ($copy['archived_at'] ? ' · Archived' : '') . '</p>';
                if (!$copy['archived_at']) { $content .= '<form method="post">' . $token . $bookField . '<input type="hidden" name="action" value="archive_copy"><input type="hidden" name="copy_id" value="' . h($copy['copy_id']) . '"><button>Archive this copy</button></form>'; }
                $content .= '</article>';
            }
            if ($copyPage > 1) { $content .= '<a href="?id=' . h($id) . '&amp;copies_page=' . ($copyPage - 1) . '">Previous copies</a> '; }
            if (count($rows) > 50) { $content .= '<a href="?id=' . h($id) . '&amp;copies_page=' . ($copyPage + 1) . '">Next copies</a>'; }
            $content .= '</section><section class="management-section"><h2>Replace cover</h2>' . catalog_cover_html($book) . '<form method="post" enctype="multipart/form-data">' . $token . $bookField . '<input type="hidden" name="action" value="cover"><label for="replacement">New JPEG or PNG cover</label><input id="replacement" type="file" name="cover" accept="image/jpeg,image/png" required><p>Maximum 2 MB and 4000 × 4000 pixels. Your existing cover stays until the replacement saves successfully.</p><button>Replace cover</button></form></section>'
                . '<section class="management-section"><h2>Archive book</h2><p>Remove this title from browsing while preserving its copies and history. Resolve pending requests and outstanding allocations first.</p><form method="post">' . $token . $bookField . '<input type="hidden" name="action" value="archive_book"><button>Archive this book</button></form></section>';
        }
    } else {
        [$query, $page] = catalog_parameters($_GET);
        $results = catalog_books($pdo, $query, $page);
        $content .= '<form method="get"><label for="q">Find a book to manage</label><input id="q" name="q" maxlength="100" value="' . h($query) . '"><button>Search</button></form>';
        foreach ($results['books'] as $row) { $content .= '<article class="student-card"><div><h2>' . h($row['title']) . '</h2><p>' . h($row['authors']) . '</p></div><a class="button" href="?id=' . h($row['book_id']) . '">Manage book</a></article>'; }
        if ($results['books'] === []) { $content .= '<p>No books found.</p>'; }
        if ($page > 1) { $content .= '<a href="?' . h(http_build_query(['q' => $query, 'page' => $page - 1])) . '">Previous</a> '; }
        if ($results['has_next']) { $content .= '<a href="?' . h(http_build_query(['q' => $query, 'page' => $page + 1])) . '">Next</a>'; }
    }
    render_page('Manage books', $content . '</section>');
} catch (DomainException $exception) {
    http_response_code(403); render_page('Access denied', '<p role="alert">' . h($exception->getMessage()) . '</p>');
} catch (Throwable $exception) {
    $reference = bin2hex(random_bytes(8)); error_log('Catalog management failed [' . $reference . '] code=' . $exception->getCode());
    http_response_code(500); render_page('Unable to update catalog', '<p role="alert">Please try again later. Reference: ' . h($reference) . '</p>');
}
