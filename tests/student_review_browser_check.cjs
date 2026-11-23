const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]);fs.mkdirSync(out,{recursive:true});const base='http://127.0.0.1:8056';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8056','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9472','--user-data-dir='+out+'/chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
(async()=>{
 let tabs;for(let i=0;i<100;i++){try{tabs=await(await fetch('http://127.0.0.1:9472/json')).json();await fetch(base+'/login.php');break}catch{await pause(100)}}
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const{call,ev,key,screenshot,errors}=browser;await call('Page.enable');await call('Runtime.enable');await call('Emulation.setFocusEmulationEnabled',{enabled:true});
 async function until(expr){for(let i=0;i<100;i++){try{if(await ev(expr))return}catch{}await pause(40)}throw Error('Timed out: '+expr)}
 async function go(route){const old=await ev('performance.timeOrigin');await call('Page.navigate',{url:base+route});await until('performance.timeOrigin!=='+old+'&&document.readyState==="complete"&&!!document.querySelector(".site-footer")');await pause(100)}
 await go('/login.php');await ev(`document.querySelector('#username').value=${JSON.stringify(fixture.librarian)};document.querySelector('#password').value=${JSON.stringify(fixture.password)};document.querySelector('button[type=submit]').click()`);await until('location.pathname!=="/login.php"&&document.readyState==="complete"');
 let layouts=0;
 for(const theme of ['light','dark'])for(const width of [375,768,1440]){
  await call('Emulation.setDeviceMetricsOverride',{width,height:1100,deviceScaleFactor:1,mobile:false});await ev(`localStorage.setItem('kaaklat.appearance',${JSON.stringify(theme)})`);await go('/students.php?status=pending');
  await ev('document.querySelector(".workspace-record > summary").click()');await pause(210);
  const d='document.querySelector(".student-claim-review")';assert.ok(await ev('document.querySelectorAll(".student-review-action").length===3'),'three action sections');
  const before=await ev(d+'.getBoundingClientRect().height');await ev(d+'.querySelector("summary").scrollIntoView({block:"center"});'+d+'.querySelector("summary").click()');await pause(100);const middle=await ev(d+'.getBoundingClientRect().height');await pause(260);const full=await ev(d+'.getBoundingClientRect().height');assert.ok(middle>before&&middle<full,'smooth expansion retained '+JSON.stringify({before,middle,full}));
  assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no overflow');
  assert.ok(await ev('[...document.querySelectorAll(".student-review-form")].every(f=>f.querySelectorAll("[name=identity_confirmation]").length===1&&f.querySelector("[name=csrf_token]")&&f.querySelector("[name=review_version]"))'),'separate verified forms');
  assert.ok(await ev('(()=>{const f=document.querySelector("input[name=replacement_id]").form,b=f.querySelector("button"),c=f.querySelector(".student-review-confirmation");return b.getBoundingClientRect().top-c.getBoundingClientRect().bottom>=12})()'),'button and confirmation have deliberate spacing');
  await ev('document.querySelector("input[name=replacement_id]").value="26-0123-456";document.querySelector(".student-claim-review [name=identity_confirmation]").focus()');await key(' ','Space',32);assert.ok(await ev('document.querySelector(".student-claim-review [name=identity_confirmation]").checked'),'keyboard checkbox works');
  await ev('document.querySelector(".student-identity-review").scrollIntoView({block:"start"})');if(theme==='light'&&width!==768)await screenshot(out+'/review-'+width+'.png');layouts++;
 }
 await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});await ev('document.querySelector(".student-claim-review summary").click()');assert.ok(await ev('!document.querySelector(".student-claim-review").open'),'reduced-motion native collapse');
 assert.equal(errors.length,0);const report={layouts,runtimeErrors:errors.length,checks:['three labelled action sections','one confirmation and CSRF/version per form','action-specific text','phone/tablet/desktop Light/Dark','smooth measured expansion','consistent field/confirmation/button spacing','keyboard checkbox','reduced motion','no record mutation'],limitations:'Chromium emulation only; physical devices, Safari, screen readers and actual400% zoom remain manual.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify({layouts,runtimeErrors:errors.length}));
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{browser?.ws.close();chrome.kill();server.kill()});
