<?php
// CLI-only fictional data in a newly created acceptance database. Never deploy.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
require __DIR__ . '/student_profile_browser_fixture.php';
if (!preg_match('/^kaaklat_acceptance_[a-f0-9]{12}$/D', $fixture['database'])) { throw new RuntimeException('Scratch database required.'); }
$insertUser = $db->prepare('INSERT INTO users(user_id,username,display_name,password_hash,role,account_status) VALUES(?,?,?,?,?,?)');
for ($i=1; $i<=61; $i++) {
    $insertUser->execute([9+$i, 'pager_student_'.$i, 'Pager Student '.$i, $hash, 'student', 'active']);
}
for ($i=1; $i<=41; $i++) {
    $insertUser->execute([99+$i, 'pager_staff_'.$i, 'Pager Staff '.$i, $hash, 'librarian', 'active']);
}
$bookInsert=$db->prepare('INSERT INTO books(book_id,title,description,publication_year) VALUES(?,?,?,?)');
$copyInsert=$db->prepare('INSERT INTO book_copies(book_id,copy_code) VALUES(?,?)');
for ($i=1; $i<=61; $i++) {
    $bookInsert->execute([$i,'Pager Book '.sprintf('%03d',$i),'Fictional pagination verification title.',2000]);
    for ($j=1; $j<=($i===1?101:3); $j++) { $copyInsert->execute([$i,'PAGER-'.$i.'-'.$j]); }
}
$request=$db->prepare('INSERT INTO borrow_requests(student_id,book_id,requested_days,request_reason,decision,approved_days,decided_at,decided_by,decision_note) VALUES(?,?,7,?,?,?,?,?,?)');
$loan=$db->prepare('INSERT INTO loans(request_id,copy_id,checked_out_at,checked_out_by,due_date) VALUES(?,?,?,?,?)');
for ($i=1; $i<=45; $i++) {
    $student=9+$i;
    $request->execute([$student,$i+1,'Fictional pending review.','pending',null,null,null,null]);
    foreach (['pickup','return'] as $kind) {
        $book=(($i+($kind==='pickup'?5:10))%60)+2;
        $request->execute([$student,$book,'Fictional collection verification.','approved',7,gmdate('Y-m-d H:i:s'),3,null]);
        $id=(int)$db->lastInsertId();
        $s=$db->prepare('SELECT copy_id FROM book_copies WHERE book_id=? AND copy_code=?');
        $s->execute([$book,'PAGER-'.$book.'-'.($kind==='pickup'?1:2)]);
        $checked=$kind==='return'?gmdate('Y-m-d H:i:s'):null;
        $due=$kind==='return'?(new DateTimeImmutable('now',new DateTimeZone('Asia/Manila')))->modify('+7 days')->format('Y-m-d'):null;
        $loan->execute([$id,(int)$s->fetchColumn(),$checked,$kind==='return'?3:null,$due]);
    }
    $request->execute([1,$i,'Fictional student history.','rejected',null,gmdate('Y-m-d H:i:s'),3,'Fictional rejection for pagination history.']);
}
$audit=$db->prepare("INSERT INTO audit_events(actor_user_id,event_code,entity_type,event_summary) VALUES(3,'pagination.view','test','Fictional pagination verification.')");
for ($i=0; $i<75; $i++) { $audit->execute(); }
$fixture['pagination_copy_book']=1;
file_put_contents($root.'/fixture.json',json_encode($fixture,JSON_PRETTY_PRINT));
echo "Pagination fictional fixture ready.\n";
