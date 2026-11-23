<?php
declare(strict_types=1);
require_once __DIR__.'/managed_catalog.php';
require_once __DIR__.'/time.php';

function managed_catalog_tabs(string $status): string
{
    $html='<nav class="account-tabs" aria-label="Book catalog status"><ul>';
    foreach (['active'=>'Active books','archived'=>'Archived books'] as $value=>$label) {
        $html.='<li><a href="/manage_books.php?status='.$value.'"'.($value===$status?' aria-current="page"':'').'>'.$label.'</a></li>';
    }
    return $html.'</ul></nav>';
}

function archived_book_url(?int $id, string $query, int $page): string
{
    $args=['status'=>'archived','q'=>$query,'page'=>$page];
    if ($id!==null) { $args['id']=$id; }
    return '/manage_books.php?'.http_build_query($args);
}

function archived_cover_html(array $book): string
{
    if (!empty($book['cover_path'])) {
        return '<img class="book-cover" src="/cover.php?id='.h($book['book_id']).'&amp;archived=1" alt="'.h('Cover of '.$book['title']).'" loading="lazy" width="240" height="340">';
    }
    return '<span class="book-cover" role="img" aria-label="No cover available">KA</span>';
}

function archived_catalog_html(array $results, string $query, ?array $selected, string $error): string
{
    $page=(int)$results['page'];$id=$selected ? (int)$selected['book_id'] : null;
    $html='<div class="managed-workspace"><section class="managed-catalog" aria-labelledby="catalog-records-heading"><header class="managed-catalog-heading"><h2 id="catalog-records-heading">Archived titles</h2><span>'.h($results['total']).' records</span></header>'
        .'<form method="get" class="managed-catalog-search"><input type="hidden" name="status" value="archived"><label for="q">Search title or author</label><input id="q" name="q" maxlength="100" value="'.h($query).'" placeholder="Start with a name, title or keyword"><button>Search</button><a href="'.h(archived_book_url(null,'',1)).'">Clear</a></form>'
        .'<div class="managed-column-headings" aria-hidden="true"><span>Title &amp; author</span><span>Archived</span><span>Copies</span><span></span></div>';
    foreach ($results['books'] as $book) {
        $html.='<article class="managed-catalog-record'.((int)$book['book_id']===$id?' is-selected':'').'"><div class="managed-title">'.archived_cover_html($book).'<div><h3>'.h($book['title']).'</h3><p>'.h($book['authors'] ?: 'Author information unavailable').'</p></div></div>'
            .'<p class="managed-category"><span class="managed-mobile-label">Archived: </span>'.h(display_manila_time($book['archived_at'])).'</p>'
            .'<p class="managed-availability">'.h($book['total_copies']).' active<br>'.h($book['archived_copies']).' archived</p>'
            .'<a class="managed-select" href="'.h(archived_book_url((int)$book['book_id'],$query,$page)).'#book-editor" aria-label="'.h('Review restoration of '.$book['title']).'">Review →</a></article>';
    }
    if ($results['books']===[]) { $html.='<div class="empty-state"><h3>No archived books found</h3><p>Try another title or author, or clear the search. Archived titles will appear here.</p></div>'; }
    $html.='<div class="managed-pages-wrap">'.pagination_html($results,static fn(int $target): string=>archived_book_url($id,$query,$target),'Archived book pages','books').'</div></section>'
        .'<aside class="managed-editor" id="book-editor" tabindex="-1" aria-label="Archived title and restoration"><header class="managed-editor-heading"><p>Archived title</p><a class="button button-secondary" href="'.h(archived_book_url(null,$query,$page)).'" aria-label="Close book review">×</a></header>';
    if ($error!=='') { $html.='<p class="flash flash-error" role="alert">'.h($error).'</p>'; }
    if ($selected) {
        $html.='<h2>'.h($selected['title']).'</h2><p>'.h($selected['authors'] ?: 'Author information unavailable').'</p>'.archived_cover_html($selected)
            .'<p>Archived: '.h(display_manila_time($selected['archived_at'])).'</p><p>'.h($selected['total_copies']).' active copies · '.h($selected['archived_copies']).' individually archived copies</p>'
            .'<p>Unarchiving returns this existing title to the catalog. Individually archived copies stay archived. Borrowing history is preserved.</p>';
        if ((int)$selected['total_copies']===0) { $html.='<p class="flash flash-info">This title has no active copies. It will be listed as unavailable after restoration.</p>'; }
        $html.='<form method="post" action="'.h(archived_book_url($id,$query,$page)).'"><input type="hidden" name="csrf_token" value="'.h(csrf_token()).'"><input type="hidden" name="action" value="unarchive_book"><input type="hidden" name="book_id" value="'.h($id).'">'
            .'<input type="hidden" name="archive_version" value="'.h($selected['archived_at']).'"><input type="hidden" name="archive_event" value="'.h($selected['archive_event']).'">'
            .'<label><input type="checkbox" name="restore_confirmation" value="yes" required> I confirm this title should return to the active catalog.</label><button>Unarchive book</button></form>';
    } else { $html.='<h2>Select an archived book</h2><p>Choose Review beside a title to check its copies and confirm restoration.</p>'; }
    return $html.'</aside></div>';
}
