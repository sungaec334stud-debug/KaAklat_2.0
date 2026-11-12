<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/catalog.php';

function catalog_filter_form_html(array $input, array $categories, ?string $error = null): string
{
    $value = static fn (string $key): string => isset($input[$key]) && is_scalar($input[$key]) && !is_bool($input[$key]) ? mb_substr((string) $input[$key], 0, 200) : '';
    $from = $value('year_from') !== '' ? $value('year_from') : $value('year');
    $to = $value('year_to') !== '' ? $value('year_to') : $value('year');
    $target = 'catalog-search';
    if ($error !== null) {
        foreach (['year' => 'year-from', 'sort' => 'sort-filter', 'availability' => 'available-filter', 'category' => 'category-filter', 'letter' => 'letter-filter', 'author' => 'author-filter', 'title' => 'title-filter'] as $term => $id) {
            if (str_contains(strtolower($error), $term)) { $target = $id; break; }
        }
    }
    if ($error !== null && str_starts_with($error, 'To year')) { $target = 'year-to'; }
    $invalid = static fn (string $id): string => $error !== null && $target === $id ? ' aria-invalid="true" aria-describedby="' . (str_starts_with($id, 'year-') ? 'publication-year-hint ' : '') . $id . '-error"' : '';
    $fieldError = static fn (string $id): string => $error !== null && $target === $id ? '<p class="field-error" id="' . $id . '-error">' . h($error) . '</p>' : '';
    $html = $error === null ? '' : '<div class="flash flash-error error-summary" role="alert" tabindex="-1"><h2>Check your search</h2><ul><li><a href="#' . $target . '">' . h($error) . '</a></li></ul></div>';
    $html .= '<form action="/catalog.php" method="get" class="catalog-filters catalog-controls" novalidate><div class="catalog-filter-grid">';
    $html .= '<div class="catalog-search-control"><label for="catalog-search">Search title or author</label><input type="search" id="catalog-search" name="q" maxlength="100" value="' . h($value('q')) . '"' . $invalid('catalog-search') . '>' . $fieldError('catalog-search');
    if ($value('q') !== '' && $error === null) {
        [, $page] = catalog_parameters($input);
        $html .= '<a class="catalog-clear-search" aria-label="Clear title or author search" href="' . h(catalog_remove_filter_url($value('q'), $page, $input, 'q')) . '#catalog-search">Clear search</a>';
    }
    $html .= '</div><div><label for="category-filter">Category</label><select id="category-filter" name="category"' . $invalid('category-filter') . '><option value="">All categories</option>';
    $categoryKnown = $value('category') === '';
    foreach ($categories as $category) {
        $selected = (string) $category['category_id'] === $value('category');
        $categoryKnown = $categoryKnown || $selected;
        $html .= '<option value="' . h($category['category_id']) . '"' . ($selected ? ' selected' : '') . '>' . h($category['category_name']) . '</option>';
    }
    if (!$categoryKnown && ctype_digit($value('category'))) { $html .= '<option value="' . h($value('category')) . '" selected>Category unavailable</option>'; }
    $html .= '</select>' . $fieldError('category-filter') . '</div><div><label for="available-filter">Availability</label><select id="available-filter" name="available"' . $invalid('available-filter') . '>';
    foreach (['' => 'All books', '1' => 'Available now', '0' => 'Currently unavailable'] as $key => $label) { $html .= '<option value="' . h($key) . '"' . ((string) $key === $value('available') ? ' selected' : '') . '>' . h($label) . '</option>'; }
    $html .= '</select>' . $fieldError('available-filter') . '</div><div><label for="sort-filter">Sort by</label><select id="sort-filter" name="sort"' . $invalid('sort-filter') . '>';
    foreach (catalog_sort_options() as $key => $label) { $html .= '<option value="' . h($key) . '"' . ($key === ($value('sort') ?: 'title_asc') ? ' selected' : '') . '>' . h($label) . '</option>'; }
    $html .= '</select>' . $fieldError('sort-filter') . '</div></div>';
    $open = $error !== null || $value('author') !== '' || $value('title') !== '' || $value('letter') !== '' || $from !== '' || $to !== '';
    $html .= '<details class="catalog-more-filters"' . ($open ? ' open' : '') . '><summary>More filters</summary><div class="catalog-filter-grid">';
    foreach (['author' => ['author-filter', 'Author contains'], 'title' => ['title-filter', 'Title contains']] as $name => [$id, $label]) { $html .= '<div><label for="' . $id . '">' . $label . '</label><input id="' . $id . '" name="' . $name . '" maxlength="100" value="' . h($value($name)) . '"' . $invalid($id) . '>' . $fieldError($id) . '</div>'; }
    $html .= '<div><label for="letter-filter">Title starts with</label><select id="letter-filter" name="letter"' . $invalid('letter-filter') . '><option value="">Any letter</option>';
    foreach (range('A', 'Z') as $letter) { $html .= '<option' . ($letter === strtoupper($value('letter')) ? ' selected' : '') . '>' . $letter . '</option>'; }
    $html .= '</select>' . $fieldError('letter-filter') . '</div>';
    $html .= '<fieldset class="catalog-year-range"><legend>Publication year</legend>';
    foreach (['year_from' => ['year-from', 'From year', $from], 'year_to' => ['year-to', 'To year', $to]] as $name => [$id, $label, $v]) { $html .= '<div><label for="' . $id . '">' . $label . '</label><input type="number" id="' . $id . '" name="' . $name . '" min="1" max="' . date('Y') . '" step="1"' . ($error === null || $target !== $id ? ' aria-describedby="publication-year-hint"' : '') . ' value="' . h($v) . '"' . $invalid($id) . '>' . $fieldError($id) . '</div>'; }
    $html .= '</fieldset></div><p id="publication-year-hint" class="catalog-filter-hint">Years refer to when the work was first published, not the cover edition. Leave either end blank for an open range; use the same year at both ends for an exact year.</p></details><div class="filter-actions"><button>Apply filters</button><a href="/catalog.php">Clear all</a></div></form>';
    return $html;
}

