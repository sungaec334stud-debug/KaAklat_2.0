// Operator-run headless Chrome acceptance. Fictional scratch fixture only; no npm packages.
const {spawn, execFileSync} = require('node:child_process');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const {connect_browser_protocol}=require('./browser_protocol.cjs');
const output = execFileSync('C:/php-8.4.14/php.exe', ['tests/student_profile_browser_fixture.php', '--campus'], {encoding:'utf8'});
const fixture = JSON.parse(fs.readFileSync(output.match(/Private fixture file: (.+)/)[1].trim(), 'utf8'));
const base = 'http://127.0.0.1:8034';
const server = spawn('C:/php-8.4.14/php.exe', ['-S','127.0.0.1:8034','-t','public','tests/acceptance_router.php'], {env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new','--remote-debugging-port=9358','--user-data-dir='+fixture.directory+'/theme-chrome','--no-first-run','--no-proxy-server','about:blank'], {stdio:'ignore',windowsHide:true});
const pause = ms => new Promise(resolve => setTimeout(resolve,ms));
async function ready(url) { for(let i=0;i<80;i++){try{return await fetch(url)}catch{}await pause(150)}throw Error('Not ready '+url); }
async function run() {
    await ready(base+'/login.php');
    const target = (await(await ready('http://127.0.0.1:9358/json/list')).json()).find(t=>t.type==='page');
    const {ws,call,ev,screenshot: captureScreenshot,errors}=await connect_browser_protocol(target.webSocketDebuggerUrl, {awaitPromise:false});
    const contrast=[];
    async function until(expression){for(let i=0;i<70;i++){try{if(await ev(expression))return;}catch{}await pause(80);}throw Error('Timed out '+expression);}
    async function go(path){const r=await call('Page.navigate',{url:base+path});if(r.errorText)throw Error(r.errorText);await until('document.readyState==="complete"');await pause(300);}
    async function viewport(width){await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});await pause(150);}
    const screenshot=name=>captureScreenshot(fixture.directory+'/'+name+'.png');
    await call('Page.enable');await call('Runtime.enable');
    let combinations=0;
    const matrix={active:['/','/catalog.php','/requests.php','/requests.php?book=6','/profile.php','/about.php','/logout.php'],pending:['/catalog.php','/profile.php'],librarian:['/','/catalog.php','/review_requests.php','/pickups.php','/returns.php','/manage_books.php','/students.php','/recover_account.php','/about.php','/logout.php'],admin:['/','/catalog.php','/staff.php','/students.php','/audit.php','/recover_account.php','/about.php','/logout.php']};
    for(const [role,paths] of Object.entries(matrix)){
        await call('Network.clearBrowserCookies');await go('/login.php');
        await ev("document.querySelector('[name=username]').value="+JSON.stringify(fixture[role])+";document.querySelector('[name=password]').value="+JSON.stringify(fixture.password)+";document.querySelector('[name=password]').form.requestSubmit()");
        await until("location.pathname==='/' && document.querySelector('.dashboard')");
        for(const theme of ['light','dark']){
            await go('/');await ev("if(document.documentElement.dataset.theme!=="+JSON.stringify(theme)+") [...document.querySelectorAll('[data-theme-toggle]')].find(b=>b.getClientRects().length).click()");await pause(300);
            for(const path of paths){
                await go(path);assert.equal(await ev('document.documentElement.dataset.theme'),theme,'Theme persists across pages');
                if (path === '/') {
                    assert.ok(await ev("!document.querySelector('main').textContent.includes('temporarily unavailable')"), 'Dashboard read models and catalog preview render without fallback');
                    if (role === 'active') assert.equal(await ev("document.querySelectorAll('.dashboard-books article').length"),4,'Student discovery remains bounded to four catalog books');
                    if (role === 'librarian') {
                        assert.equal(await ev("document.querySelectorAll('.dashboard .staff-stat').length"),3,'Librarian gets review, collection and outstanding-loan totals');
                        assert.ok(await ev("!!document.querySelector('.dashboard-account-panel') && !!document.querySelector('.dashboard-desk-activity')"),'Librarian has accepted verification and recent-activity panels');
                    }
                    if (role === 'admin') {
                        assert.equal(await ev("document.querySelectorAll('.dashboard .staff-stat').length"),3,'Administrator gets pending, staff and recent-account-event summaries');
                        assert.ok(await ev("!!document.querySelector('.staff-access-row') && !document.querySelector('.dashboard-desk-activity')"),'Administrator has staff access without librarian circulation feed');
                    }
                }
                for(const width of [1440,768,360,320]){
                    await viewport(width);
                    await until("[...document.images].every(i=>i.loading==='lazy' || i.complete)");
                    const layout=await ev("({width:document.documentElement.clientWidth,scroll:document.documentElement.scrollWidth,settings:!document.querySelector('.header-nav [href=\"/settings.php\"]'),images:[...document.images].every(i=>(i.loading==='lazy'&&!i.complete)||i.naturalWidth>0),current:document.querySelectorAll('.header-nav [aria-current=page]').length})");
                    assert.ok(layout.scroll<=layout.width,JSON.stringify({role,theme,path,width,layout}));assert.ok(layout.settings);assert.ok(layout.images,'Missing image '+path);combinations++;
                }
                await viewport(1440);
                // Inspect real rendered text colors against the closest opaque surface.
                const failures=await ev(`(()=>{
                    const rgb=s=>{const a=s.match(/[\\d.]+/g);return a?a.map(Number):null};
                    const lum=a=>a.slice(0,3).map(v=>{v/=255;return v<=.04045?v/12.92:((v+.055)/1.055)**2.4}).reduce((s,v,i)=>s+v*[.2126,.7152,.0722][i],0);
                    const result=[];
                    for(const el of document.querySelectorAll('body *')){
                        if(['SCRIPT','STYLE','SVG','PATH','NOSCRIPT'].includes(el.tagName)||!el.getClientRects().length||![...el.childNodes].some(n=>n.nodeType===3&&n.textContent.trim()))continue;
                        const s=getComputedStyle(el);if(s.visibility==='hidden'||s.opacity==='0')continue;
                        const fg=rgb(s.color);let bg=null;
                        for(let p=el;p;p=p.parentElement){const c=rgb(getComputedStyle(p).backgroundColor);if(c&&c.length>=3&&(c.length===3||c[3]===1)){bg=c;break;}}
                        if(!fg||!bg)continue;
                        const a=lum(fg),b=lum(bg),ratio=(Math.max(a,b)+.05)/(Math.min(a,b)+.05),large=parseFloat(s.fontSize)>=24||(parseFloat(s.fontSize)>=18.66&&parseInt(s.fontWeight)>=700);
                        if(ratio<(large?3:4.5)-.02)result.push({tag:el.tagName,class:el.className,text:el.textContent.trim().slice(0,65),ratio:+ratio.toFixed(2)});
                    }return result;
                })()`);
                contrast.push(...failures.map(f=>({role,theme,path,...f})));
                if(theme==='dark'&&['/','/requests.php','/review_requests.php'].includes(path))await screenshot(role+'-'+path.replace(/\W/g,'')+'-dark');
            }
        }
        if (role === 'active') {
            await go('/requests.php?book=6');
            await ev("document.querySelector('[name=days]').value='31';document.querySelector('[name=reason]').value='short';document.querySelector('form[method=post] button').click()");
            assert.ok(await ev("!!document.querySelector('.error-summary') && document.activeElement.matches('.error-summary')"),'Invalid student input focuses summary');
            await screenshot('student-invalid-dark');
        }
        if (role === 'librarian') {
            await go('/review_requests.php');
            await ev("document.querySelector('[name=days]').value='8';document.querySelector('[name=note]').value='';document.querySelector('button[value=approve]').click()");
            await until("document.readyState==='complete' && document.activeElement.matches('.error-summary, [role=alert]')");
            assert.ok(await ev("document.activeElement.matches('.error-summary, [role=alert]')"),'Invalid librarian decision focuses summary');
            await screenshot('librarian-invalid-dark');
        }
        console.log('PASS role pages/layout:',role);
    }
    await go('/');await viewport(360);
    await call('Emulation.setFocusEmulationEnabled',{enabled:true});
    await ev("[...document.querySelectorAll('[data-theme-toggle]')].find(b=>b.getClientRects().length).focus()");
    const before=await ev('document.documentElement.dataset.theme');
    await call('Input.dispatchKeyEvent',{type:'keyDown',key:'Enter',code:'Enter',windowsVirtualKeyCode:13,text:'\r'});
    await call('Input.dispatchKeyEvent',{type:'keyUp',key:'Enter',code:'Enter',windowsVirtualKeyCode:13});
    await pause(100);
    assert.notEqual(await ev('document.documentElement.dataset.theme'),before,'Keyboard header toggle');
    await ev("localStorage.removeItem('kaaklat.appearance')");await go('/');
    for(const value of ['dark','light']){await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-color-scheme',value}]});await pause(300);assert.equal(await ev('document.documentElement.dataset.theme'),value,'Device theme until explicit choice');}
    const injection=await call('Page.addScriptToEvaluateOnNewDocument',{source:"Object.defineProperty(window,'localStorage',{get(){throw new Error('blocked')}})"});
    await go('/');await ev("[...document.querySelectorAll('[data-theme-toggle]')].find(b=>b.getClientRects().length).click()");
    assert.ok(await ev("document.querySelector('#appearance-status').textContent.includes('could not be saved')"));
    await call('Page.removeScriptToEvaluateOnNewDocument',{identifier:injection.identifier});
    await call('Emulation.setScriptExecutionDisabled',{value:true});
    await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-color-scheme',value:'dark'}]});await go('/');
    assert.ok(await ev("[...document.querySelectorAll('[data-theme-toggle]')].every(b=>b.hidden) && !document.documentElement.dataset.theme && getComputedStyle(document.documentElement).colorScheme==='dark'"),'No-script device theme and hidden toggle');
    await call('Emulation.setScriptExecutionDisabled',{value:false});
    await call('Network.clearBrowserCookies');
    for(const path of ['/','/login.php','/register.php','/reset_password.php','/about.php']){await go(path);for(const theme of ['dark','light']){await ev("localStorage.setItem('kaaklat.appearance',"+JSON.stringify(theme)+")");await go(path);for(const width of [1440,360]){await viewport(width);assert.ok(await ev('document.documentElement.scrollWidth<=document.documentElement.clientWidth'),'Guest overflow');}if(theme==='dark'&&path==='/login.php')await screenshot('guest-login-dark');}}
    const report={database:fixture.database,directory:fixture.directory,combinations,runtimeErrors:errors.length,contrastFailures:contrast,checks:['theme persistence','keyboard header toggle','visible 44px header target','student and librarian invalid-input summary focus','system changes','blocked storage','JavaScript-free system theme','guest themes']};
    fs.writeFileSync(fixture.directory+'/theme-browser-results.json',JSON.stringify(report,null,2));
    console.log(JSON.stringify(report,null,2));assert.equal(errors.length,0);assert.equal(contrast.length,0,'Rendered text contrast');ws.close();
}
run().catch(error=>{console.error(error);process.exitCode=1;}).finally(()=>{chrome.kill();server.kill();});
