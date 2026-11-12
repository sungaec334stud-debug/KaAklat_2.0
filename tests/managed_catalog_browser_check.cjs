// Isolated fictional configuration only. Exercises real native GET/POST forms.
const {spawn, execFileSync}=require('node:child_process');
const fs=require('node:fs'),assert=require('node:assert/strict');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const output=execFileSync('C:/php-8.4.14/php.exe',['tests/student_profile_browser_fixture.php','--campus'],{encoding:'utf8'});
const fixture=JSON.parse(fs.readFileSync(output.match(/Private fixture file: (.+)/)[1].trim(),'utf8'));
const base='http://127.0.0.1:8042';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8042','-t','public','tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9380','--user-data-dir='+fixture.directory+'/managed-chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
async function ready(url){for(let i=0;i<100;i++){try{return await fetch(url);}catch{}await pause(100);}throw Error('Not ready');}
(async()=>{
 await ready(base+'/login.php'); const tabs=await(await ready('http://127.0.0.1:9380/json/list')).json();
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);
 const {call,ev,key,screenshot,errors}=browser;await call('Page.enable');await call('Runtime.enable');
 async function until(expression){for(let i=0;i<100;i++){try{if(await ev(expression))return;}catch{}await pause(50);}throw Error('Timed out: '+expression);}
 async function go(path){await call('Page.navigate',{url:base+path});await until('document.readyState==="complete" && location.pathname==='+JSON.stringify(new URL(base+path).pathname));await pause(80);}
 await go('/login.php');await ev("document.querySelector('[name=username]').value="+JSON.stringify(fixture.librarian)+";document.querySelector('[name=password]').value="+JSON.stringify(fixture.password)+";document.querySelector('[name=password]').form.requestSubmit()");await until("location.pathname==='/'");
 let layouts=0;
 for(const theme of ['light','dark']){
  await go('/settings.php');await ev("document.querySelector('[name=appearance][value="+theme+"]').click()");
  for(const path of ['/manage_books.php','/manage_books.php?q=Fictional&id=1','/manage_books.php?new=1','/manage_books.php?q=NoMatches&id=1']){
   await go(path);
   for(const width of [1440,768,360]){
    await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});
    assert.ok(await ev('document.documentElement.scrollWidth<=document.documentElement.clientWidth'),'No overflow '+path+' '+width);
    assert.ok(await ev("!!document.querySelector('.managed-catalog') && !!document.querySelector('.managed-editor')"),'List and editor rendered together');
    if(width===1440)assert.ok(await ev("document.querySelector('.managed-editor').getBoundingClientRect().left>document.querySelector('.managed-catalog').getBoundingClientRect().right"),'Desktop columns are side by side');
    layouts++;
    if(path.includes('q=Fictional'))await screenshot(fixture.directory+'/managed-'+theme+'-'+width+'.png');
   }
  }
 }
 await call('Emulation.setDeviceMetricsOverride',{width:1440,height:1100,deviceScaleFactor:1,mobile:false});
 await go('/manage_books.php?q=Fictional');
 await call('Emulation.setScriptExecutionDisabled',{value:true});
 await ev("document.querySelector('.managed-select').focus()");await key('Enter');
 await until("document.readyState==='complete' && !!document.querySelector('#title')");
 assert.ok(await ev("new URL(location.href).searchParams.get('q')==='Fictional' && !!document.querySelector('.managed-catalog-record.is-selected')"),'Native no-JS selection retains search');
 assert.ok(await ev("document.querySelector('.managed-editor [name=csrf_token]').value.length>0 && document.querySelector('.managed-editor [name=book_id]').value==='1'"),'Original scoped POST and CSRF fields retained');
 await call('Emulation.setScriptExecutionDisabled',{value:false});
 await ev("document.querySelector('#title').value='';document.querySelector('#authors').value='Retained Fictional Author';document.querySelector('#title').form.submit()");
 await until("document.readyState==='complete' && !!document.querySelector('[role=alert]')");
 assert.ok(await ev("document.querySelector('#title').value==='' && document.querySelector('#authors').value==='Retained Fictional Author' && !!document.querySelector('.managed-catalog')"),'Invalid save retains fields beside list');
 const original='Fictional Campus Book 1';
 await ev("document.querySelector('#title').value="+JSON.stringify(original+' Updated')+";document.querySelector('#title').form.requestSubmit()");
 await until("document.readyState==='complete' && !!document.querySelector('.flash-success')");
 assert.ok(await ev("document.querySelector('#title').value.endsWith(' Updated') && new URL(location.href).searchParams.get('q')==='Fictional' && new URL(location.href).searchParams.get('id')==='1'"),'Successful save retains selected editor and catalog context');
 await ev("document.querySelector('[name=action][value=add_copies]').form.requestSubmit()");
 await until("document.readyState==='complete' && !!document.querySelector('.flash-success') && document.querySelector('.managed-catalog-record.is-selected .managed-availability').textContent.includes('4')");
 assert.ok(await ev("document.querySelector('.managed-editor').textContent.includes('4 active copies')"),'Copies and derived availability refresh in both panes');
 assert.equal(errors.length,0);
 const report={database:fixture.database,directory:fixture.directory,layouts,runtimeErrors:errors.length,checks:['two desktop columns','stacked narrow layout','native no-JS selection','search/selection retained','CSRF and book fields retained','invalid save retains input','successful save remains in split editor','add-copies refreshes derived counts']};
 fs.writeFileSync(fixture.directory+'/managed-browser-results.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));
})().catch(e=>{console.error(e);process.exitCode=1;}).finally(()=>{browser?.ws.close();chrome.kill();server.kill();});
