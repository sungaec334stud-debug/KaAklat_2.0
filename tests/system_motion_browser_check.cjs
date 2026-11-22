// Isolated fictional accounts only. Exercise native navigation and measured disclosure layout.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));
assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]);fs.mkdirSync(out,{recursive:true});const base='http://127.0.0.1:8053';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8053','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9469','--user-data-dir='+out+'/chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
(async()=>{
 let tabs;for(let i=0;i<100;i++){try{tabs=await(await fetch('http://127.0.0.1:9469/json')).json();await fetch(base+'/login.php');break}catch{await pause(100)}}
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const{call,ev,key,screenshot,errors}=browser;
 await call('Page.enable');await call('Runtime.enable');await call('Emulation.setFocusEmulationEnabled',{enabled:true});
 await call('Page.addScriptToEvaluateOnNewDocument',{source:`window.motionProbe={};addEventListener('pagereveal',e=>{if(!e.viewTransition)return;e.viewTransition.ready.then(()=>{motionProbe.names=document.getAnimations().map(a=>a.effect.pseudoElement||'')},()=>{});e.viewTransition.finished.then(()=>motionProbe.finished=true,()=>motionProbe.finished=true)});`});
 async function until(expr){for(let i=0;i<160;i++){try{if(await ev(expr))return}catch{}await pause(35)}throw Error('Timed out: '+expr)}
 async function go(route){const old=await ev('performance.timeOrigin');await call('Page.navigate',{url:base+route});await until('performance.timeOrigin!=='+old+'&&document.readyState==="complete"&&!!document.querySelector(".site-footer")');await pause(80)}
 async function login(role){await call('Network.clearBrowserCookies');await go('/login.php');await ev(`document.querySelector('#username').value=${JSON.stringify(fixture[role])};document.querySelector('#password').value=${JSON.stringify(fixture.password)};document.querySelector('button[type=submit]').click()`);await until('location.pathname!=="/login.php"&&document.readyState==="complete"&&document.body.classList.contains("is-authenticated")');await pause(100)}
 const roles={active:['/catalog.php','/requests.php','/profile.php','/about.php','/'],pending:['/catalog.php','/profile.php','/about.php','/'],librarian:['/catalog.php','/manage_books.php','/review_requests.php','/pickups.php','/returns.php','/students.php','/recover_account.php','/about.php','/'],admin:['/catalog.php','/staff.php','/students.php','/audit.php','/recover_account.php','/about.php','/']};
 let transitions=0,disclosures=0;const measurements=[];
 for(const theme of (process.argv.includes('--tail-only')?[]:['light','dark']))for(const width of [375,768,1440])for(const[role,routes]of Object.entries(roles)){
  await call('Emulation.setDeviceMetricsOverride',{width,height:1100,deviceScaleFactor:1,mobile:false});await login(role);await ev(`localStorage.setItem('kaaklat.appearance',${JSON.stringify(theme)});document.documentElement.dataset.theme=${JSON.stringify(theme)}`);
  assert.ok(await ev('!!document.querySelector("script[src*=page-navigation]")'),'signed-in pages load shared native transition observer');
  for(const route of routes){
   if(width<1100)await ev('document.querySelector("#navigation-toggle").click()');
   await ev(`document.querySelector('.header-nav a[href=${JSON.stringify(route)}]').click()`);
   await until('location.pathname==='+JSON.stringify(route)+'&&motionProbe.finished');
   assert.ok(await ev('motionProbe.names?.some(n=>n.includes("root"))'),'real native page crossfade '+role+route);
   if(width>=1100)assert.ok(await ev('motionProbe.names?.some(n=>n.includes("guest-tab"))'),'desktop active marker moves');
   assert.ok(await ev('!document.documentElement.classList.contains("page-navigation-pending")&&!document.documentElement.classList.contains("page-transition-active")'),'pending clears');
   assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no overflow');transitions++;
  }
 }
 async function exercise(route,selector,width){
  await go(route);await ev(`window.testDetails=document.querySelector(${JSON.stringify(selector)});testDetails.open=false;window.submitCount=0;document.addEventListener('submit',()=>submitCount++);`);await pause(30);
  await ev('testDetails.closest(".workspace-record")?.querySelector(":scope > summary").click()');
  await pause(220);
  assert.ok(await ev('testDetails.classList.contains("disclosure-enhanced")'),'shared disclosure enhancement '+route+selector);
  const fields=await ev('JSON.stringify([...document.querySelectorAll("input,textarea,select")].map(f=>[f.name,f.value]))');
  const closed=await ev('testDetails.getBoundingClientRect().height');await ev('testDetails.querySelector(":scope > summary").click()');
  assert.ok(await ev('testDetails.getAnimations({subtree:true}).some(a=>a.effect.getKeyframes().some(k=>"height"in k))'),'height opens');
  await pause(60);const opening=await ev('testDetails.getBoundingClientRect().height');await pause(260);const full=await ev('testDetails.getBoundingClientRect().height');
  assert.ok(opening>closed+.2&&opening<full-.2,'measured intermediate expansion '+route+JSON.stringify({closed,opening,full}));
  const arrow=await ev('getComputedStyle(testDetails.querySelector("summary"),testDetails.classList.contains("request-row")?null:"::before").transform');
  await ev('testDetails.querySelector("summary").click()');await pause(60);const closing=await ev('testDetails.getBoundingClientRect().height');assert.ok(closing>closed+.2&&closing<full-.2,'measured intermediate collapse');await pause(260);
  assert.ok(await ev('!testDetails.open&&!testDetails.getAnimations({subtree:true}).length'),'collapse cleanup');
  if(!await ev('testDetails.classList.contains("request-row")'))assert.notEqual(arrow,await ev('getComputedStyle(testDetails.querySelector("summary"),"::before").transform'),'arrow changes direction');
  await ev('testDetails.querySelector("summary").focus()');await key('Enter','Enter',13);await pause(300);assert.ok(await ev('testDetails.open'),'keyboard expansion');await key(' ','Space',32);await pause(300);assert.ok(await ev('!testDetails.open'),'keyboard collapse');
  for(let i=0;i<6;i++){await ev('testDetails.querySelector("summary").click()');await pause(20)}await pause(300);assert.ok(await ev('!testDetails.open'),'rapid reversal intent');
  assert.equal(await ev('JSON.stringify([...document.querySelectorAll("input,textarea,select")].map(f=>[f.name,f.value]))'),fields,'fields/CSRF unchanged');assert.equal(await ev('submitCount'),0,'no submission');
  measurements.push({route,selector,width,closed,opening,full,closing});disclosures++;
 }
 for(const width of (process.argv.includes('--tail-only')?[]:[375,768,1440])){
  await call('Emulation.setDeviceMetricsOverride',{width,height:1100,deviceScaleFactor:1,mobile:false});await login('librarian');
  await exercise('/catalog.php','.catalog-more-filters',width);await exercise('/manage_books.php?id=1','.editor-section',width);await exercise('/manage_books.php?id=1','.editor-section:nth-of-type(2)',width);await exercise('/manage_books.php?id=1','.editor-section:nth-of-type(3)',width);await exercise('/manage_books.php?id=1','.editor-section:nth-of-type(4)',width);
  await login('active');await exercise('/requests.php','.request-row',width);
  await login('admin');await exercise('/staff.php','.workspace-create',width);
  if(width===1440)await screenshot(out+'/staff-desktop.png');if(width===375)await screenshot(out+'/staff-phone.png');
 }

 let statusTabs=0;
 for(const width of [375,1440]){
  await call('Emulation.setDeviceMetricsOverride',{width,height:1100,deviceScaleFactor:1,mobile:false});await login('librarian');
  await exercise('/students.php?status=pending','.student-card details',width);
  await go('/manage_books.php?id=1');await ev('document.querySelectorAll(".editor-section")[1].querySelector("summary").click()');await pause(300);await screenshot(out+'/editor-expanded-'+width+'.png');
  await login('admin');await go('/students.php');
  const target=await ev('document.querySelector(".account-tabs a:not([aria-current])").href');
  const previous=await ev('performance.timeOrigin');await ev('document.querySelector(".account-tabs a:not([aria-current])").click()');
  await until('performance.timeOrigin!=='+previous+'&&location.href==='+JSON.stringify(target)+'&&motionProbe.finished');
  assert.ok(await ev('motionProbe.names.some(n=>n.includes("root"))'),'same-page status tab crossfade');statusTabs++;
 }
 await login('admin');await go('/staff.php');await ev('window.testDetails=document.querySelector(".workspace-create");testDetails.querySelector("summary").click()');await pause(40);await ev('window.mediaSettled=false;matchMedia("(prefers-reduced-motion: reduce)").addEventListener("change",()=>window.mediaSettled=true,{once:true})');await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});await ev('getComputedStyle(testDetails.querySelector("summary"),"::before").transitionDuration');await until('mediaSettled');assert.ok(await ev('testDetails.open&&!testDetails.getAnimations({subtree:true}).length'),'reduced preference settles animation '+JSON.stringify(await ev('({open:testDetails.open,animations:testDetails.getAnimations({subtree:true}).map(a=>({name:a.animationName,frames:a.effect.getKeyframes()}))})')));await ev('testDetails.querySelector("summary").click()');assert.ok(await ev('!testDetails.open'),'reduced preference toggles immediately');
 await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'no-preference'}]});await ev('testDetails.querySelector("summary").click()');await pause(30);await call('Emulation.setDeviceMetricsOverride',{width:820,height:900,deviceScaleFactor:1,mobile:false});await pause(60);assert.ok(await ev('testDetails.open&&!testDetails.getAnimations({subtree:true}).some(a=>a.effect.getKeyframes().some(k=>"height"in k))'),'resize settles height');
 await call('Emulation.setScriptExecutionDisabled',{value:true});await go('/staff.php');await ev('document.querySelector(".workspace-create summary").focus()');await key('Enter','Enter',13);assert.ok(await ev('document.querySelector(".workspace-create").open'),'no-script native disclosure');await call('Emulation.setScriptExecutionDisabled',{value:false});
 const injection=await call('Page.addScriptToEvaluateOnNewDocument',{source:'Element.prototype.animate=undefined'});await go('/staff.php');await ev('document.querySelector(".workspace-create summary").click()');assert.ok(await ev('document.querySelector(".workspace-create").open'),'missing WAAPI fallback');await call('Page.removeScriptToEvaluateOnNewDocument',{identifier:injection.identifier});
 assert.equal(errors.length,0);const report={mode:process.argv.includes('--tail-only')?'edge-checks':'full',transitions,disclosures,statusTabs,runtimeErrors:errors.length,measurements,checks:[...(transitions?['four role/status navigation sets at375/768/1440 in Light/Dark','native cross-document fade and desktop sliding active marker','mobile drawer navigation']:[]),'nested student-ID claim disclosures','same-page status tabs','measured disclosure expansion/collapse and arrow direction','keyboard Enter/Space and repeated reversal','unchanged form values and CSRF/no submission','reduced-motion and resize cleanup','no-JS and missing WAAPI fallback'],limitations:'Chromium emulation only; physical-device, Safari, screen-reader and actual400% zoom acceptance remain manual.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify({transitions,disclosures,statusTabs,runtimeErrors:errors.length}));
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{browser?.ws.close();chrome.kill();server.kill()});
