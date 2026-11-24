const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]);fs.mkdirSync(out,{recursive:true});const base='http://127.0.0.1:8057';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8057','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9473','--user-data-dir='+out+'/chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
(async()=>{
 let tabs;for(let i=0;i<100;i++){try{tabs=await(await fetch('http://127.0.0.1:9473/json')).json();await fetch(base+'/login.php');break}catch{await pause(100)}}
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const{call,ev,errors}=browser;await call('Page.enable');await call('Runtime.enable');
 async function until(expr){for(let i=0;i<100;i++){try{if(await ev(expr))return}catch{}await pause(40)}throw Error('Timed out '+expr)}
 async function go(route){const old=await ev('performance.timeOrigin');await call('Page.navigate',{url:base+route});await until('performance.timeOrigin!=='+old+'&&document.readyState==="complete"&&!!document.querySelector(".site-footer")');}
 await go('/login.php');await ev(`document.querySelector('#username').value=${JSON.stringify(fixture.librarian)};document.querySelector('#password').value=${JSON.stringify(fixture.password)};document.querySelector('button[type=submit]').click()`);await until('location.pathname!=="/login.php"&&document.readyState==="complete"');let layouts=0;
 for(const theme of ['light','dark'])for(const width of [375,768,1440]){
  await call('Emulation.setDeviceMetricsOverride',{width,height:1100,deviceScaleFactor:1,mobile:false});await ev(`localStorage.setItem('kaaklat.appearance',${JSON.stringify(theme)})`);await go('/review_requests.php');
  for(const days of [1,7,30])assert.ok(await ev(`(()=>{const f=document.querySelector('input[name=days][max="${days}"]');return f&&f.value==="${days}"&&f.min==="1"&&!!document.getElementById(f.getAttribute('aria-describedby'))})()`),'requested bound/default/help '+days);
  await ev('document.querySelector("input[name=days][max=\\"7\\"]").closest(".workspace-record")?.querySelector("summary").click()');await pause(210);
  await ev('window.field=document.querySelector("input[name=days][max=\\"7\\"]");field.value="8";field.form.querySelector("[name=note]").value="Cannot extend even with an explanation";field.form.querySelector("button[value=approve]").click()');
  assert.ok(await ev('field.validity.rangeOverflow&&!!field.form.querySelector(".error-summary")'),'browser blocks longer approval');assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no overflow');layouts++;
 }
 // Bypass client validation deliberately: the service must still reject the extension.
 const old=await ev('performance.timeOrigin');await ev('const form=field.form;const action=document.createElement("input");action.type="hidden";action.name="action";action.value="approve";form.append(action);form.submit()');await until('performance.timeOrigin!=='+old+'&&document.readyState==="complete"');
 assert.ok(await ev('document.body.textContent.includes("cannot exceed")&&!!document.querySelector("input[name=days][aria-invalid=true]")'),'server rejects and associates duration error');
 assert.equal(errors.length,0);const report={layouts,runtimeErrors:errors.length,checks:['1/7/30 requested bounds and defaults','Light/Dark375/768/1440','linked duration guidance','native overlong validation','forged native POST rejected by server with field error','no overflow'],limitations:'Chromium emulation only; physical-device, Safari, screen-reader and actual400% zoom checks not rerun.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify({layouts,runtimeErrors:errors.length}));
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{browser?.ws.close();chrome.kill();server.kill()});
