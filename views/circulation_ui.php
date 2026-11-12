<?php
declare(strict_types=1);

/** Existing authorized cover endpoint only; never render private storage keys. */
function circulation_cover_html(array $book): string
{
    $id = filter_var($book['book_id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    $image = $id !== false && !empty($book['cover_path']) && empty($book['book_archived_at'])
        ? '<img src="/cover.php?id='.h($id).'" alt="" loading="lazy" width="48" height="68">' : '';
    return '<span class="circulation-cover" aria-hidden="true">'.$image
        . '<span class="circulation-cover-fallback">No cover</span></span>';
}
