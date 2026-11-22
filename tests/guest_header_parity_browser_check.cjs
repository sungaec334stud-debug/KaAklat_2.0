// Compare the same shared header across public routes, not just overflow on each page.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{spawn}=require('node:child_process');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const root=path.resolve(__dirname,'..'),fixture=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));assert.match(fixture.database,/^kaaklat_acceptance_[a-f0-9]{12}$/);
const out=path.resolve(process.argv[3]||root+'/tmp/header-parity');fs.mkdirSync(out,{recursive:true});const base='http://127.0.0.1:8052';
const server=spawn('C:/php-8.4.14/php.exe',['-S','127.0.0.1:8052','-t',root+'/public',root+'/tests/acceptance_router.php'],{env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome=spawn('C:/Program Files/Google/Chrome/Application/chrome.exe',['--headless=new','--remote-debugging-port=9468','--user-data-dir='+out+'/chrome','--no-first-run','--no-proxy-server','about:blank'],{stdio:'ignore',windowsHide:true});
const pause=ms=>new Promise(r=>setTimeout(r,ms));let browser;
(async()=>{
 let tabs;for(let i=0;i<100;i++){try{tabs=await(await fetch('http://127.0.0.1:9468/json')).json();await fetch(base+'/login.php');break}catch{await pause(100)}}
 browser=await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);const{call,ev,key,errors}=browser;await call('Page.enable');await call('Runtime.enable');await call('Emulation.setFocusEmulationEnabled',{enabled:true});
 async function until(expr){for(let i=0;i<100;i++){try{if(await ev(expr))return}catch{}await pause(40)}throw Error('Timed out: '+expr)}
 async function go(route){const previous=await ev('performance.timeOrigin');await call('Page.navigate',{url:base+route});await until('performance.timeOrigin!=='+previous+'&&document.readyState==="complete"&&!!document.querySelector(".site-footer")');await ev('scrollTo(0,0)');await pause(40)}
 const snapshot=`(()=>{const result={};for(const [name,selector]of Object.entries({header:'.site-header',inner:'.header-inner',branding:'.site-branding',brand:'.brand',logo:'.brand-mark',name:'.brand-wordmark',university:'.brand-university',nav:'.header-nav',theme:'.theme-toggle'})){const e=document.querySelector(selector),r=e.getBoundingClientRect(),s=getComputedStyle(e);result[name]={x:r.x,y:r.y,width:r.width,height:r.height,fontSize:s.fontSize,fontFamily:s.fontFamily,display:s.display,color:s.color};}result.links=[...document.querySelectorAll('.header-nav a')].map(e=>{const r=e.getBoundingClientRect(),s=getComputedStyle(e);return{x:r.x,y:r.y,width:r.width,height:r.height,fontSize:s.fontSize}});return result})()`;
 function compare(actual,expected,label){
  for(const name of Object.keys(expected)){
   if(name==='links'){actual[name].forEach((link,i)=>{for(const key of Object.keys(link))if(typeof link[key]==='number')assert.ok(Math.abs(link[key]-expected[name][i][key])<=.5,label+' link '+i+' '+key);else assert.equal(link[key],expected[name][i][key],label+' link style')});continue}
   for(const key of Object.keys(expected[name]))if(typeof expected[name][key]==='number')assert.ok(Math.abs(actual[name][key]-expected[name][key])<=.5,label+' '+name+'.'+key+' '+JSON.stringify({actual:actual[name][key],expected:expected[name][key]}));else assert.equal(actual[name][key],expected[name][key],label+' '+name+'.'+key);
  }
 }
 await go('/login.php');let layouts=0;const measurements=[];
 for(const theme of ['light','dark']){
  await ev('localStorage.setItem("kaaklat.appearance",'+JSON.stringify(theme)+')');
  for(const width of [320,375,390,450,540,760,761,768,820,1024,1050,1051,1100,1280,1440,1920]){
   await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});let expected;
   for(const route of ['/login.php','/register.php','/reset_password.php','/about.php','/']){
    await go(route);const current=await ev(snapshot);if(!expected)expected=current;else compare(current,expected,theme+' '+width+' '+route);
    assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no overflow');
    assert.ok(await ev('[...document.querySelectorAll(".header-nav a,.theme-toggle")].every(e=>e.getBoundingClientRect().height>=44)'),'44px controls');
    assert.equal(current.university.display==='none',width<=1050,'consistent university breakpoint');
    if(width<=760)assert.ok(current.brand.x<current.theme.x&&current.nav.y>current.brand.y,'brand left/theme right above links');
    measurements.push({theme,width,route,headerHeight:current.header.height,brandX:current.brand.x,themeX:current.theme.x,navY:current.nav.y});layouts++;
    if(theme==='light'&&[375,1440].includes(width)&&['/login.php','/about.php'].includes(route)){
     const shot=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:true,clip:{x:0,y:0,width,height:current.header.height+1,scale:1}});fs.writeFileSync(out+'/'+route.slice(1,-4)+'-'+width+'.png',Buffer.from(shot.data,'base64'));
    }
   }
  }
 }
 await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});await go('/login.php');await ev('document.querySelector(".header-nav a").focus()');assert.ok(await ev('getComputedStyle(document.activeElement).outlineStyle!=="none"'),'keyboard focus');await key('Tab','Tab',9);assert.ok(await ev('document.activeElement.getAttribute("href")==="/register.php"'),'native link order');
 await call('Emulation.setScriptExecutionDisabled',{value:true});await call('Emulation.setDeviceMetricsOverride',{width:375,height:900,deviceScaleFactor:1,mobile:false});await go('/login.php');const noScript=await ev(snapshot);await go('/about.php');compare(await ev(snapshot),noScript,'no-script header');
 assert.equal(errors.length,0);const report={layouts,runtimeErrors:errors.length,measurements,checks:['five public routes have matching header geometry and typography','sixteen widths including375 and breakpoint boundaries','Light/Dark','consistent university visibility','44px controls','left brand/right theme on phones','native keyboard order','reduced motion','no-script parity'],limitations:'Chromium emulation only; physical devices, Safari, screen readers and actual400% zoom remain manual.'};fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2));console.log(JSON.stringify({layouts,runtimeErrors:errors.length},null,2));
})().catch(e=>{console.error(e);process.exitCode=1}).finally(()=>{browser?.ws.close();chrome.kill();server.kill()});
