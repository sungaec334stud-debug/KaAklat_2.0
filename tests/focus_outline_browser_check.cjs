const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]);fs.mkdirSync(out,{recursive:true});const base='http://127.0.0.1:8054';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8054','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9470','--user-data-dir='+out+'/chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
(async()=>{
 let tabs;for(let i=0;i<100;i++){try{tabs=await(await fetch('http://127.0.0.1:9470/json')).json();await fetch(base+'/login.php');break}catch{await pause(100)}}
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const{call,ev,key,errors}=browser;
 await call('Page.enable');await call('Runtime.enable');await call('Emulation.setFocusEmulationEnabled',{enabled:true});let cases=0;
 async function go(route){const previous=await ev('performance.timeOrigin');await call('Page.navigate',{url:base+route});for(let i=0;i<100;i++){if(await ev('performance.timeOrigin!=='+previous+'&&document.readyState==="complete"'))return;await pause(40)}throw Error('load timeout')}
 async function pointer(selector){const p=await ev(`(()=>{const r=document.querySelector(${JSON.stringify(selector)}).getBoundingClientRect();return{x:r.x+r.width/2,y:r.y+r.height/2}})()`);await call('Input.dispatchMouseEvent',{type:'mousePressed',button:'left',clickCount:1,...p});await call('Input.dispatchMouseEvent',{type:'mouseReleased',button:'left',clickCount:1,...p});}
 for(const theme of ['light','dark'])for(const width of [375,1440])for(const route of ['/','/login.php','/register.php','/about.php','/reset_password.php']){
  await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});await go(route);await ev('document.documentElement.dataset.theme='+JSON.stringify(theme));
  await pointer('h1');await ev('document.querySelector("main").focus()');
  assert.ok(await ev('getComputedStyle(document.querySelector("main")).outlineStyle==="none"'),'pointer focus does not outline entire page '+route);
  await key('Tab','Tab',9);await ev('document.querySelector(".header-nav a").focus()');assert.ok(await ev('getComputedStyle(document.activeElement).outlineStyle!=="none"'),'keyboard links keep focus indicator');
  await ev('document.querySelector(".skip-link").focus()');await key('Enter','Enter',13);await pause(20);assert.ok(await ev('document.activeElement.id==="main-content"&&getComputedStyle(document.activeElement).outlineStyle!=="none"'),'keyboard skip target remains focused and visible');
  cases++;
 }
 assert.equal(errors.length,0);fs.writeFileSync(out+'/report.json',JSON.stringify({cases,runtimeErrors:errors.length,checks:['pointer-focused content has no page-wide outline','keyboard links retain focus rings','keyboard skip link retains visible focus on main'],limitations:'Chromium emulation; physical-device/screen-reader checks not rerun.'},null,2));console.log(JSON.stringify({cases,runtimeErrors:errors.length}));
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{browser?.ws.close();chrome.kill();server.kill()});
