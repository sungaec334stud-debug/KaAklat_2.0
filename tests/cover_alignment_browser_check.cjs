// Real pages, fictional scratch records only; no pilot writes or cover-file removal.
const {spawn,execFileSync}=require('node:child_process');
const fs=require('node:fs'),assert=require('node:assert/strict');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const output=execFileSync('C:/php-8.4.14/php.exe',['tests/student_profile_browser_fixture.php','--campus'],{encoding:'utf8'});
const fixtureFile=output.match(/Private fixture file: (.+)/)[1].trim();
const fixture=JSON.parse(fs.readFileSync(fixtureFile,'utf8'));
execFileSync('C:/php-8.4.14/php.exe',['-r',`require 'app/bootstrap.php';$f=json_decode(file_get_contents($argv[1]),true);if(!preg_match('/^kaaklat_acceptance_[a-f0-9]{12}$/D',$f['database']))exit(1);$c=application_config()['database'];$c['database']=$f['database'];$d=database_connection($c);$d->exec("UPDATE users SET created_at='2026-10-30 12:00:00',school_student_id='06-0001-001' WHERE user_id=1");$d->exec("UPDATE users SET created_at='2026-10-30 12:00:00',school_student_id='06-0001-002',account_status='suspended' WHERE user_id=2");$d->exec("INSERT INTO audit_events(actor_user_id,event_code,entity_type,entity_id,event_summary,occurred_at) VALUES(3,'student.activate','users',1,'Fictional activation.','2026-10-31 17:30:00'),(3,'student.activate','users',2,'Fictional activation.','2026-10-31 17:30:00'),(3,'student.suspend','users',2,'Fictional suspension.','2026-11-01 02:00:00')");for($i=9;$i<=28;$i++){$d->prepare('INSERT INTO books(title,description,category_id) VALUES(?,?,1)')->execute(['Fictional Paging Title '.$i,'Fictional cover and alignment verification.']);}`,fixtureFile]);
const base='http://127.0.0.1:8044';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8044','-t','public','tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9382','--user-data-dir='+fixture.directory+'/cover-alignment-chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
async function ready(url){for(let i=0;i<100;i++){try{return await fetch(url);}catch{}await pause(100);}throw Error('Not ready');}
(async()=>{
 await ready(base+'/login.php');const tabs=await(await ready('http://127.0.0.1:9382/json/list')).json();
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const {call,ev,screenshot,errors}=browser;
 await call('Page.enable');await call('Runtime.enable');await call('Network.enable');
 async function until(expression){for(let i=0;i<100;i++){try{if(await ev(expression))return;}catch{}await pause(60);}await screenshot(fixture.directory+'/cover-failure.png');console.error(await ev("({path:location.pathname,images:[...document.querySelectorAll('.circulation-cover img')].map(i=>({src:i.getAttribute('src'),hidden:i.hidden,complete:i.complete,natural:i.naturalWidth,y:i.getBoundingClientRect().y}))})"));throw Error('Timed out: '+expression);}
 async function go(path){await call('Page.navigate',{url:base+path});await until('document.readyState==="complete" && location.pathname==='+JSON.stringify(new URL(base+path).pathname));await pause(100);}
 async function login(role){await call('Network.clearBrowserCookies');await go('/login.php');await ev("document.querySelector('[name=username]').value="+JSON.stringify(fixture[role])+";document.querySelector('[name=password]').value="+JSON.stringify(fixture.password)+";document.querySelector('[name=password]').form.requestSubmit()");await until("location.pathname==='/' && !!document.querySelector('.dashboard')");}
 await login('librarian');let layouts=0;
 for(const theme of ['light','dark']){
  await go('/settings.php');await ev("document.querySelector('[name=appearance][value="+theme+"]').click()");
  for(const path of ['/review_requests.php','/pickups.php','/returns.php','/students.php?status=active','/students.php?status=suspended','/manage_books.php?id=1&page=3']){
   await go(path);
   for(const width of [1440,768,360]){
    await call('Emulation.setDeviceMetricsOverride',{width,height:1100,deviceScaleFactor:1,mobile:false});
    assert.ok(await ev('document.documentElement.scrollWidth<=document.documentElement.clientWidth'),'No overflow '+path+' '+width);layouts++;
   }
   await call('Emulation.setDeviceMetricsOverride',{width:1440,height:1100,deviceScaleFactor:1,mobile:false});
   await ev("if(document.querySelector('.workspace-inspector')?.hidden)document.querySelector('.workspace-record > summary')?.click()");
   if(['/review_requests.php','/pickups.php','/returns.php'].includes(path)){
    await until("[...document.querySelectorAll('.workspace-list .circulation-cover img, .workspace-inspector .circulation-cover img')].filter(i=>i.getClientRects().length).every(i=>i.complete&&i.naturalWidth>0)");
    assert.ok(await ev("document.querySelectorAll('.workspace-list .circulation-cover img').length>0 && document.querySelector('.workspace-inspector .circulation-cover img').naturalWidth>0"),'Real cover in row and selected panel');
   }
   if(path.includes('students')){
    assert.ok(await ev("document.querySelector('.workspace-inspector .student-account-facts').textContent.includes('06-0001-00') && document.querySelector('.workspace-inspector .student-account-facts').textContent.includes('Nov 1, 2026 1:30 am')"),'Librarian ID and recorded activation timestamp');
    if(path.includes('suspended'))assert.ok(await ev("document.querySelector('.workspace-inspector .student-account-facts').textContent.includes('Last suspended') && document.querySelector('.workspace-inspector .student-account-facts').textContent.includes('Nov 1, 2026 10:00 am')"),'Recorded suspension timestamp');
   }
   if(path.includes('manage_books')){
    assert.ok(await ev("[...document.querySelectorAll('.managed-title > .book-placeholder')].every(n=>n.getBoundingClientRect().height<=54)"),'Missing-cover placeholders stay thumbnail-sized');
    assert.ok(await ev("(()=>{const c=document.querySelector('.managed-catalog').getBoundingClientRect();const nodes=[...document.querySelectorAll('.managed-pages-wrap a,.managed-pages-wrap .pagination-current')];return nodes.length>3&&Math.max(...nodes.map(n=>n.getBoundingClientRect().bottom))<=c.bottom-20;})()"),'Pagination clears the panel bottom');
    assert.ok(await ev("(()=>{const h=[...document.querySelectorAll('.managed-column-headings>span')];return [...document.querySelectorAll('.managed-catalog-record')].every(r=>[r.querySelector('.managed-category'),r.querySelector('.managed-availability')].every((n,i)=>{const a=n.getBoundingClientRect(),b=h[i+1].getBoundingClientRect();return Math.abs((a.left+a.right-b.left-b.right)/2)<1&&getComputedStyle(n).textAlign==='center';}));})()"),'Column headers and values share centers');
   }
   await screenshot(fixture.directory+'/covers-'+path.replace(/\W/g,'')+'-'+theme+'.png');
  }
 }
 await go('/review_requests.php');await call('Network.setCacheDisabled',{cacheDisabled:true});await call('Network.setBlockedURLs',{urls:['*/cover.php?id=1*']});
 await ev("document.querySelectorAll('.circulation-cover img').forEach(i=>i.src=i.src+'&fault=1')");
 await until("[...document.querySelectorAll('.circulation-cover img')].every(i=>i.hidden)");
 assert.ok(await ev("[...document.querySelectorAll('.circulation-cover-fallback')].every(n=>getComputedStyle(n).display!=='none')"),'Unavailable image has honest fallback');
 await call('Network.setBlockedURLs',{urls:[]});
 await login('admin');await go('/students.php?status=active');
 assert.ok(await ev("!document.querySelector('main').textContent.includes('06-0001-001') && document.querySelector('.student-account-facts').textContent.includes('First activated')"),'Existing administrator ID privacy retained; dates remain available');
 assert.equal(errors.length,0);
 const report={database:fixture.database,directory:fixture.directory,layouts,runtimeErrors:errors.length,checks:['loaded protected covers in list and inspector','light/dark and narrow layouts','centered category/availability columns','pagination bottom spacing','librarian school IDs','recorded activation/suspension Manila times','administrator ID exclusion','broken-cover fallback']};
 fs.writeFileSync(fixture.directory+'/cover-alignment-results.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));
})().catch(e=>{console.error(e);process.exitCode=1;}).finally(()=>{browser?.ws.close();chrome.kill();server.kill();});
