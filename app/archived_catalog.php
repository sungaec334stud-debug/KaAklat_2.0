<?php
declare(strict_types=1);

require_once __DIR__ . '/catalog_management.php';

/** Archived metadata never shares the student/administrator catalog read path. */
function archived_catalog_select_sql(): string
{
    return "SELECT b.book_id, b.title, b.description, b.cover_path, b.archived_at,
        (SELECT GROUP_CONCAT(a.author_name ORDER BY ba.credit_order SEPARATOR ', ')
         FROM book_authors ba JOIN authors a ON a.author_id=ba.author_id WHERE ba.book_id=b.book_id) AS authors,
        (SELECT COUNT(*) FROM book_copies c WHERE c.book_id=b.book_id AND c.archived_at IS NULL) AS total_copies,
        (SELECT COUNT(*) FROM book_copies c WHERE c.book_id=b.book_id AND c.archived_at IS NOT NULL) AS archived_copies,
        (SELECT COALESCE(MAX(e.audit_id),0) FROM audit_events e WHERE e.entity_type='books' AND e.entity_id=b.book_id AND e.event_code='catalog.archive_book') AS archive_event
        FROM books b WHERE b.archived_at IS NOT NULL";
}

function archived_catalog_book(PDO $pdo, int $id): ?array
{
    require_active_librarian(current_user($pdo));
    $statement=$pdo->prepare(archived_catalog_select_sql().' AND b.book_id=?');
    $statement->execute([$id]);
    return $statement->fetch() ?: null;
}

function archived_catalog_books(PDO $pdo, string $query, int $page): array
{
    require_active_librarian(current_user($pdo));
    [$query,$page]=catalog_parameters(['q'=>$query,'page'=>$page]);
    $sql=archived_catalog_select_sql();$args=[];
    if ($query!=='') {
        $sql.=" AND (b.title LIKE ? ESCAPE '!' OR EXISTS (SELECT 1 FROM book_authors ba JOIN authors a ON a.author_id=ba.author_id WHERE ba.book_id=b.book_id AND a.author_name LIKE ? ESCAPE '!'))";
        $args=array_fill(0,2,'%'.catalog_like($query).'%');
    }
    $rows=pagination_rows($pdo,$sql,$args,'title ASC, book_id ASC',$page,12,$pagination);
    return ['books'=>array_slice($rows,0,12)]+$pagination;
}
