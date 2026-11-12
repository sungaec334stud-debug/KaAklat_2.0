<?php
declare(strict_types=1);
require __DIR__.'/catalog_integration_test.php';
require_once __DIR__.'/catalog_discovery_test.php';
$data=[[501,'Sort fixture Zebra',2000],[502,'Sort fixture Alpha',1990],[503,'Sort fixture Unknown',null],[504,'Sort fixture Same',2000],[505,'Sort fixture Same',2000],[506,'Sort fixture Beta',2010],[507,'Sort fixture 100%_!',2000]];
foreach($data as [$id,$title,$year]){$pdo->prepare('INSERT INTO books(book_id,title,description,publication_year) VALUES (?,?,?,?)')->execute([$id,$title,'Fictional discovery test',$year]);$pdo->prepare('INSERT INTO book_copies(copy_id,book_id,archived_at) VALUES (?,?,NULL)')->execute([$id,$id]);}
$pdo->exec("INSERT INTO authors VALUES (501,'Sort Author 100%_!'),(502,'Second Sort Author')");$pdo->exec('INSERT INTO book_authors VALUES (501,501,1),(501,502,2)');
$ids=static fn(array $f=[])=>array_map('intval',array_column(catalog_books($pdo,'Sort fixture',1,$f)['books'],'book_id'));
discovery_check($ids(['sort'=>'title_desc'])===[501,503,504,505,506,502,507],'Title descending with stable ID tie');
discovery_check($ids(['sort'=>'year_desc'])===[506,507,504,505,501,502,503],'Newest first unknown last/title/ID ties');
discovery_check($ids(['sort'=>'year_asc'])===[502,507,504,505,501,506,503],'Oldest first unknown last/title/ID ties');
discovery_check(count($ids(['year_from'=>2000,'year_to'=>2000]))===4,'Inclusive equal bounds');
discovery_check(count($ids(['year_from'=>2000]))===5&&count($ids(['year_to'=>1990]))===1,'Open-ended bounds exclude unknown');
discovery_check(catalog_books($pdo,'Sort Author',1)['total']===1,'Multiple matching author credits count one title');
discovery_check(catalog_books($pdo,'100%_!',1)['total']===2,'Literal wildcard text searches title OR author');
discovery_check(count($ids(['available'=>'0']))===0,'New copies initially available');
$pdo->exec('INSERT INTO borrow_requests VALUES (501,UTC_TIMESTAMP())');
discovery_check(count($ids(['available'=>'1']))===7,'Pending record does not reserve');
$pdo->exec('INSERT INTO loans VALUES (501,501,NULL,NULL,NULL)');
discovery_check($ids(['available'=>'0'])===[501],'Approved uncollected copy unavailable');
$pdo->exec('UPDATE loans SET checked_out_at=UTC_TIMESTAMP() WHERE copy_id=501');discovery_check($ids(['available'=>'0'])===[501],'Pickup does not change unavailability');
$pdo->exec('UPDATE loans SET returned_at=UTC_TIMESTAMP() WHERE copy_id=501');discovery_check($ids(['available'=>'0'])===[],'Return restores availability');
$pdo->exec('UPDATE loans SET returned_at=NULL,checked_out_at=NULL,cancelled_at=UTC_TIMESTAMP() WHERE copy_id=501');discovery_check($ids(['available'=>'0'])===[],'Cancellation releases availability');
$pdo->exec('UPDATE loans SET cancelled_at=NULL WHERE copy_id=501');$pdo->exec('UPDATE borrow_requests SET decided_at=UTC_TIMESTAMP()-INTERVAL 48 HOUR WHERE request_id=501');discovery_check($ids(['available'=>'0'])===[],'Exact expiry boundary releases availability');
$pdo->exec('UPDATE book_copies SET archived_at=UTC_TIMESTAMP() WHERE copy_id=501');discovery_check($ids(['available'=>'0'])===[501],'Only archived copies means unavailable');
$pdo->exec('UPDATE books SET archived_at=UTC_TIMESTAMP() WHERE book_id=501');discovery_check($ids(['available'=>'0'])===[],'Archived title never listed');
for($i=600;$i<625;$i++)$pdo->exec("INSERT INTO books(book_id,title,description,publication_year) VALUES ($i,'Page fixture Same','Fixture',2000)");
$seen=[];for($p=1;$p<=3;$p++){$result=catalog_books($pdo,'Page fixture', $p,['sort'=>'year_desc','available'=>'0']);discovery_check($result['total']===25,'Correct distinct total for unavailable titles');$seen=array_merge($seen,array_map('intval',array_column($result['books'],'book_id')));}
discovery_check($seen===range(600,624),'Stable pagination with identical title/year and zero copies');
echo "PASS: Catalog sorting/ranges/availability lifecycle/distinct totals and stable pagination.\n";
