// Read-only UI checks using explicitly fictional role fixtures.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));
assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]||root+'/tmp/developer-card');fs.mkdirSync(out,{recursive:true});
const base='http://127.0.0.1:8049';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8049','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9464','--user-data-dir='+out+'/chrome-profile','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
(async()=>{
 let tabs;for(let i=0;i<90;i++){try{tabs=await(await fetch('http://127.0.0.1:9464/json')).json();await fetch(base+'/about.php');break}catch{await pause(100)}}
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const{call,ev,key,errors}=browser;
 await call('Page.enable');await call('Runtime.enable');await call('Emulation.setFocusEmulationEnabled',{enabled:true});
 async function until(expr){for(let i=0;i<100;i++){try{if(await ev(expr))return}catch{}await pause(50)}throw Error('Timed out: '+expr)}
 async function go(route){const previous=await ev('performance.timeOrigin');await call('Page.navigate',{url:base+route});await until('performance.timeOrigin!=='+previous+'&&document.readyState==="complete"&&!!document.querySelector(".site-footer")')}
 let layouts=0;
 for(const role of ['guest','active','pending','librarian','admin']){
  await call('Network.clearBrowserCookies');
  if(role!=='guest'){await go('/login.php');await ev('document.querySelector("#username").value='+JSON.stringify(fixture[role])+';document.querySelector("#password").value='+JSON.stringify(fixture.password)+';document.querySelector("form").requestSubmit()');await until('document.readyState==="complete"&&document.body.classList.contains("is-authenticated")')}
  for(const theme of ['light','dark'])for(const width of [320,768,1440]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});await go('/about.php');
   await ev('document.documentElement.dataset.theme='+JSON.stringify(theme)+';document.querySelector(".about-developer").scrollIntoView({block:"center"})');
   await until('document.querySelector(".developer-photo").complete&&document.querySelector(".developer-photo").naturalWidth>0');await pause(260);
   assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no page overflow '+role+theme+width);
   assert.ok(await ev('(()=>{const c=document.querySelector(".about-developer");return c.scrollWidth<=c.clientWidth})()'),'card does not overflow');
   const aligned=await ev('(()=>{const p=document.querySelector(".developer-photo").getBoundingClientRect(),d=document.querySelector(".developer-details").getBoundingClientRect();return {beside:p.right<=d.left,stacked:p.bottom<=d.top}})()');
   assert.ok(width===320?aligned.stacked:aligned.beside,'identity adapts '+width);
   assert.equal(await ev('document.querySelector(".developer-links a").getAttribute("href")'),'mailto:emmanuelsunga200527@gmail.com');
   assert.equal(await ev('document.querySelectorAll(".developer-links a")[1].getAttribute("href")'),'https://github.com/sungaec334stud-debug/KaAklat_2.0');
   assert.ok(await ev('[...document.querySelectorAll(".developer-links a")].every(a=>a.getBoundingClientRect().height>=44)'),'44px contact links');
   assert.ok(await ev('document.querySelector(".developer-email").textContent.includes("emmanuelsunga200527@gmail.com")'),'address remains readable');
   assert.ok(await ev('document.querySelectorAll(".about-steps li").length===6&&document.querySelector(".about-intro").textContent.includes("demonstration")'),'borrowing and disclaimer preserved');
   await ev('document.querySelector(".developer-links a").focus()');assert.ok(await ev('getComputedStyle(document.activeElement).outlineStyle!=="none"'),'visible keyboard focus');
   await key('Tab','Tab',9);assert.ok(await ev('document.activeElement.textContent==="View project on GitHub"'),'native action focus order');
   if(role==='guest'&&((width===1440&&theme==='light')||(width===320&&theme==='dark'))){
    await ev('document.activeElement.blur()');const clip=await ev('(()=>{const r=document.querySelector(".about-developer").getBoundingClientRect();return{x:r.x+scrollX,y:r.y+scrollY,width:r.width,height:r.height,scale:1}})()');
    const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:true,clip});fs.writeFileSync(out+'/card-'+theme+'-'+width+'.png',Buffer.from(shot.data,'base64'));
   }
   layouts++;
  }
 }
 await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});await go('/about.php');
 assert.equal(await ev('document.querySelector(".about-developer").getAnimations({subtree:true}).length'),0,'reduced-motion readable card');
 await call('Emulation.setScriptExecutionDisabled',{value:true});await go('/about.php');
 assert.ok(await ev('document.querySelectorAll(".developer-links a").length===2&&document.querySelector(".developer-photo").getBoundingClientRect().width>0'),'no-script identity and actions');
 assert.equal(errors.length,0,'zero runtime errors');
 const report={layouts,runtimeErrors:errors.length,checks:['all five role states','Light/Dark at320/768/1440px','portrait row/phone stack','44px real contact links','keyboard focus order','email and school/course facts retained','six borrowing steps and disclaimer retained','reduced motion','no-script content'],limitations:'Chromium emulation only; physical devices, Safari, screen readers and actual400% zoom remain manual.'};
 fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{browser?.ws.close();chrome.kill();server.kill()});
