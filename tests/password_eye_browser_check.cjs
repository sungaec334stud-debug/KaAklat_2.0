const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));
assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]||root+'/tmp/password-eye');fs.mkdirSync(out,{recursive:true});
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8047','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9462','--user-data-dir='+out+'/chrome-profile','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));
(async()=>{
let tabs;for(let i=0;i<90;i++){try{tabs=await(await fetch('http://127.0.0.1:9462/json')).json();await fetch('http://127.0.0.1:8047/login.php');break}catch{await pause(100)}}
const {ws,call,ev,key,screenshot,errors}=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await call('Page.enable');await call('Runtime.enable');await call('Emulation.setFocusEmulationEnabled',{enabled:true});
async function until(expression){for(let i=0;i<100;i++){try{if(await ev(expression))return}catch{}await pause(70)}throw Error('Timed out: '+expression)}
async function go(route){await call('Page.navigate',{url:'http://127.0.0.1:8047'+route});await until('document.readyState==="complete"&&!!document.querySelector(".site-footer")');await pause(150)}
let layouts=0,fieldCases=0;
for(const route of ['/login.php','/register.php','/reset_password.php'])for(const theme of ['light','dark'])for(const width of [320,768,1440]){
 await call('Emulation.setDeviceMetricsOverride',{width,height:1100,deviceScaleFactor:1,mobile:false});await go(route);
 await ev('localStorage.setItem("kaaklat.appearance",'+JSON.stringify(theme)+');document.documentElement.dataset.theme='+JSON.stringify(theme));await pause(260);
 assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no overflow '+route+' '+width);
 const ids=await ev('[...document.querySelectorAll("[data-password]")].map(b=>b.dataset.password)');assert.equal(ids.length,route==='/reset_password.php'?3:1);
 await ev('window.eyeSubmits=0;document.addEventListener("submit",()=>window.eyeSubmits++)');
 for(const id of ids){
  const button='document.querySelectorAll("[data-password]")['+ids.indexOf(id)+']',field='document.getElementById('+JSON.stringify(id)+')';
  const value=id==='secret'?'a'.repeat(64):'Fictional phrase 123';
  await ev(field+'.value='+JSON.stringify(value));
  assert.equal(await ev(field+'.type'),'password');
  assert.equal(await ev(button+'.textContent.trim()'),'');
  assert.equal(await ev(button+'.getAttribute("aria-controls")'),id);
  assert.ok(await ev('(()=>{const b='+button+'.getBoundingClientRect(),f='+field+'.getBoundingClientRect();return b.width>=44&&b.height>=44&&b.left>=f.left&&b.right<=f.right&&Math.abs((b.top+b.height/2)-(f.top+f.height/2))<3})()'),'44px eye aligned inside field');
  await ev(button+'.click()');assert.equal(await ev(field+'.type'),'text');assert.equal(await ev(button+'.getAttribute("aria-pressed")'),'true');
  assert.ok(await ev(button+'.getAttribute("aria-label").startsWith("Hide ")'));
  assert.ok(await ev(button+'.getAnimations({subtree:true}).length>0'),'eye opens with finite animation');
  await pause(280);assert.equal(await ev('getComputedStyle('+button+'.querySelector(".password-eye-open")).opacity'),'1');
  assert.ok(await ev('[...document.querySelectorAll("[data-password]")].filter(b=>b.dataset.password!=='+JSON.stringify(id)+').every(b=>document.getElementById(b.dataset.password).type==="password")'),'other fields stay masked');
  await ev(button+'.click()');await pause(280);assert.equal(await ev(field+'.type'),'password');assert.equal(await ev('getComputedStyle('+button+'.querySelector(".password-eye-closed")).opacity'),'1');
  await ev(button+'.focus()');await key('Enter','Enter',13);assert.equal(await ev(field+'.type'),'text');await key(' ','Space',32);assert.equal(await ev(field+'.type'),'password');
  await ev('for(let i=0;i<8;i++)'+button+'.click()');assert.equal(await ev(field+'.type'),'password');assert.equal(await ev(field+'.value'),value);assert.equal(await ev('window.eyeSubmits'),0,'visibility never submits');fieldCases++;
  await ev(field+'.value=""');
 }
 if(route==='/reset_password.php'){
  assert.ok(await ev('!document.querySelector("main").textContent.includes("UTF-8 bytes")'));
  assert.ok(await ev('document.querySelector(".recovery-code-help").open===false'));
  await ev('document.querySelector(".recovery-code-help summary").focus()');await key('Enter','Enter',13);assert.ok(await ev('document.querySelector(".recovery-code-help").open'));
  if(width===1440||width===320)await screenshot(out+'/recovery-'+theme+'-'+width+'.png');
 }
 layouts++;
}
await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});await go('/reset_password.php');
await ev('document.querySelector("[data-password]").click()');assert.equal(await ev('document.querySelector("[data-password]").getAnimations({subtree:true}).length'),0);assert.equal(await ev('document.querySelector("#secret").type'),'text');
await call('Emulation.setScriptExecutionDisabled',{value:true});await go('/reset_password.php');assert.ok(await ev('[...document.querySelectorAll("[data-password]")].every(b=>b.hidden)'));assert.ok(await ev('[...document.querySelectorAll(".password-wrap input")].every(f=>f.type==="password")'));assert.ok(await ev('!document.querySelector("button[type=submit]").disabled'));await call('Emulation.setScriptExecutionDisabled',{value:false});
await go('/reset_password.php');await ev('document.querySelector("#secret").value="a".repeat(64);document.querySelector("#password").value="Fictional phrase 123";document.querySelector("#password_confirmation").value="Different phrase 456";document.querySelector("button[type=submit]").click()');
await until('document.readyState==="complete"&&!!document.querySelector(".error-summary")');assert.ok(await ev('document.querySelector(".error-summary").textContent.includes("match")'));assert.ok(await ev('[...document.querySelectorAll(".password-wrap input")].every(f=>f.value===""&&f.type==="password")'),'failed recovery echoes no secrets or passwords');
assert.equal(errors.length,0);const report={layouts,fieldCases,runtimeErrors:errors.length,checks:['five independent masked fields across three routes','44px centered icon controls','open/close animations and reduced motion','Enter and Space','rapid clicks/value preservation','no accidental submit','collapsed recovery help','plain accurate password/code directions','no-script submission available','mismatch errors and secret non-echo'],database:fixture.database,limitations:'Chromium emulation; physical-device, Safari, actual400% zoom and screen-reader acceptance remain manual.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));ws.close();
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{chrome.kill();server.kill()});
