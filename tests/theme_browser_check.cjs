// Operator-run headless Chrome acceptance. Fictional scratch fixture only; no npm packages.
const {spawn, execFileSync} = require('node:child_process');
const fs = require('node:fs');
const assert = require('node:assert/strict');
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
    const ws = new WebSocket(target.webSocketDebuggerUrl);
    await new Promise((resolve,reject)=>{ws.onopen=resolve;ws.onerror=reject;});
    let id=0; const pending=new Map(), errors=[], contrast=[];
    ws.onmessage=({data})=>{const m=JSON.parse(data);if(m.method==='Runtime.exceptionThrown')errors.push(m.params);const p=pending.get(m.id);if(p){pending.delete(m.id);m.error?p.reject(m.error):p.resolve(m.result);}};
    const call=(method,params={})=>new Promise((resolve,reject)=>{const key=++id;pending.set(key,{resolve,reject});ws.send(JSON.stringify({id:key,method,params}));});
    const ev=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true});if(r.exceptionDetails)throw Error(r.result.description);return r.result.value;};
    async function until(expression){for(let i=0;i<70;i++){try{if(await ev(expression))return;}catch{}await pause(80);}throw Error('Timed out '+expression);}
    async function go(path){const r=await call('Page.navigate',{url:base+path});if(r.errorText)throw Error(r.errorText);await until('document.readyState==="complete"');await pause(70);}
    async function viewport(width){await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});}
    async function screenshot(name){const r=await call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(fixture.directory+'/'+name+'.png',Buffer.from(r.data,'base64'));}
    await call('Page.enable');await call('Runtime.enable');
    let combinations=0;
    const matrix={active:['/','/settings.php','/catalog.php','/requests.php','/requests.php?book=6','/profile.php','/about.php','/logout.php'],pending:['/settings.php','/catalog.php','/profile.php'],librarian:['/','/settings.php','/catalog.php','/review_requests.php','/pickups.php','/returns.php','/manage_books.php','/students.php','/recover_account.php','/about.php','/logout.php'],admin:['/','/settings.php','/catalog.php','/staff.php','/students.php','/audit.php','/recover_account.php','/about.php','/logout.php']};
    for(const [role,paths] of Object.entries(matrix)){
        await call('Network.clearBrowserCookies');await go('/login.php');
        await ev("document.querySelector('[name=username]').value="+JSON.stringify(fixture[role])+";document.querySelector('[name=password]').value="+JSON.stringify(fixture.password)+";document.querySelector('[name=password]').form.requestSubmit()");
        await until("location.pathname==='/' && document.querySelector('.dashboard')");
        for(const theme of ['light','dark']){
            await go('/settings.php');await ev("document.querySelector('[name=appearance][value="+theme+"]').click()");
            for(const path of paths){
                await go(path);assert.equal(await ev('document.documentElement.dataset.theme'),theme,'Theme persists across pages');
                for(const width of [1440,768,360,320]){
                    await viewport(width);
                    await until("[...document.images].every(i=>i.loading==='lazy' || i.complete)");
                    const layout=await ev("({width:document.documentElement.clientWidth,scroll:document.documentElement.scrollWidth,settings:!!document.querySelector('.header-nav [href=\"/settings.php\"]'),images:[...document.images].every(i=>(i.loading==='lazy'&&!i.complete)||i.naturalWidth>0),current:document.querySelectorAll('.header-nav [aria-current=page]').length})");
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
                if(theme==='dark'&&['/','/settings.php','/requests.php','/review_requests.php'].includes(path))await screenshot(role+'-'+path.replace(/\W/g,'')+'-dark');
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
            await until("!!document.querySelector('.error-summary, [role=alert]')");
            assert.ok(await ev("document.activeElement.matches('.error-summary, [role=alert]')"),'Invalid librarian decision focuses summary');
            await screenshot('librarian-invalid-dark');
        }
        console.log('PASS role pages/layout:',role);
    }
    await go('/settings.php');await viewport(360);
    await ev("document.querySelector('[name=appearance][value=system]').focus()");
    await call('Input.dispatchKeyEvent',{type:'keyDown',key:'ArrowRight',code:'ArrowRight',windowsVirtualKeyCode:39});
    await call('Input.dispatchKeyEvent',{type:'keyUp',key:'ArrowRight',code:'ArrowRight',windowsVirtualKeyCode:39});
    assert.equal(await ev('document.documentElement.dataset.theme'),'light','Native keyboard theme change');
    await ev("document.querySelector('.header-nav [href=\"/settings.php\"]').focus()");
    assert.ok(await ev("(()=>{const r=document.activeElement.getBoundingClientRect();return r.left>=0&&r.right<=innerWidth})()"),'Keyboard scrolls settings into view');
    await screenshot('settings-phone');
    await ev("document.querySelector('[name=appearance][value=system]').click()");
    for(const value of ['dark','light']){await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-color-scheme',value}]});await pause(80);assert.equal(await ev('document.documentElement.dataset.theme'),value,'System changes applied');}
    const injection=await call('Page.addScriptToEvaluateOnNewDocument',{source:"Object.defineProperty(window,'localStorage',{get(){throw new Error('blocked')}})"});
    await go('/settings.php');await ev("document.querySelector('[name=appearance][value=dark]').click()");
    assert.ok(await ev("document.querySelector('#appearance-status').textContent.includes('could not be saved')"));
    await call('Page.removeScriptToEvaluateOnNewDocument',{identifier:injection.identifier});
    await call('Emulation.setScriptExecutionDisabled',{value:true});
    await call('Emulation.setEmulatedMedia',{features:[{name:'prefers-color-scheme',value:'dark'}]});await go('/settings.php');
    assert.ok(await ev("document.querySelector('#appearance-options').disabled && !document.documentElement.dataset.theme && getComputedStyle(document.documentElement).colorScheme==='dark'"),'JS-free system theme and honest disabled controls');
    await call('Emulation.setScriptExecutionDisabled',{value:false});
    await call('Network.clearBrowserCookies');
    for(const path of ['/','/login.php','/register.php','/reset_password.php','/about.php']){await go(path);for(const theme of ['dark','light']){await ev("localStorage.setItem('kaaklat.appearance',"+JSON.stringify(theme)+")");await go(path);for(const width of [1440,360]){await viewport(width);assert.ok(await ev('document.documentElement.scrollWidth<=document.documentElement.clientWidth'),'Guest overflow');}if(theme==='dark'&&path==='/login.php')await screenshot('guest-login-dark');}}
    const report={database:fixture.database,directory:fixture.directory,combinations,runtimeErrors:errors.length,contrastFailures:contrast,checks:['theme persistence','keyboard radio selection','narrow navigation keyboard visibility','student and librarian invalid-input summary focus','system changes','blocked storage','JavaScript-free system theme','guest themes']};
    fs.writeFileSync(fixture.directory+'/theme-browser-results.json',JSON.stringify(report,null,2));
    console.log(JSON.stringify(report,null,2));assert.equal(errors.length,0);assert.equal(contrast.length,0,'Rendered text contrast');ws.close();
}
run().catch(error=>{console.error(error);process.exitCode=1;}).finally(()=>{chrome.kill();server.kill();});
