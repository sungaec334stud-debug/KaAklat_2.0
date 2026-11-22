// Fictional fixture only. Measure actual expanding/collapsing layout, not just a fade.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]||root+'/tmp/help-disclosure');fs.mkdirSync(out,{recursive:true});const base='http://127.0.0.1:8050';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8050','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9466','--user-data-dir='+out+'/chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
(async()=>{
 let tabs;for(let i=0;i<100;i++){try{tabs=await(await fetch('http://127.0.0.1:9466/json')).json();await fetch(base+'/login.php');break}catch{await pause(100)}}
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const{call,ev,key,screenshot,errors}=browser;
 await call('Page.enable');await call('Runtime.enable');await call('Emulation.setFocusEmulationEnabled',{enabled:true});
 async function until(expr){for(let i=0;i<100;i++){try{if(await ev(expr))return}catch{}await pause(50)}throw Error('Timed out: '+expr)}
 async function go(route){const previous=await ev('performance.timeOrigin');await call('Page.navigate',{url:base+route});await until('performance.timeOrigin!=='+previous+'&&document.readyState==="complete"&&!!document.querySelector(".site-footer")');await pause(300)}
 const help='document.querySelector(".auth-main details.help")';let cases=0;const measurements=[];
 for(const route of ['/login.php','/reset_password.php'])for(const theme of ['light','dark'])for(const width of [320,768,1440]){
  await call('Emulation.setDeviceMetricsOverride',{width,height:1100,deviceScaleFactor:1,mobile:false});await go(route);await ev('document.documentElement.dataset.theme='+JSON.stringify(theme));
  await ev('window.formSubmits=0;document.addEventListener("submit",()=>window.formSubmits++);[...document.querySelectorAll(".auth-main input:not([type=hidden])")].forEach(f=>f.value="Fictional phrase 123")');
  const original=await ev('JSON.stringify([...document.querySelectorAll(".auth-main input")].map(f=>[f.name,f.value]))');
  const closed=await ev(help+'.getBoundingClientRect().height');
  await ev(help+'.querySelector("summary").click()');
  assert.ok(await ev(help+'.open&&'+help+'.getAnimations({subtree:true}).some(a=>a.effect.getKeyframes().some(f=>"height" in f))'),'opening animates actual height');
  await pause(65);const opening=await ev(help+'.getBoundingClientRect().height');await pause(250);const expanded=await ev(help+'.getBoundingClientRect().height');
  assert.ok(opening>closed+.5&&opening<expanded-.5,'intermediate expansion '+route+theme+width+' '+JSON.stringify({closed,opening,expanded}));
  await ev(help+'.querySelector("summary").click()');assert.ok(await ev(help+'.open'),'content stays open until collapse completes');
  await pause(65);const closing=await ev(help+'.getBoundingClientRect().height');assert.ok(closing>closed+.5&&closing<expanded-.5,'intermediate collapse');
  await pause(250);assert.ok(await ev('!'+help+'.open&&'+help+'.getAnimations({subtree:true}).length===0'),'collapse cleans effects');
  assert.ok(Math.abs(await ev(help+'.getBoundingClientRect().height')-closed)<1,'exact original collapsed height');
  await ev(help+'.querySelector("summary").focus()');await key('Enter','Enter',13);await pause(310);assert.ok(await ev(help+'.open'));await key(' ','Space',32);await pause(310);assert.ok(await ev('!'+help+'.open'));
  for(let i=0;i<8;i++){await ev(help+'.querySelector("summary").click()');await pause(20)}await pause(320);
  assert.ok(await ev('!'+help+'.open&&'+help+'.getAnimations({subtree:true}).length===0'),'rapid reversal settles to final intent');
  assert.equal(await ev('JSON.stringify([...document.querySelectorAll(".auth-main input")].map(f=>[f.name,f.value]))'),original,'fields and CSRF preserved');assert.equal(await ev('window.formSubmits'),0);
  assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no overflow');
  measurements.push({route,theme,width,closed,opening,expanded,closing});cases++;
  if(route==='/login.php'&&theme==='light'&&width===1440){await ev(help+'.querySelector("summary").click()');await pause(310);await screenshot(out+'/login-expanded.png')}
 }
 await go('/login.php');await ev(help+'.querySelector("summary").click()');await pause(30);
 await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});await pause(30);
 assert.ok(await ev(help+'.open&&'+help+'.getAnimations({subtree:true}).length===0'),'preference change cancels active motion');
 await ev(help+'.querySelector("summary").click()');assert.ok(await ev('!'+help+'.open'),'reduced-motion native immediate close');
 await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'no-preference'}]});
 await ev(help+'.querySelector("summary").click()');await pause(25);await call('Emulation.setDeviceMetricsOverride',{width:320,height:900,deviceScaleFactor:1,mobile:false});await pause(80);
 assert.ok(await ev(help+'.open&&!'+help+'.getAnimations({subtree:true}).some(a=>a.effect.getKeyframes().some(f=>"height" in f))&&'+help+'.querySelector(".help-content").style.overflow===""'),'resize settles height to natural expanded layout');
 await ev(help+'.querySelector("p").textContent="Long recovery guidance ".repeat(150);'+help+'.querySelector("summary").click()');await pause(310);await ev(help+'.querySelector("summary").click()');await pause(310);
 assert.ok(await ev('(()=>{const d='+help+';return d.scrollHeight<=d.clientHeight+1})()'),'long content is not capped');
 const injected=await call('Page.addScriptToEvaluateOnNewDocument',{source:'Element.prototype.animate=undefined'});await go('/reset_password.php');await ev(help+'.querySelector("summary").click()');assert.ok(await ev(help+'.open'),'missing WAAPI uses native disclosure');await call('Page.removeScriptToEvaluateOnNewDocument',{identifier:injected.identifier});
 await call('Emulation.setScriptExecutionDisabled',{value:true});await go('/login.php');await ev(help+'.querySelector("summary").focus()');await key('Enter','Enter',13);assert.ok(await ev(`${help}.open&&!!${help}.querySelector('a[href="/reset_password.php"]')`),'no-script keyboard recovery link remains');
 assert.equal(errors.length,0);
 const report={cases,runtimeErrors:errors.length,measurements,checks:['measured intermediate opening and closing heights','pointer and Enter/Space','rapid reversals','unchanged fields/CSRF and no submission','mid-animation reduced-motion change','resize cleanup','uncapped long content','native fallback without WAAPI or JS'],limitations:'Chromium emulation only; physical devices, Safari, screen readers and actual400% zoom remain manual.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify({cases,runtimeErrors:errors.length},null,2));
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{browser?.ws.close();chrome.kill();server.kill()});
