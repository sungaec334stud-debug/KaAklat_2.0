<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function catalog_access_allowed(?array $user): bool
{
    return $user !== null && (
        ($user['role'] === 'student' && in_array($user['account_status'], ['pending', 'active'], true))
        || (in_array($user['role'], ['librarian', 'administrator'], true) && $user['account_status'] === 'active')
    );
}

function catalog_parameters(array $input): array
{
    $query = $input['q'] ?? '';
    $pageInput = $input['page'] ?? '1';
    if (!is_string($query) || mb_strlen($query) > 100 || !is_scalar($pageInput)) {
        throw new InvalidArgumentException('Use a search of at most 100 characters and a valid page number.');
    }
    $page = filter_var($pageInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000]]);
    if ($page === false) { throw new InvalidArgumentException('Choose a page from 1 to 10000.'); }
    return [trim($query), $page];
}

function catalog_select_sql(): string
{
    return "SELECT b.book_id, b.title, b.description, b.cover_path, b.publication_year, b.category_id, cat.category_name,
        (SELECT GROUP_CONCAT(a.author_name ORDER BY ba.credit_order SEPARATOR ', ')
         FROM book_authors ba JOIN authors a ON a.author_id = ba.author_id WHERE ba.book_id = b.book_id) AS authors,
        (SELECT COUNT(*) FROM book_copies c WHERE c.book_id = b.book_id AND c.archived_at IS NULL) AS total_copies,
        (SELECT COUNT(*) FROM book_copies c WHERE c.book_id = b.book_id AND c.archived_at IS NULL
         AND NOT EXISTS (SELECT 1 FROM loans l JOIN borrow_requests r ON r.request_id = l.request_id
             WHERE l.copy_id = c.copy_id AND l.returned_at IS NULL AND l.cancelled_at IS NULL
             AND (l.checked_out_at IS NOT NULL OR r.decided_at > UTC_TIMESTAMP() - INTERVAL 48 HOUR))) AS available_copies
        FROM books b LEFT JOIN categories cat ON cat.category_id = b.category_id WHERE b.archived_at IS NULL";
}

function catalog_books(PDO $connection, string $query, int $page, array $filters = []): array
{
    [$query, $page] = catalog_parameters(['q' => $query, 'page' => $page]);
    $sql = catalog_select_sql();
    $parameters = [];
    $filters = catalog_filters($filters);
    foreach (['category' => 'b.category_id', 'year' => 'b.publication_year'] as $key => $column) {
        if ($filters[$key] !== null) { $sql .= ' AND ' . $column . ' = :' . $key; $parameters[$key] = $filters[$key]; }
    }
    foreach (['title' => 'b.title', 'letter' => 'b.title'] as $key => $column) {
        if ($filters[$key] !== '') {
            $sql .= " AND $column LIKE :filter_$key ESCAPE '!'";
            $parameters['filter_' . $key] = ($key === 'title' ? '%' : '') . catalog_like($filters[$key]) . '%';
        }
    }
    if ($filters['author'] !== '') {
        $sql .= " AND EXISTS (SELECT 1 FROM book_authors fa JOIN authors aa ON aa.author_id = fa.author_id WHERE fa.book_id = b.book_id AND aa.author_name LIKE :filter_author ESCAPE '!')";
        $parameters['filter_author'] = '%' . catalog_like($filters['author']) . '%';
    }
    if ($query !== '') {
        // Treat wildcard characters as literal search text, using an explicit escape character.
        $pattern = '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $sql .= " AND (b.title LIKE :title ESCAPE '!' OR EXISTS (
            SELECT 1 FROM book_authors sx JOIN authors ax ON ax.author_id = sx.author_id
            WHERE sx.book_id = b.book_id AND ax.author_name LIKE :author ESCAPE '!'))";
        $parameters += ['title' => $pattern, 'author' => $pattern];
    }
    if ($filters['available'] === '1') { $sql = 'SELECT * FROM (' . $sql . ') catalog_results WHERE available_copies > 0'; }
    $statement = $connection->prepare($sql . ' ORDER BY ' . ($filters['available'] === '1' ? 'title, book_id' : 'b.title, b.book_id') . ' LIMIT 13 OFFSET ' . (($page - 1) * 12));
    $statement->execute($parameters);
    $rows = $statement->fetchAll();
    return ['books' => array_slice($rows, 0, 12), 'has_next' => count($rows) > 12];
}

function catalog_like(string $value): string
{
    return strtr($value, ['!' => '!!', '%' => '!%', '_' => '!_']);
}

function catalog_publication_year(mixed $value): ?int
{
    if ($value === '' || $value === null) { return null; }
    $year = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => (int) date('Y')]]) : false;
    if ($year === false) { throw new InvalidArgumentException('Enter a publication year from 1 through the current year, or leave it unknown.'); }
    return $year;
}

function catalog_filters(array $input): array
{
    $result = ['letter' => '', 'year' => null, 'author' => '', 'category' => null, 'title' => '', 'available' => ''];
    $available = $input['available'] ?? '';
    if (!is_string($available) || !in_array($available, ['', '1'], true)) { throw new InvalidArgumentException('Choose a valid availability filter.'); }
    $result['available'] = $available;
    foreach (['letter', 'author', 'title'] as $key) {
        $value = $input[$key] ?? '';
        if (!is_string($value) || mb_strlen($value) > 100) { throw new InvalidArgumentException('Use valid title, author and letter filters.'); }
        $result[$key] = trim($value);
    }
    $result['letter'] = strtoupper($result['letter']);
    if ($result['letter'] !== '' && !preg_match('/^[A-Z]$/D', $result['letter'])) { throw new InvalidArgumentException('Choose a title initial from A to Z.'); }
    $result['year'] = catalog_publication_year($input['year'] ?? null);
    $category = $input['category'] ?? '';
    if ($category !== '' && $category !== null) {
        $value = is_scalar($category) ? filter_var($category, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        if ($value === false) { throw new InvalidArgumentException('Choose a valid category.'); }
        $result['category'] = $value;
    }
    return $result;
}

function catalog_metadata_html(array $book): string
{
    return '<p class="book-metadata">' . h($book['category_name'] ?: 'Uncategorized') . ' · First published: ' . h($book['publication_year'] ?? 'Unknown') . '</p>';
}

function catalog_book(PDO $connection, int $id): ?array
{
    $statement = $connection->prepare(catalog_select_sql() . ' AND b.book_id = :id');
    $statement->execute(['id' => $id]);
    return $statement->fetch() ?: null;
}

function catalog_cover_html(array $book): string
{
    if (!empty($book['cover_path'])) {
        return '<img class="book-cover" src="/cover.php?id=' . h($book['book_id']) . '" alt="Cover of ' . h($book['title']) . '" loading="lazy" width="240" height="340">';
    }
    return '<div class="book-placeholder" role="img" aria-label="No cover available">KaAklat<span>No cover available</span></div>';
}
