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
    return "SELECT b.book_id, b.title, b.description, b.cover_path,
        (SELECT GROUP_CONCAT(a.author_name ORDER BY ba.credit_order SEPARATOR ', ')
         FROM book_authors ba JOIN authors a ON a.author_id = ba.author_id WHERE ba.book_id = b.book_id) AS authors,
        (SELECT COUNT(*) FROM book_copies c WHERE c.book_id = b.book_id AND c.archived_at IS NULL) AS total_copies,
        (SELECT COUNT(*) FROM book_copies c WHERE c.book_id = b.book_id AND c.archived_at IS NULL
         AND NOT EXISTS (SELECT 1 FROM loans l JOIN borrow_requests r ON r.request_id = l.request_id
             WHERE l.copy_id = c.copy_id AND l.returned_at IS NULL AND l.cancelled_at IS NULL
             AND (l.checked_out_at IS NOT NULL OR r.decided_at > UTC_TIMESTAMP() - INTERVAL 48 HOUR))) AS available_copies
        FROM books b WHERE b.archived_at IS NULL";
}

function catalog_books(PDO $connection, string $query, int $page): array
{
    [$query, $page] = catalog_parameters(['q' => $query, 'page' => $page]);
    $sql = catalog_select_sql();
    $parameters = [];
    if ($query !== '') {
        // Treat wildcard characters as literal search text, using an explicit escape character.
        $pattern = '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $sql .= " AND (b.title LIKE :title ESCAPE '!' OR EXISTS (
            SELECT 1 FROM book_authors sx JOIN authors ax ON ax.author_id = sx.author_id
            WHERE sx.book_id = b.book_id AND ax.author_name LIKE :author ESCAPE '!'))";
        $parameters = ['title' => $pattern, 'author' => $pattern];
    }
    $statement = $connection->prepare($sql . ' ORDER BY b.title, b.book_id LIMIT 13 OFFSET ' . (($page - 1) * 12));
    $statement->execute($parameters);
    $rows = $statement->fetchAll();
    return ['books' => array_slice($rows, 0, 12), 'has_next' => count($rows) > 12];
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
