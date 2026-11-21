// Actual shared header controls, only with explicitly named fictional acceptance fixtures.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));
assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]||root+'/tmp/header-theme');fs.mkdirSync(out,{recursive:true});
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8039','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9460','--user-data-dir='+out+'/chrome-profile','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));
(async()=>{
let tabs;for(let i=0;i<90;i++){try{tabs=await(await fetch('http://127.0.0.1:9460/json')).json();await fetch('http://127.0.0.1:8039/health.php');break}catch{await pause(100)}}
const {ws,call,ev,key,screenshot,errors}=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await call('Page.enable');await call('Runtime.enable');
async function until(expression){for(let i=0;i<100;i++){try{if(await ev(expression))return}catch{}await pause(70)}throw Error('Timed out: '+expression)}
async function go(route){await call('Page.navigate',{url:'http://127.0.0.1:8039'+route});await until('document.readyState==="complete"&&!!document.querySelector(".site-footer")');await pause(100)}
async function width(value){await call('Emulation.setDeviceMetricsOverride',{width:value,height:1000,deviceScaleFactor:1,mobile:false});await pause(100)}
const visible='[...document.querySelectorAll("[data-theme-toggle]")].filter(b=>b.getClientRects().length)';
let layouts=0,toggles=0;
for(const role of ['guest','active','pending','librarian','admin']){
 await call('Network.clearBrowserCookies');await width(1440);await go('/login.php');
 if(role!=='guest'){
  await ev('document.querySelector("#username").value='+JSON.stringify(fixture[role])+';document.querySelector("#password").value='+JSON.stringify(fixture.password)+';document.querySelector("button[type=submit]").click()');
  await until('document.readyState==="complete"&&document.body.classList.contains("is-authenticated")&&document.body.classList.contains("navigation-enhanced")&&document.documentElement.classList.contains("appearance-ready")');
 }
 for(const w of [320,360,768,1100,1440]){
  await width(w);assert.equal(await ev(visible+'.length'),1,'one visible control '+role+' '+w);
  assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no overflow '+role+' '+w);
  assert.ok(await ev('![...document.querySelectorAll(".header-nav a")].some(a=>a.getAttribute("href")==="/settings.php")'));
  if(role!=='guest'&&w<1100)assert.ok(await ev('document.querySelector(".mobile-current-page").getBoundingClientRect().height>0'));
  const box=await ev('(()=>{const r='+visible+'[0].getBoundingClientRect();return {width:r.width,height:r.height,left:r.left,right:r.right}})()');
  assert.ok(box.width>=44&&box.height>=44&&box.left>=0&&box.right<=w);
  if(role==='guest'&&w<=760)assert.ok(await ev('Math.abs('+visible+'[0].getBoundingClientRect().top-document.querySelector(".site-branding").getBoundingClientRect().top)<12'),'guest toggle stays on brand row');
  for(let i=0;i<2;i++){
   const old=await ev('document.documentElement.dataset.theme');
   await ev(visible+'[0].click()');
   const target=old==='dark'?'light':'dark';
   assert.equal(await ev('document.documentElement.dataset.theme'),target);
   assert.equal(await ev('localStorage.getItem("kaaklat.appearance")'),target);
   assert.ok(await ev(visible+'[0].getAnimations({subtree:true}).length>0'),'finite click animation');
   await pause(400);
   assert.equal(await ev('getComputedStyle('+visible+'[0].querySelector(".theme-'+(target==='dark'?'sun':'moon')+'")).opacity'),'1');
   toggles++;
  }
  layouts++;
 }
 if(role==='librarian'){
  await width(1440);await ev('if(document.documentElement.dataset.theme!=="dark")'+visible+'[0].click()');await pause(400);await screenshot(out+'/librarian-desktop-dark.png');
  await width(360);await screenshot(out+'/librarian-phone-dark.png');
 }
 const current=await ev('document.documentElement.dataset.theme');await go('/about.php');assert.equal(await ev('document.documentElement.dataset.theme'),current,'page persistence');
 await ev(visible+'[0].focus()');await key('Enter','Enter',13);assert.notEqual(await ev('document.documentElement.dataset.theme'),current,'keyboard toggle');
 await ev('for(let i=0;i<9;i++)'+visible+'[0].click()');await pause(400);assert.equal(await ev('document.documentElement.dataset.theme'),current,'rapid toggles converge');
}
await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});
await ev(visible+'[0].click()');assert.equal(await ev(visible+'[0].getAnimations({subtree:true}).length'),0,'reduced motion');
await ev('localStorage.setItem("kaaklat.appearance","system")');await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-color-scheme',value:'dark'}]});await go('/about.php');assert.equal(await ev('document.documentElement.dataset.theme'),'dark','legacy system');
const inject=await call('Page.addScriptToEvaluateOnNewDocument',{source:'Object.defineProperty(window,"localStorage",{get(){throw Error("blocked")}})'});
await go('/about.php');await ev(visible+'[0].click()');assert.ok(await ev('document.querySelector("#appearance-status").textContent.includes("could not be saved")'));await call('Page.removeScriptToEvaluateOnNewDocument',{identifier:inject.identifier});
await call('Emulation.setScriptExecutionDisabled',{value:true});await go('/about.php');assert.ok(await ev('[...document.querySelectorAll("[data-theme-toggle]")].every(b=>b.hidden)'));assert.equal(await ev('getComputedStyle(document.documentElement).colorScheme'),'dark');await call('Emulation.setScriptExecutionDisabled',{value:false});
assert.equal(errors.length,0);const report={layouts,toggles,roles:5,runtimeErrors:errors.length,checks:['one visible 44px header button','icon/color animation','keyboard Enter','rapid clicks','saved page preference','legacy system preference','blocked storage','no-script fallback','reduced motion','no Settings navigation','current mobile page retained'],database:fixture.database,limitations:'Chromium emulation; physical-device, Safari, screen-reader and actual 400% zoom remain manual.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));ws.close();
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{server.kill();chrome.kill()});
