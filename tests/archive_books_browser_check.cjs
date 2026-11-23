const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]);fs.mkdirSync(out,{recursive:true});const base='http://127.0.0.1:8055';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8055','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9471','--user-data-dir='+out+'/chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
(async()=>{
 let tabs;for(let i=0;i<100;i++){try{tabs=await(await fetch('http://127.0.0.1:9471/json')).json();await fetch(base+'/login.php');break}catch{await pause(100)}}
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const{call,ev,key,screenshot,errors}=browser;
 await call('Page.enable');await call('Runtime.enable');await call('Emulation.setFocusEmulationEnabled',{enabled:true});
 async function until(expr){for(let i=0;i<150;i++){try{if(await ev(expr))return}catch{}await pause(40)}throw Error('Timed out: '+expr)}
 async function go(route){const old=await ev('performance.timeOrigin');await call('Page.navigate',{url:base+route});await until('performance.timeOrigin!=='+old+'&&document.readyState==="complete"&&!!document.querySelector(".site-footer")');await pause(100)}
 async function login(role){await call('Network.clearBrowserCookies');await go('/login.php');await ev(`document.querySelector('#username').value=${JSON.stringify(fixture[role])};document.querySelector('#password').value=${JSON.stringify(fixture.password)};document.querySelector('button[type=submit]').click()`);await until('location.pathname!=="/login.php"&&document.readyState==="complete"');}
 await login('librarian');let layouts=0;
 for(const theme of (process.argv.includes('--fallback-only')?[]:['light','dark']))for(const width of [375,768,1440]){
  await call('Emulation.setDeviceMetricsOverride',{width,height:1100,deviceScaleFactor:1,mobile:false});await ev(`localStorage.setItem('kaaklat.appearance',${JSON.stringify(theme)})`);
  for(const route of ['/manage_books.php','/manage_books.php?status=archived','/manage_books.php?status=archived&id=9000','/manage_books.php?status=archived&id=9001','/manage_books.php?status=archived&page=2','/manage_books.php?status=archived&q=missing-fictional-title']){
   await go(route);assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no overflow '+route+width);
   assert.equal(await ev('document.querySelectorAll("h1").length'),1);assert.ok(await ev('!!document.querySelector(".account-tabs a[aria-current=page]")'),'tab current state');
   await ev('[...document.querySelectorAll("img.book-cover")].forEach(i=>i.loading="eager")');await until('[...document.querySelectorAll("img.book-cover")].every(i=>i.complete&&i.naturalWidth>0)');
   if(route.includes('id=9001'))assert.ok(await ev('document.body.textContent.includes("no active copies")'),'unavailable restoration warning');
   if(route.includes('id=9000')&&theme==='light'&&width!==768)await screenshot(out+'/review-'+width+'.png');
   layouts++;
  }
 }
 await go('/manage_books.php');await ev('document.querySelector(".account-tabs a[href*=archived]").focus()');assert.ok(await ev('getComputedStyle(document.activeElement).outlineStyle!=="none"'),'visible keyboard focus');await key('Enter','Enter',13);await until('location.search.includes("archived")&&document.readyState==="complete"');
 await go('/manage_books.php?status=archived&id=9000');await ev('document.querySelector(".managed-editor button").click()');assert.ok(await ev('!!document.querySelector(".error-summary")'),'confirmation required client feedback');
 await ev('document.querySelector("[name=restore_confirmation]").checked=true;document.querySelector(".managed-editor button").click()');await until('!location.search.includes("id=")&&document.readyState==="complete"');assert.ok(await ev('document.body.textContent.includes("Book unarchived")'),'restore success feedback');
 await go('/manage_books.php?q=Archived+Fictional+00');assert.ok(await ev('document.querySelector(".managed-catalog").textContent.includes("Archived Fictional 00")'),'restored title appears active');
 await pause(350);await call('Page.navigate',{url:'about:blank'});await pause(100);await call('Emulation.setScriptExecutionDisabled',{value:true});await go('/manage_books.php?status=archived&id=9001');await ev('document.querySelector("[name=restore_confirmation]").checked=true;document.querySelector(".managed-editor button").click()');await until('!location.search.includes("id=")&&document.readyState==="complete"');assert.ok(await ev('document.body.textContent.includes("Book unarchived")'),'native no-JS restoration');await pause(350);await call('Page.navigate',{url:'about:blank'});await pause(100);await call('Emulation.setScriptExecutionDisabled',{value:false});
 for(const role of ['active','pending','admin']){await login(role);await go('/manage_books.php?status=archived');assert.ok(await ev('document.body.textContent.includes("Access denied")&&!document.querySelector("[name=restore_confirmation]")'),'unauthorized role denied '+role);}
 if(errors.length)console.error(JSON.stringify(errors));assert.equal(errors.length,0);const report={layouts,runtimeErrors:errors.length,checks:['Active/Archived tabs and current state','Light/Dark375/768/1440 responsive layouts','protected covers load','archived list/detail/pagination/empty states','explicit zero-active-copy warning','keyboard tab focus and native Enter','confirmation validation','successful restore and active-list visibility','native no-JS restoration','student/pending/admin denied'],limitations:'Chromium emulation only; physical-device, Safari, screen-reader and actual400% zoom checks remain manual.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify({layouts,runtimeErrors:errors.length}));
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{browser?.ws.close();chrome.kill();server.kill()});
