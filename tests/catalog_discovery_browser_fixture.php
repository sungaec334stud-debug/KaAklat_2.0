<?php
declare(strict_types=1);
ob_start();require __DIR__.'/student_profile_browser_fixture.php';$output=ob_get_clean();
$db->exec("INSERT INTO categories(category_name) VALUES('Discovery category')");$category=(int)$db->lastInsertId();$db->exec("INSERT INTO authors(author_name) VALUES('Discovery Browser Author')");$author=(int)$db->lastInsertId();$ids=[];
for($i=1;$i<=25;$i++){$db->prepare('INSERT INTO books(title,description,category_id,publication_year) VALUES(?,?,?,2000)')->execute(['Discovery fixture '.sprintf('%02d',$i),'Wholly fictional unavailable title used for catalog browser checks.',$category]);$id=(int)$db->lastInsertId();$ids[]=$id;$db->prepare('INSERT INTO book_authors(book_id,author_id,credit_order) VALUES(?,?,1)')->execute([$id,$author]);}
$fixture['discovery_category']=$category;$fixture['discovery_ids']=$ids;file_put_contents($root.'/fixture.json',json_encode($fixture,JSON_PRETTY_PRINT));echo $output;
