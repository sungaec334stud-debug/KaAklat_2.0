<?php

// Included after pagination fixtures in the isolated HTTP runner.
declare(strict_types=1);
$statusBooks = $db->query("SELECT book_id FROM books WHERE title LIKE 'Pagination fixture %' ORDER BY title LIMIT 6")->fetchAll(PDO::FETCH_COLUMN);
$db->prepare("INSERT INTO users(username,display_name,password_hash,role,account_status) VALUES('catalog_status_other','Fictional label owner',?,'student','active')")
    ->execute([password_hash($password, PASSWORD_DEFAULT)]);
$statusOtherId = (int)$db->lastInsertId();
foreach ($statusBooks as $index=>$bookId) {
    $pending = in_array($index,[0,5],true);
    $owner = $index===5 ? $statusOtherId : 1;
    $db->prepare('INSERT INTO borrow_requests(student_id,book_id,requested_days,request_reason,requested_at,decision,approved_days,decided_at,decided_by) VALUES(?,?,7,?,UTC_TIMESTAMP()-INTERVAL 10 DAY,?,?,?,?)')
        ->execute([$owner,$bookId,'Fictional catalog status verification.',$pending?'pending':'approved',$pending?null:7,$pending?null:gmdate('Y-m-d H:i:s'),$pending?null:3]);
    $requestId = (int)$db->lastInsertId();
    if ($pending) { continue; }
    $copyId = (int)$db->query('SELECT MIN(copy_id) FROM book_copies WHERE book_id=' . (int)$bookId)->fetchColumn();
    if ($index===1) {
        $db->prepare('INSERT INTO loans(request_id,copy_id) VALUES(?,?)')->execute([$requestId,$copyId]);
    } else {
        $daysAgo = $index===2 ? 1 : 8;
        $db->prepare('INSERT INTO loans(request_id,copy_id,checked_out_at,checked_out_by,due_date,returned_at,returned_by) VALUES(?,?,UTC_TIMESTAMP()-INTERVAL ' . $daysAgo . ' DAY,3,DATE(UTC_TIMESTAMP()+INTERVAL 8 HOUR)-INTERVAL ' . $daysAgo . ' DAY+INTERVAL 7 DAY,' . ($index===4 ? 'UTC_TIMESTAMP(),3' : 'NULL,NULL') . ')')
            ->execute([$requestId,$copyId]);
    }
}
[$code,$html] = accept_http($portalStudent, '/catalog.php?title=Pagination+fixture');
$dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
foreach ([1=>'Your request: Pending',2=>'Your book: Awaiting pickup',3=>'Your loan: Active',4=>'Your loan: Overdue'] as $number=>$label) {
    $title = 'Pagination fixture ' . sprintf('%02d',$number);
    $nodes = $xpath->query('//article[h2/a[text()="' . $title . '"]]//p[@class="catalog-request-status"]');
    accept_check($code===200 && $nodes->length===1 && $nodes->item(0)->textContent===$label, 'Catalog card shows own ' . $label);
}
accept_check($xpath->query('//p[@class="catalog-request-status"]')->length===4, 'Returned and another student’s pending book have no own label');
foreach (['librarian'=>$clients['librarian'],'administrator'=>$clients['admin']] as $role=>$client) {
    [$code,$html] = accept_http($client, '/catalog.php?title=Pagination+fixture');
    accept_check($code===200 && !str_contains($html,'class="catalog-request-status"'), "$role catalog has no personal borrowing labels");
}
$statusClient = curl_init();
curl_setopt_array($statusClient,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>10]);
[$code,$html] = accept_http($statusClient, '/login.php');
[$code] = accept_http($statusClient, '/login.php', ['username'=>'catalog_status_other','password'=>$password,'csrf_token'=>accept_token($html)]);
accept_check($code===303, 'Other catalog student fixture login');
[$code,$html] = accept_http($statusClient, '/catalog.php?title=Pagination+fixture');
$dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
accept_check($code===200 && $xpath->query('//p[@class="catalog-request-status"]')->length===1
    && $xpath->query('//article[h2/a[text()="Pagination fixture 06"]]//p[@class="catalog-request-status"]')->length===1, 'Other student sees only own label, not another borrower’s statuses');
curl_close($statusClient);
