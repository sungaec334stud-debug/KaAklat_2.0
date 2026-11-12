// Fictional isolated fixture only; no npm dependency or pilot account access.
const {spawn}=require('node:child_process'),fs=require('node:fs'),assert=require('node:assert/strict');
const fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));
assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const base='http://127.0.0.1:8037';const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8037','-t','public','tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9364','--user-data-dir='+fixture.directory+'/pagination-chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const pause=ms=>new Promise(r=>setTimeout(r,ms));
async function ready(url){for(let i=0;i<100;i++){try{return await fetch(url)}catch{}await pause(100)}throw Error('Not ready')}
(async()=>{await ready(base+'/login.php');const tabs=await(await ready('http://127.0.0.1:9364/json/list')).json();const {ws,call,ev:rawEvaluate,key,screenshot,errors}=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);let paginationSelector='nav.catalog-pagination';const ev=expression=>rawEvaluate(expression.replaceAll('nav.catalog-pagination',paginationSelector));async function until(expression){for(let i=0;i<100;i++){try{if(await ev(expression))return}catch{}await pause(50)}throw Error('Timed out: '+expression)};async function go(path){await call('Page.navigate',{url:base+path});await until('document.readyState==="complete" && location.pathname==='+JSON.stringify(new URL(base+path).pathname));await pause(60)};const shot=name=>screenshot(fixture.directory+'/'+name+'.png');await call('Page.enable');await call('Runtime.enable');
let combinations=0,keyboardChecks=0;
const pages={
active:['/requests.php?status=rejected','/catalog.php?q=Pager'],
pending:['/requests.php','/catalog.php?q=Pager'],
librarian:['/students.php?status=active','/review_requests.php?q=Pager','/pickups.php?q=Pager','/returns.php?q=Pager','/manage_books.php?q=Pager','/manage_books.php?id=1','/catalog.php?q=Pager'],
admin:['/students.php?status=active','/staff.php?q=Pager','/audit.php?event=pagination.view&actor=profile_browser_lib','/catalog.php?q=Pager']};
for(const [role,paths] of Object.entries(pages)){
 await call('Network.clearBrowserCookies');await go('/login.php');
 await ev("document.querySelector('[name=username]').value="+JSON.stringify(fixture[role])+";document.querySelector('[name=password]').value="+JSON.stringify(fixture.password)+";document.querySelector('[name=password]').form.requestSubmit()");
 await until("location.pathname==='/' && !!document.querySelector('.dashboard')");
 for(const theme of ['light','dark']){
  await go('/settings.php');await ev("document.querySelector('[name=appearance][value="+theme+"]').click()");
  for(const path of paths){
   paginationSelector=path.includes('id=1')?'nav.catalog-pagination[aria-label="Copy pages"]':'nav.catalog-pagination:not([aria-label="Copy pages"])';
   await go(path);assert.ok(await ev("!!document.querySelector('nav.catalog-pagination .pagination-summary')"));
   for(const width of [1440,768,360]){
    await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});
    await ev("document.querySelector('nav.catalog-pagination').scrollIntoView({block:'center'})");
    assert.ok(await ev('document.documentElement.scrollWidth<=document.documentElement.clientWidth'),'No overflow '+role+path+width);
    assert.equal(await ev('document.documentElement.dataset.theme'),theme);
    assert.ok(await ev("[...document.querySelectorAll('nav.catalog-pagination a')].every(a=>a.getBoundingClientRect().height>=44 && a.getBoundingClientRect().width>=44)"),'44px controls');
    combinations++;
    if(role==='admin'&&path.startsWith('/staff.php')&&width!==768)await shot('pagination-'+theme+'-'+width);
   }
   const second=await ev("document.querySelector('nav.catalog-pagination [aria-label=\"Go to page 2\"]')?.getAttribute('href')");
   if(second){
    await ev("document.querySelector('nav.catalog-pagination [aria-label=\"Go to page 2\"]').focus()");
    await key('Tab','Tab',9);assert.ok(await ev("document.activeElement.matches('nav.catalog-pagination a')"),'Tab between native page links');
    await ev("document.querySelector('nav.catalog-pagination [aria-label=\"Go to page 2\"]').focus()");
    await call('Emulation.setScriptExecutionDisabled',{value:true});await key('Enter');
    const parameter=path.includes('id=1')?'copies_page':'page';
    await until("document.readyState==='complete' && new URL(location.href).searchParams.get("+JSON.stringify(parameter)+")==='2' && document.querySelector('nav.catalog-pagination .pagination-summary').textContent.includes('Page 2 of')");
    assert.ok(await ev("document.querySelector('nav.catalog-pagination a[rel=prev]').getAttribute('href').includes('"+parameter+"=1')"));
    await call('Emulation.setScriptExecutionDisabled',{value:false});keyboardChecks++;
   }
  }
 }
 console.log('PASS shared pagination browser role: '+role);
}
assert.equal(errors.length,0);
const report={database:fixture.database,combinations,keyboardNoJsNavigations:keyboardChecks,runtimeErrors:errors.length,widths:[1440,768,360],themes:['light','dark']};
fs.writeFileSync(fixture.directory+'/pagination-browser-result.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));ws.close();
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{chrome.kill();server.kill();});