function catalog_active_filters_html(string $query, int $page, array $filters, array $categories): string
{
    $labels = [];
    if ($query !== '') { $labels['q'] = 'Search: ' . $query; }
    if ($filters['category'] !== null) { $labels['category'] = 'Category: ' . (array_column($categories, 'category_name', 'category_id')[$filters['category']] ?? 'Unavailable category'); }
    foreach (['author' => 'Author', 'title' => 'Title contains', 'letter' => 'Starts with'] as $key => $label) { if ($filters[$key] !== '') { $labels[$key] = $label . ': ' . $filters[$key]; } }
    if ($filters['year_from'] !== null && $filters['year_from'] === $filters['year_to']) { $labels['years'] = 'Year: ' . $filters['year_from']; }
    else { foreach (['year_from' => 'From year', 'year_to' => 'To year'] as $key => $label) { if ($filters[$key] !== null) { $labels[$key] = $label . ': ' . $filters[$key]; } } }
    if ($filters['available'] !== '') { $labels['available'] = $filters['available'] === '1' ? 'Available now' : 'Currently unavailable'; }
    if ($filters['sort'] !== 'title_asc') { $labels['sort'] = catalog_sort_options()[$filters['sort']]; }
    if ($labels === []) { return ''; }
    $html = '<nav class="catalog-active-filters" aria-label="Applied catalog choices">';
    foreach ($labels as $key => $label) { $html .= '<a href="' . h(catalog_remove_filter_url($query, $page, $filters, $key)) . '" aria-label="' . h('Remove ' . $label) . '">' . h($label) . '<span aria-hidden="true"> ×</span></a>'; }
    return $html . '</nav>';
}

function catalog_unavailable_badge_html(array $book): string
{
    return (int) $book['available_copies'] === 0 ? '<p class="catalog-unavailable"><span class="status-badge">Currently unavailable</span></p>' : '';
}
