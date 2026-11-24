const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]);fs.mkdirSync(out,{recursive:true});const base='http://127.0.0.1:8059';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8059','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9475','--user-data-dir='+out+'/chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
(async()=>{
 let tabs;for(let i=0;i<100;i++){try{tabs=await(await fetch('http://127.0.0.1:9475/json')).json();await fetch(base+'/login.php');break}catch{await pause(100)}}
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const{call,ev,errors}=browser;await call('Page.enable');await call('Runtime.enable');let roles=0,backChecks=0;
 async function until(expr){for(let i=0;i<150;i++){try{if(await ev(expr))return}catch{}await pause(40)}throw Error('Timed out: '+expr)}
 async function go(route){const old=await ev('performance.timeOrigin');await call('Page.navigate',{url:base+route});await until('performance.timeOrigin!=='+old+'&&document.readyState==="complete"&&!!document.querySelector(".site-footer")');await pause(120)}
 const dashboard='location.pathname==="/"&&document.readyState==="complete"&&document.body.classList.contains("is-authenticated")&&!document.querySelector(".auth-main form")';
 for(const width of [375,1440])for(const role of ['active','pending','librarian','admin']){
  await call('Network.clearBrowserCookies');await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});await go('/register.php');await go('/login.php');
  await ev(`document.querySelector('#username').value=${JSON.stringify(fixture[role])};document.querySelector('#password').value=${JSON.stringify(fixture.password)};document.querySelector('button[type=submit]').click()`);await until(dashboard);
  await go('/login.php');assert.ok(await ev(dashboard),'signed-in login goes to dashboard '+role);
  await go('/register.php');assert.ok(await ev(dashboard),'signed-in register goes to dashboard '+role);
  await go('/catalog.php');await go('/about.php');await ev('history.back()');await until('location.pathname==="/catalog.php"&&document.readyState==="complete"&&document.body.classList.contains("is-authenticated")');backChecks++;
  await ev('history.back()');await until(dashboard);backChecks++;
  // Revisit the pre-login entries through actual browser history.
  for(let i=0;i<4;i++){await ev('history.back()');await until('document.readyState==="complete"&&document.body.classList.contains("is-authenticated")&&!document.querySelector(".auth-main form")');await pause(150);assert.ok(await ev('!["/login.php","/register.php"].includes(location.pathname)'),'Back does not leave a signed-in entry form');backChecks++;}
  await go('/logout.php');await ev('document.querySelector("button[name=confirm]").click()');await until('location.pathname==="/login.php"&&document.readyState==="complete"&&document.body.classList.contains("is-guest")');assert.ok(await ev('!!document.querySelector("#username")&&!document.querySelector(".sidebar-identity")'),'logout shows genuine guest login');roles++;
 }
 // No JavaScript: server redirects still protect direct signed-in entry requests.
 await go('/login.php');await ev(`document.querySelector('#username').value=${JSON.stringify(fixture.active)};document.querySelector('#password').value=${JSON.stringify(fixture.password)};document.querySelector('button[type=submit]').click()`);await until(dashboard);await call('Page.navigate',{url:'about:blank'});await pause(100);await call('Emulation.setScriptExecutionDisabled',{value:true});await go('/register.php');assert.ok(await ev(dashboard),'no-JS signed-in entry redirects');
 assert.equal(errors.length,0);const report={roleViewportCases:roles,backChecks,runtimeErrors:errors.length,checks:['active/pending student, librarian and admin at375/1440','authenticated Login/Registration redirect without auth forms/sidebar mixture','native Back between recent app pages','actual traversal of pre-login entries preserves login','explicit logout returns true guest login','server guard without JavaScript'],limitations:'Chromium emulation; actual BFCache use depends on browser. Physical devices, Safari, screen readers and actual400% zoom not rerun.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify({roles,backChecks,runtimeErrors:errors.length}));
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{browser?.ws.close();chrome.kill();server.kill()});
