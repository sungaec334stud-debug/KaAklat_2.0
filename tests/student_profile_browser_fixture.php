<?php

// Operator-only fictional browser fixtures. Never deploy. Creates a NEW scratch DB; retains it.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
require_once dirname(__DIR__) . '/app/bootstrap.php';
$config = application_config()['database'];
$name = 'kaaklat_acceptance_' . bin2hex(random_bytes(6));
$db = database_connection($config);
$db->exec('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$config['database'] = $name;
$db = database_connection($config);
foreach ([dirname(__DIR__) . '/schema.sql', dirname(__DIR__) . '/migrations/001_book_categories_year.sql'] as $file) {
    $sql = preg_replace('/^--.*$/m', '', (string)file_get_contents($file));
    foreach (explode(';', $sql) as $statement) { if (trim($statement) !== '') { $db->exec($statement); } }
}
$password = bin2hex(random_bytes(18));
$hash = password_hash($password, PASSWORD_DEFAULT);
$s = $db->prepare('INSERT INTO users(user_id,username,display_name,password_hash,role,account_status,created_at) VALUES(?,?,?,?,?,?,?)');
foreach ([[1,'profile_browser_active','Fictional Student With A Long Display Name For Narrow Screen Wrapping Review','student','active'],
    [2,'profile_browser_pending','Fictional Pending Student','student','pending'],
    [3,'profile_browser_lib','Fictional Librarian','librarian','active'],
    [4,'profile_browser_admin','Fictional Administrator','administrator','active']] as [$id,$username,$display,$role,$status]) {
    $s->execute([$id,$username,$display,$hash,$role,$status,'2026-11-03 17:30:00']);
}
$root = dirname(__DIR__) . '/tmp/student-profile-browser-' . substr($name, -12);
mkdir($root, 0700);
$fixture = ['database'=>$name,'password'=>$password,'active'=>'profile_browser_active','pending'=>'profile_browser_pending',
    'librarian'=>'profile_browser_lib','admin'=>'profile_browser_admin','directory'=>$root,'covers'=>[]];
if (($argv[1] ?? '') === '--campus') {
    require_once dirname(__DIR__) . '/app/pickups.php';
    require_once dirname(__DIR__) . '/app/returns.php';
    $appConfig['session_name'] = 'kaaklat_fixture_seed';
    start_application_session();
    $db->exec("INSERT INTO categories(category_id,category_name) VALUES(1,'Fictional Literature')");
    $db->exec("INSERT INTO authors(author_id,author_name) VALUES(1,'Fictional Campus Author')");
    for ($book = 1; $book <= 8; $book++) {
        $cover = 'upload-' . bin2hex(random_bytes(16)) . '.jpg';
        $image = imagecreatetruecolor(400, 600);
        $tones = [[11,35,80],[71,101,136],[39,75,128],[87,110,144]];
        [$r,$g,$b] = $tones[($book - 1) % 4];
        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));
        $white = imagecolorallocate($image, 235, 242, 255);
        imagerectangle($image, 25, 25, 374, 574, $white);
        imagestring($image, 5, 60, 250, 'FICTIONAL CAMPUS', $white);
        imagestring($image, 5, 110, 285, 'BOOK ' . $book, $white);
        imagejpeg($image, dirname(__DIR__) . '/private/covers/' . $cover, 85);
        imagedestroy($image);
        $fixture['covers'][] = $cover;
        $db->prepare('INSERT INTO books(book_id,title,description,cover_path,category_id,publication_year) VALUES(?,?,?,?,1,2020)')
            ->execute([$book,'Fictional Campus Book ' . $book,'Wholly fictional book and cover for interface verification. This is not a real school record.', $cover]);
        $db->exec("INSERT INTO book_authors(book_id,author_id,credit_order) VALUES($book,1,1)");
        for ($copy = 1; $copy <= 3; $copy++) {
            $db->exec("INSERT INTO book_copies(book_id,copy_code) VALUES($book,'CAMPUS-$book-$copy')");
        }
    }
    foreach ([1,2,3,4,5] as $book) {
        $_SESSION = ['user_id'=>1,'session_version'=>1,'last_activity'=>time()];
        $request = create_borrow_request($db,$book,7,'Fictional campus interface verification request.',csrf_token());
        if ($book === 1) { continue; }
        $_SESSION = ['user_id'=>3,'session_version'=>1,'last_activity'=>time()];
        decide_borrow_request($db,$request,$book === 5 ? 'reject' : 'approve',7,$book === 5 ? 'Fictional review explanation.' : '',csrf_token());
        if (in_array($book,[3,4],true)) { record_pickup($db,$request,csrf_token()); }
        if ($book === 4) { record_return($db,$request,csrf_token()); }
    }
}
file_put_contents($root . '/fixture.json', json_encode($fixture, JSON_PRETTY_PRINT));
echo "New fictional browser database: $name\nPrivate fixture file: $root/fixture.json\n";
