// Fictional fixtures only: exercise real browser motion and native workflow boundaries.
const fs = require('node:fs'), path = require('node:path'), assert = require('node:assert/strict');
const {spawn} = require('node:child_process');
const {connect_browser_protocol} = require('./browser_protocol.cjs');
const root = path.resolve(__dirname, '..'), fixture = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
assert.match(fixture.database, /^kaaklat_acceptance_[a-f0-9]{12}$/);
const out = path.resolve(process.argv[3] || root + '/tmp/motion'); fs.mkdirSync(out, {recursive:true});
const base = 'http://127.0.0.1:8048';
const server = spawn('C:/php-8.4.14/php.exe', ['-S','127.0.0.1:8048','-t',root+'/public',root+'/tests/acceptance_router.php'], {env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new','--remote-debugging-port=9463','--user-data-dir='+out+'/chrome-profile','--no-first-run','--no-proxy-server','about:blank'], {stdio:'ignore',windowsHide:true});
const pause = ms => new Promise(resolve => setTimeout(resolve, ms));
let browser;
(async () => {
    let tabs;
    for (let i=0;i<100;i++) { try { tabs=await(await fetch('http://127.0.0.1:9463/json')).json(); await fetch(base+'/login.php'); break; } catch { await pause(100); } }
    browser = await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);
    const {call,ev,key,screenshot,errors} = browser;
    await call('Page.enable'); await call('Runtime.enable'); await call('Emulation.setFocusEmulationEnabled',{enabled:true});
    async function until(expression) { for(let i=0;i<100;i++) { try { if(await ev(expression)) return; } catch {} await pause(60); } throw Error('Timed out: '+expression); }
    async function go(route) {
        const previous = await ev('performance.timeOrigin');
        await call('Page.navigate',{url:base+route});
        await until('performance.timeOrigin!=='+previous+'&&document.readyState==="complete"&&location.pathname==='+JSON.stringify(new URL(base+route).pathname)+'&&!!document.querySelector(".site-footer")');
    }
    async function login(role) { await call('Network.clearBrowserCookies'); await go('/login.php'); await ev('document.querySelector("#username").value='+JSON.stringify(fixture[role])+';document.querySelector("#password").value='+JSON.stringify(fixture.password)+';document.querySelector("form").requestSubmit()'); await until('location.pathname==="/"&&!!document.querySelector(".dashboard")'); }
    await go('/login.php');
    assert.ok(await ev(`!!document.querySelector('link[href*="/assets/motion.css"]')`),'shared motion stylesheet loaded');
    let layouts=0, selections=0;
    const routes = {guest:['/','/login.php','/register.php','/reset_password.php','/about.php'],active:['/','/catalog.php','/requests.php','/profile.php'],librarian:['/','/review_requests.php','/pickups.php','/returns.php','/manage_books.php','/students.php'],admin:['/','/staff.php','/students.php','/audit.php','/recover_account.php']};
    for (const [role,pages] of Object.entries(routes)) {
        if(role==='guest') await call('Network.clearBrowserCookies'); else await login(role);
        for (const theme of ['light','dark']) for (const width of [320,768,1440]) {
            await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});
            for (const route of pages) {
                await go(route); await ev('document.documentElement.dataset.theme='+JSON.stringify(theme)); await pause(210);
                assert.ok(await ev('document.documentElement.scrollWidth<=innerWidth'),'no overflow '+role+route+width);
                assert.ok(await ev('[...document.querySelectorAll("main input,main button,main a")].every(n=>getComputedStyle(n).visibility!=="hidden")'),'controls visible');
                assert.ok(await ev('[...document.querySelectorAll("nav[aria-label$=pages] a")].every(n=>getComputedStyle(n).animationName==="none"&&getComputedStyle(n).transitionDuration==="0s")'),'pagination unchanged');
                assert.ok(await ev('[...document.querySelectorAll(".account-tabs a")].every(n=>getComputedStyle(n).transitionDuration.split(",").every(v=>parseFloat(v)>0))'),'status tabs use shared feedback');
                if(role==='admin'&&route==='/students.php') {
                    const available=await ev('document.querySelectorAll(".workspace-record summary").length');
                    if(available) {
                        // Desktop starts with an already-selected record; test a real changed selection.
                        await ev('if(!document.querySelector(".workspace-inspector").hidden)document.querySelector(".workspace-back").click()');
                        await ev('document.querySelector(".workspace-record summary").focus()'); await key('Enter','Enter',13);
                        assert.ok(await ev('!document.querySelector(".workspace-inspector").hidden'));
                        assert.ok(await ev('document.querySelector(".workspace-inspector").getAnimations().length===1'),'explicit selection animates once');
                        assert.ok(await ev('document.querySelector(".workspace-inspector").contains(document.activeElement)'),'selection focuses real heading immediately');
                        await ev('for(let i=0;i<8;i++)document.querySelectorAll(".workspace-record summary")[i%'+available+'].click()');
                        assert.ok(await ev('document.querySelector(".workspace-inspector").getAnimations().length<=1'),'rapid clicks cancel old motion');
                        await pause(220); assert.equal(await ev('document.querySelector(".workspace-inspector").getAnimations().length'),0,'finite selection');
                        await ev('document.querySelector(".workspace-back").click()'); assert.ok(await ev('document.querySelector(".workspace-inspector").hidden&&document.activeElement.matches("summary")'));
                        selections++;
                    }
                }
                layouts++;
            }
        }
    }
    await call('Network.clearBrowserCookies'); await go('/login.php'); await pause(220);
    const rect=await ev('(()=>{const r=document.querySelector("button[type=submit]").getBoundingClientRect();return{x:r.x+r.width/2,y:r.y+r.height/2}})()');
    await call('Input.dispatchMouseEvent',{type:'mousePressed',...rect,button:'left',clickCount:1}); await pause(70);
    assert.notEqual(await ev('getComputedStyle(document.querySelector("button[type=submit]")).transform'),'none','press feedback');
    await call('Input.dispatchMouseEvent',{type:'mouseReleased',...rect,button:'left',clickCount:1});
    await until('!!document.querySelector(".error-summary")'); assert.ok(await ev('document.activeElement.matches(".error-summary")'),'error focus is immediate');
    assert.ok(await ev('document.querySelector(".error-summary").getAnimations().length>0'),'validation feedback animates');
    await ev('document.querySelector(".help summary").focus()'); await key('Enter','Enter',13); assert.ok(await ev('document.querySelector(".help").open'),'native disclosure');
    await screenshot(out+'/login-feedback.png');
    await login('admin'); await go('/staff.php');
    await ev('document.querySelector(".workspace-back").click()');
    await ev('document.querySelector(".workspace-record summary").click()');
    assert.equal(await ev('document.querySelector(".workspace-inspector").getAnimations().length'),1,'animation active before preference change');
    await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]}); await pause(25);
    assert.equal(await ev('document.querySelector(".workspace-inspector").getAnimations().length'),0,'reduced-motion change cancels selection');
    await ev('document.querySelector(".workspace-record summary").click()'); assert.equal(await ev('document.querySelector(".workspace-inspector").getAnimations().length'),0);
    await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'no-preference'}]});
    await ev('document.querySelector(".workspace-back").click();Element.prototype.animate=undefined;document.querySelector(".workspace-record summary").click()'); assert.ok(await ev('!document.querySelector(".workspace-inspector").hidden'),'no WAAPI fallback');
    await screenshot(out+'/staff-desktop.png');
    await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]}); await go('/staff.php');
    assert.equal(await ev('document.getAnimations().length'),0,'no decorative motion under reduce');
    await call('Emulation.setScriptExecutionDisabled',{value:true}); await go('/staff.php');
    assert.ok(await ev('!document.querySelector(".workspace-directory")&&!!document.querySelector("main form")'),'native no-JS forms retained');
    await call('Emulation.setScriptExecutionDisabled',{value:false});
    assert.equal(errors.length,0,'no runtime errors');
    const report={layouts,selections,runtimeErrors:errors.length,checks:['button press','finite record handoff','rapid interruption','immediate keyboard/error focus','native disclosures','reduced motion including mid-animation change','missing WAAPI fallback','no-JS forms','pagination excluded'],limitations:'Chromium emulation; physical devices, Safari, screen readers and actual 400% zoom remain manual.'};
    fs.writeFileSync(out+'/report.json',JSON.stringify(report,null,2)); console.log(JSON.stringify(report,null,2));
})().catch(error=>{console.error(error);process.exitCode=1;}).finally(()=>{browser?.ws.close();chrome.kill();server.kill();});
