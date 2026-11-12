<?php
declare(strict_types=1);

require_once __DIR__.'/pagination.php';

/** Read-only context. Never include these values in a mutation payload. */
function managed_book_url(?int $bookId, string $query, int $page, ?int $copyPage = null): string
{
    $parameters = ['q'=>$query, 'page'=>$page];
    if ($bookId !== null) { $parameters['id'] = $bookId; }
    if ($copyPage !== null) { $parameters['copies_page'] = $copyPage; }
    return '/manage_books.php?'.http_build_query($parameters);
}

function managed_catalog_html(array $results, string $query, ?int $selectedId): string
{
    $page = (int)$results['page'];
    $html = '<section class="managed-catalog" aria-labelledby="catalog-records-heading">'
        . '<header class="managed-catalog-heading"><h2 id="catalog-records-heading">Catalog records</h2><span>'.h($results['total']).' records</span></header>'
        . '<form method="get" class="managed-catalog-search"><label for="q">Search title or author</label>'
        . '<input id="q" name="q" maxlength="100" value="'.h($query).'" placeholder="Start with a name, title or keyword">';
    if ($selectedId !== null) { $html .= '<input type="hidden" name="id" value="'.h($selectedId).'">'; }
    $html .= '<button>Search</button><a href="'.h(managed_book_url($selectedId,'',1)).'">Clear</a></form>'
        . '<div class="managed-column-headings" aria-hidden="true"><span>Title &amp; author</span><span>Category</span><span>Available</span><span></span></div>';
    foreach ($results['books'] as $book) {
        $selected = (int)$book['book_id'] === $selectedId;
        $html .= '<article class="managed-catalog-record'.($selected?' is-selected':'').'">'
            . '<div class="managed-title">'.catalog_cover_html($book).'<div><h3>'.h($book['title']).'</h3><p>'.h($book['authors']).'</p></div></div>'
            . '<p class="managed-category"><span class="managed-mobile-label">Category: </span>'.h($book['category_name']??'Uncategorized').'</p>'
            . '<p class="managed-availability"><span class="managed-mobile-label">Available: </span><strong>'.h($book['available_copies']).' / '.h($book['total_copies']).'</strong><span class="sr-only"> available active copies</span></p>'
            . '<a class="managed-select" href="'.h(managed_book_url((int)$book['book_id'],$query,$page)).'#book-editor"'
            . ($selected?' aria-current="true"':'').' aria-label="'.h('Manage '.$book['title']).'">Manage →</a></article>';
    }
    if ($results['books'] === []) { $html .= '<div class="empty-state"><h3>No books found</h3><p>Try a different title or author, or clear the search.</p></div>'; }
    $html .= '<div class="managed-pages-wrap">'.pagination_html($results, static fn(int $target): string => managed_book_url($selectedId,$query,$target), 'Managed book pages', 'books').'</div>';
    return $html.'</section>';
}
