const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]||root+'/tmp/page-navigation');fs.mkdirSync(out,{recursive:true});const base='http://127.0.0.1:8051';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8051','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9467','--user-data-dir='+out+'/chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
(async()=>{
 let tabs;for(let i=0;i<100;i++){try{tabs=await(await fetch('http://127.0.0.1:9467/json')).json();await fetch(base+'/login.php');break}catch{await pause(100)}}
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const{ws,call,ev,key,screenshot,errors}=browser;await call('Page.enable');await call('Runtime.enable');await call('Emulation.setFocusEmulationEnabled',{enabled:true});
 const pendingProbes=[];ws.addEventListener('message',({data})=>{const m=JSON.parse(data);if(m.method==='Runtime.consoleAPICalled'&&m.params.args[0]?.value==='navigation-pending-probe')pendingProbes.push({pending:m.params.args[1].value,opacity:m.params.args[2].value})});
 await call('Page.addScriptToEvaluateOnNewDocument',{source:`window.navProbe={ready:false,finished:false,native:false};window.addEventListener('pageswap',()=>console.info('navigation-pending-probe',document.documentElement.classList.contains('page-navigation-pending'),getComputedStyle(document.body,'::before').opacity));window.addEventListener('pagereveal',e=>{if(!e.viewTransition)return;navProbe.native=true;e.viewTransition.ready.then(()=>{navProbe.ready=true;navProbe.names=document.getAnimations().map(a=>a.effect.pseudoElement||'');},()=>{navProbe.skipped=true});e.viewTransition.finished.then(()=>{navProbe.finished=true},()=>{navProbe.finished=true});});`});
 async function until(expr){for(let i=0;i<150;i++){try{if(await ev(expr))return}catch{}await pause(35)}throw Error('Timed out: '+expr)}
 async function go(route){const previous=await ev('performance.timeOrigin');await call('Page.navigate',{url:base+route});await until('performance.timeOrigin!=='+previous+'&&document.readyState==="complete"&&!!document.querySelector(".site-footer")');await pause(300)}
 await go('/login.php');assert.ok(await ev(`!!document.querySelector('link[href*="page-navigation.css"]')`),'native page navigation stylesheet is present');
 let transitions=0;const samples=[];
 for(const theme of ['light','dark'])for(const width of [320,768,1440])for(const[from,to]of[['/login.php','/register.php'],['/register.php','/about.php'],['/about.php','/login.php']]){
  await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});await go(from);await ev('localStorage.setItem("kaaklat.appearance",'+JSON.stringify(theme)+');document.documentElement.dataset.theme='+JSON.stringify(theme));
  await ev('document.querySelector(".header-nav a[href=\\"'+to+'\\"]").click()');
  await until('location.pathname==='+JSON.stringify(to)+'&&navProbe.ready');
  assert.ok(await ev('navProbe.names.some(n=>n.includes("guest-tab"))'),'native active-tab slide exists');
  assert.ok(await ev('navProbe.names.some(n=>n.includes("root"))'),'native document crossfade exists');
  await until('navProbe.finished');
  await pause(40);
  assert.ok(await ev('[...document.querySelectorAll(".enter,.enter-later,.reading-slip,.hero-photo img,#main-content > .about-page")].every(e=>e.getAnimations().every(a=>!["rise-in","photo-settle","interaction-arrive"].includes(a.animationName)))'),'entry entrance does not replay after page crossfade');
  assert.equal(await ev('document.querySelectorAll(".nav-current-marker").length'),1);
  assert.ok(await ev('document.querySelector(".header-nav a[aria-current=page]").getAttribute("href")==='+JSON.stringify(to)));
  assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no overflow');
  assert.ok(await ev('!document.documentElement.classList.contains("page-navigation-pending")&&!document.documentElement.classList.contains("page-transition-active")'),'no retained busy state');
  transitions++;samples.push({theme,width,from,to});
 }
 await go('/login.php');await ev('document.querySelector(".header-nav a[href=\\"/register.php\\"]").focus()');await key('Enter','Enter',13);await until('location.pathname==="/register.php"&&navProbe.finished');
 await go('/login.php');
 const paused=[];const fetchListener=({data})=>{const m=JSON.parse(data);if(m.method==='Fetch.requestPaused'){paused.push(m.params.requestId);setTimeout(()=>call('Fetch.continueRequest',{requestId:m.params.requestId}).catch(()=>{}),250)}};ws.addEventListener('message',fetchListener);
 await call('Fetch.enable',{patterns:[{urlPattern:'*/register.php',resourceType:'Document',requestStage:'Request'}]});
 await ev('document.querySelector(".header-nav a[href=\\"/register.php\\"]").click()');await until('location.pathname==="/register.php"&&navProbe.finished');
 assert.ok(paused.length,'real navigation request starts without a fake wait');
 assert.ok(pendingProbes.at(-1).pending,'pending indicator follows real delayed request');
 assert.equal(pendingProbes.at(-1).opacity,'1','indeterminate line appears after a genuinely slower response');
 await call('Fetch.disable');ws.removeEventListener('message',fetchListener);
 await screenshot(out+'/registration-final.png');
 await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});await go('/login.php');await ev('document.querySelector(".header-nav a[href=\\"/about.php\\"]").click()');await until('location.pathname==="/about.php"&&document.readyState==="complete"');await pause(80);assert.ok(await ev('!navProbe.ready'),'no native motion under reduced preference');
 await call('Emulation.setScriptExecutionDisabled',{value:true});await go('/login.php');await ev('document.querySelector(".header-nav a[href=\\"/register.php\\"]").focus()');await key('Enter','Enter',13);await until('location.pathname==="/register.php"&&document.readyState==="complete"');assert.ok(await ev('!!document.querySelector("form")&&!document.querySelector("button[type=submit]").disabled'),'no-JS links and form remain native');await call('Emulation.setScriptExecutionDisabled',{value:false});
 await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'no-preference'}]});await call('Network.setBlockedURLs',{urls:['*page-navigation.css*']});await go('/login.php');await ev('document.querySelector(".header-nav a[href=\\"/register.php\\"]").click()');await until('location.pathname==="/register.php"&&document.readyState==="complete"');assert.ok(await ev('!!document.querySelector("#school_student_id")'),'fallback without transition CSS still navigates');await call('Network.setBlockedURLs',{urls:[]});
 assert.equal(errors.length,0);const report={transitions,runtimeErrors:errors.length,samples,checks:['real cross-document transitions','sliding active marker','viewport crossfade','native keyboard navigation','pending line follows delayed real request','no artificial navigation wait','reduced motion','no-JS forms/links','transition stylesheet unavailable fallback'],limitations:'Chromium emulation only; native cross-document transitions are progressive enhancement; physical devices, Safari, screen readers and actual400% zoom remain manual.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify({transitions,runtimeErrors:errors.length},null,2));
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{browser?.ws.close();chrome.kill();server.kill()});
