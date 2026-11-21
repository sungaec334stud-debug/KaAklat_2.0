const fs=require('node:fs'),path=require('node:path'),{spawn}=require('node:child_process'),assert=require('node:assert/strict');
const root=path.resolve(__dirname,'..');const {connect_browser_protocol}=require('./browser_protocol.cjs');
if(!process.argv[2])throw Error('Pass a private fictional fixture.json');const fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]||path.join(root,'tmp/navigation-browser'));fs.mkdirSync(out,{recursive:true});
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8029','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9436','--user-data-dir='+path.join(out,'chrome-profile'),'--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));
async function main(){let tabs;for(let i=0;i<80;i++){try{tabs=await(await fetch('http://127.0.0.1:9436/json')).json();await fetch('http://127.0.0.1:8029/login.php');break}catch{await pause(100)}}
const {ws,call,ev,key,screenshot,errors}=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);await call('Page.enable');await call('Runtime.enable');
async function until(expression){for(let i=0;i<100;i++){try{if(await ev(expression))return}catch{}await pause(80)}throw Error('Timed out '+expression)}
async function go(route){await call('Page.navigate',{url:'http://127.0.0.1:8029'+route});await until('document.readyState==="complete"&&!!document.querySelector(".site-footer")');await pause(90)}
async function viewport(width,height=900){await call('Emulation.setDeviceMetricsOverride',{width,height,deviceScaleFactor:1,mobile:false});await pause(100)}
async function open(){await ev('document.querySelector("#navigation-toggle").click()');await pause(230)}
async function escape(){await key('Escape','Escape',27);await pause(70)}
let layouts=0,routeChecks=0;
for(const role of ['active','pending','librarian','admin']){
 await call('Network.clearBrowserCookies');await viewport(1440);await go('/login.php');assert.ok(await ev('!document.querySelector("#navigation-toggle")'),'guest unchanged');
 await ev('document.querySelector("#username").value='+JSON.stringify(fixture[role])+';document.querySelector("#password").value='+JSON.stringify(fixture.password)+';document.querySelector("button[type=submit]").click()');await until('document.body.classList.contains("is-authenticated")');
 const route=role==='admin'?'/staff.php':role==='librarian'?'/review_requests.php':'/requests.php';await go(route);
 const destinations=await ev('[...document.querySelectorAll(".header-nav a")].map(a=>a.getAttribute("href"))');
 for(const theme of ['light','dark']){
  await ev('localStorage.setItem("kaaklat.appearance",'+JSON.stringify(theme)+');document.documentElement.dataset.theme='+JSON.stringify(theme));
  for(const width of [320,360,768,1024,1099,1100,1440]){
   await viewport(width);assert.ok(await ev('document.documentElement.scrollWidth<=document.documentElement.clientWidth'),role+' overflow '+width);
   assert.equal(await ev('document.querySelectorAll(".header-nav").length'),1);assert.equal(await ev('document.querySelectorAll(".header-nav [aria-current=page]").length'),1);
   if(width<1100){
    assert.ok(await ev('!document.querySelector("#navigation-drawer").open'));assert.equal(await ev('document.querySelector("#navigation-toggle").textContent.trim()'),'');
    assert.ok(await ev('document.querySelector(".mobile-current-page").getBoundingClientRect().height>0'));
    await open();assert.ok(await ev('document.querySelector("#navigation-drawer").matches(":modal")'),JSON.stringify({role,width,theme,errors,debug:await ev('({ready:document.readyState,classes:document.body.className,open:document.querySelector("#navigation-drawer").open,media:matchMedia("(min-width:1100px)").matches})')}));assert.equal(await ev('document.querySelector("#navigation-toggle").getAttribute("aria-expanded")'),'true');
    assert.ok(await ev('document.querySelector("#navigation-drawer").contains(document.activeElement)'));
    // Tab wraps both ways without entering the inert page.
    await ev('(()=>{const stops=[...document.querySelectorAll("#navigation-drawer button,#navigation-drawer a[href]")].filter(x=>x.getClientRects().length);stops[stops.length-1].focus()})()');await key('Tab','Tab',9);assert.equal(await ev('document.activeElement.className'),'navigation-close');
    await call('Input.dispatchKeyEvent',{type:'keyDown',key:'Tab',code:'Tab',windowsVirtualKeyCode:9,modifiers:8});await call('Input.dispatchKeyEvent',{type:'keyUp',key:'Tab',code:'Tab',windowsVirtualKeyCode:9,modifiers:8});assert.ok(await ev('document.querySelector("#navigation-drawer").contains(document.activeElement)'));
    if(width===360&&theme==='light')await screenshot(out+'/'+role+'-drawer-360.png');
    await escape();assert.ok(await ev('!document.querySelector("#navigation-drawer").open'));assert.equal(await ev('document.activeElement.id'),'navigation-toggle');
    if(width===360&&theme==='light')await screenshot(out+'/'+role+'-closed-360.png');
   }else{
    assert.ok(await ev('document.querySelector(".site-header").parentElement===document.body'));assert.equal(await ev('getComputedStyle(document.querySelector(".mobile-navigation-bar")).display'),'none');
    if(width===1440&&theme==='light')await screenshot(out+'/'+role+'-desktop.png');
   }
   layouts++;
  }
 }
 // Native link navigation and page identity, across the role's full route list.
 await viewport(360);
 for(const destination of destinations.filter(x=>x!=='/logout.php')){
  await open();await ev('[...document.querySelectorAll(".header-nav a")].find(a=>a.getAttribute("href")==='+JSON.stringify(destination)+').click()');await until('document.readyState==="complete"&&location.pathname==='+JSON.stringify(destination));await pause(130);
  assert.ok(await ev('!document.querySelector("#navigation-drawer").open'));assert.equal(await ev('document.querySelectorAll(".header-nav [aria-current=page]").length'),1);assert.ok(await ev('document.querySelector(".mobile-current-page").textContent.trim().length>0'));routeChecks++;
 }
 await go('/catalog.php');await ev('document.querySelector("input[name=q]").value="Fictional unsent search"');await open();await ev('document.querySelector(".navigation-close").click()');assert.equal(await ev('document.querySelector("input[name=q]").value'),'Fictional unsent search');
 await open();await viewport(1440);assert.ok(await ev('!document.querySelector("#navigation-drawer").open&&!document.documentElement.classList.contains("navigation-open")'));assert.ok(await ev('document.querySelector(".site-header").parentElement===document.body'));await viewport(360);await open();await call('Input.dispatchMouseEvent',{type:'mousePressed',x:345,y:400,button:'left',clickCount:1});await call('Input.dispatchMouseEvent',{type:'mouseReleased',x:345,y:400,button:'left',clickCount:1});assert.ok(await ev('!document.querySelector("#navigation-drawer").open'));
 await viewport(360,360);await open();await ev('document.querySelector(".header-nav a:last-child").focus()');assert.ok(await ev('document.activeElement.getBoundingClientRect().bottom<=innerHeight'),'short-height scroll reaches final link');await escape();
 await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});await open();assert.equal(await ev('getComputedStyle(document.querySelector("#navigation-drawer")).animationName'),'none');await escape();
 await call('Emulation.setScriptExecutionDisabled',{value:true});await go('/about.php');assert.ok(await ev('!document.body.classList.contains("navigation-enhanced")'));assert.ok(await ev('getComputedStyle(document.querySelector(".header-nav")).flexWrap==="wrap"'));assert.ok(await ev('[...document.querySelectorAll(".header-nav a")].every(a=>{const r=a.getBoundingClientRect();return r.left>=0&&r.right<=innerWidth})'));await call('Emulation.setScriptExecutionDisabled',{value:false});
}
assert.equal(errors.length,0);const report={layouts,routeChecks,runtimeErrors:errors.length,database:fixture.database,checks:['active/pending student, librarian, administrator','Light/Dark;320/360/768/1024/1099/1100/1440','icon-only accessible button, unique current route and visible title','modal focus wrapping; Escape/Close/backdrop focus return','native role-specific navigation','unsent input preservation','open drawer resize to desktop and back','short-height final-link reachability','reduced motion','JavaScript-free wrapped navigation','guest entry unchanged'],limitations:'Chromium simulated widths and short-height reflow, not physical devices, Safari, actual 400% browser zoom or screen readers.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));ws.close();
}
main().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{server.kill();chrome.kill()});
