<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
ob_start(); require __DIR__.'/shared_pagination_browser_fixture.php'; ob_end_clean();
putenv('KAAKLAT_ACCEPTANCE_DB='.$fixture['database']);
$server=proc_open([PHP_BINARY,'-S','127.0.0.1:8036','-t',dirname(__DIR__).'/public',__DIR__.'/acceptance_router.php'],
    [0=>['pipe','r'],1=>['file',$root.'/server.log','a'],2=>['file',$root.'/server.log','a']],$pipes);
if (!is_resource($server)) { throw new RuntimeException('Server failed.'); } fclose($pipes[0]);
$checks=0; $clients=[];
function page_check(bool $ok,string $label):void {global $checks;if(!$ok)throw new RuntimeException($label);$checks++;echo "PASS: $label\n";}
function page_http($client,string $path,?array $post=null):array {
    curl_setopt_array($client,[CURLOPT_URL=>'http://127.0.0.1:8036'.$path,CURLOPT_POSTFIELDS=>$post===null?null:http_build_query($post),CURLOPT_POST=>$post!==null]);
    $body=curl_exec($client);if($body===false)throw new RuntimeException(curl_error($client));
    return [curl_getinfo($client,CURLINFO_RESPONSE_CODE),$body];
}
try {
    $probe=curl_init();curl_setopt_array($probe,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>2]);
    for($i=0;$i<100;$i++){try{[$code]=page_http($probe,'/login.php');if($code===200)break;}catch(RuntimeException){}usleep(100000);}
    foreach(['active','pending','librarian','admin'] as $role){
        $c=curl_init();curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>15]);
        [$code,$html]=page_http($c,'/login.php');preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$html,$token);
        [$code]=page_http($c,'/login.php',['username'=>$fixture[$role],'password'=>$password,'csrf_token'=>$token[1]]);
        page_check($code===303,"$role login");$clients[$role]=$c;
    }
    $cases=[
        ['active','/requests.php?status=rejected','Request pages',45,20,'page'],
        ['librarian','/students.php?status=active','Student account pages',62,25,'page'],
        ['admin','/students.php?status=active','Student account pages',62,25,'page'],
        ['admin','/staff.php?q=Pager','Staff pages',41,20,'page'],
        ['admin','/audit.php?event=pagination.view&actor=profile_browser_lib','Audit pages',75,25,'page'],
        ['librarian','/review_requests.php?q=Pager','Pending request pages',45,20,'page'],
        ['librarian','/pickups.php?q=Pager','Pickup pages',45,20,'page'],
        ['librarian','/returns.php?q=Pager','Return pages',45,20,'page'],
        ['librarian','/manage_books.php?q=Pager','Managed book pages',61,12,'page'],
        ['librarian','/manage_books.php?id=1','Copy pages',101,50,'copies_page'],
        ['active','/catalog.php?q=Pager','Catalog pages',61,12,'page']
    ];
    foreach($cases as [$role,$path,$label,$total,$size,$pageKey]) {
        $last=(int)ceil($total/$size);
        foreach(array_unique([1,2,$last,9999]) as $asked){
            [$code,$html]=page_http($clients[$role],$path.'&'.$pageKey.'='.$asked);
            page_check($code===200,"$label page$asked HTTP200 (actual $code)");
            $dom=new DOMDocument();@$dom->loadHTML($html);$xp=new DOMXPath($dom);
            $nav=$xp->query('//nav[@aria-label="'.$label.'"]');page_check($nav->length===1,"$label shared navigation");
            $text=$nav->item(0)->textContent;$actual=min($asked,$last);
            page_check(str_contains($text,$total.' ')&&str_contains($text,'Page '.$actual.' of '.$last),"$label accurate filtered total and clamped page");
            page_check($xp->query('.//*[@aria-current="page"]',$nav->item(0))->length===1,"$label one current page");
            parse_str(parse_url($path,PHP_URL_QUERY),$expected);
            foreach($xp->query('.//a',$nav->item(0)) as $link){
                parse_str(parse_url($link->getAttribute('href'),PHP_URL_QUERY),$params);
                page_check(!array_diff_assoc($expected,$params) && (int)$params[$pageKey]>=1 && (int)$params[$pageKey]<=$last,"$label link retains allowed search/status/book context");
            }
        }
    }
    foreach([['pending','/requests.php'],['admin','/staff.php?q=NoMatches'],['librarian','/students.php?status=suspended'],['librarian','/returns.php?q=NoMatches']] as [$role,$path]) {
        [$code,$html]=page_http($clients[$role],$path);
        page_check($code===200&&str_contains($html,'Page 1 of 1')&&!str_contains($html,'rel="next"'),"$role empty filtered list has no next page");
    }
    foreach(['active','pending'] as $role)foreach(['/students.php','/staff.php','/audit.php','/pickups.php','/returns.php','/review_requests.php','/manage_books.php'] as $path){[$code]=page_http($clients[$role],$path);page_check($code===403,"$role denied $path");}
    foreach(['/pickups.php','/returns.php','/review_requests.php','/manage_books.php','/requests.php'] as $path){[$code]=page_http($clients['admin'],$path);page_check($code===403,"admin denied $path");}
    [$code]=page_http($clients['librarian'],'/staff.php');page_check($code===403,'librarian denied staff management');
    [$code]=page_http($probe,'/returns.php?page=2');page_check($code===303,'signed-out pagination requires login');
    [$code,$html]=page_http($clients['active'],'/requests.php?status=rejected&user_id=10');page_check($code===200&&str_contains($html,'45 requests')&&!str_contains($html,'Fictional pending review.'),'Student count and history cannot select another owner');
    file_put_contents($root.'/pagination-http-result.json',json_encode(['database'=>$fixture['database'],'checks'=>$checks,'fixture'=>$root],JSON_PRETTY_PRINT));
    echo "$checks pagination HTTP checks passed. Scratch retained: ".$fixture['database']."\nFixture: $root/fixture.json\n";
} finally {foreach($clients as $c)curl_close($c);if(isset($probe))curl_close($probe);proc_terminate($server);proc_close($server);}
