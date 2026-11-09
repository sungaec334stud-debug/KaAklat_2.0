<?php

// Included only by the isolated fictional HTTP runner.
declare(strict_types=1);
$db->exec("INSERT INTO categories(category_name) VALUES('Pagination fixture')");
$paginationCategory = (int) $db->lastInsertId();
$db->exec("INSERT INTO authors(author_name) VALUES('Pagination Author')");
$paginationAuthor = (int) $db->lastInsertId();
for ($number = 1; $number <= 25; $number++) {
    $db->prepare('INSERT INTO books(title,description,category_id,publication_year) VALUES(?,?,?,2001)')
        ->execute(['Pagination fixture ' . sprintf('%02d', $number), 'Wholly fictional pagination check.', $paginationCategory]);
    $paginationBook = (int) $db->lastInsertId();
    $db->prepare('INSERT INTO book_authors(book_id,author_id,credit_order) VALUES(?,?,1)')->execute([$paginationBook,$paginationAuthor]);
    $db->prepare('INSERT INTO book_copies(book_id,copy_code) VALUES(?,?)')->execute([$paginationBook,'PAGE-' . $number]);
}
$db->prepare("INSERT INTO users(username,display_name,password_hash,role,account_status) VALUES('pagination_pending','Fictional pagination pending',?,'student','pending')")
    ->execute([password_hash($password, PASSWORD_DEFAULT)]);
$paginationPending = curl_init();
curl_setopt_array($paginationPending, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);
[$code,$html] = accept_http($paginationPending, '/login.php');
[$code] = accept_http($paginationPending, '/login.php', ['username'=>'pagination_pending','password'=>$password,'csrf_token'=>accept_token($html)]);
accept_check($code===303, 'Pagination pending fixture sign-in');
$paginationFilters = ['title'=>'Pagination fixture','q'=>'Pagination','category'=>$paginationCategory,'year'=>2001,'letter'=>'P','author'=>'Pagination Author','available'=>'1'];
foreach (['student'=>$portalStudent,'pending'=>$paginationPending,'librarian'=>$clients['librarian'],'administrator'=>$clients['admin']] as $role=>$client) {
    [$code,$html] = accept_http($client, '/catalog.php?' . http_build_query($paginationFilters + ['page'=>2]));
    accept_check($code===200 && str_contains($html, '25 books · Page 2 of 3'), "$role filtered page total");
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    accept_check($xpath->query('//article[@class="book-card"]')->length===12, "$role middle-page size");
    accept_check($xpath->query('//nav[@aria-label="Catalog pages"]//*[@aria-current="page" and text()="2"]')->length===1, "$role accessible current page");
    foreach ($xpath->query('//nav[@aria-label="Catalog pages"]//a') as $link) {
        parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $params);
        $target = (int)$params['page']; unset($params['page']);
        accept_check($target>=1 && $target<=3 && $params==$paginationFilters, "$role numbered link keeps all filters");
    }
    [$code,$html] = accept_http($client, '/catalog.php?' . http_build_query($paginationFilters + ['page'=>10000]));
    accept_check($code===200 && str_contains($html, 'Page 3 of 3') && str_contains($html, 'Pagination fixture 25'), "$role out-of-range clamps to last page");
    [$code,$html] = accept_http($client, '/catalog.php?title=no-pagination-matches&page=99');
    accept_check($code===200 && str_contains($html, '0 books · Page 1 of 1'), "$role empty result resets page");
}
curl_close($paginationPending);
