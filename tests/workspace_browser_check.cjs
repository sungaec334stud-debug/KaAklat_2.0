// New fictional scratch source only. Never use pilot identities or database records.
const {spawn, execFileSync} = require('node:child_process');
const fs = require('node:fs'), assert = require('node:assert/strict');
const {connect_browser_protocol} = require('./browser_protocol.cjs');
const output = execFileSync('C:/php-8.4.14/php.exe', ['tests/student_profile_browser_fixture.php', '--campus'], {encoding:'utf8'});
const fixtureFile = output.match(/Private fixture file: (.+)/)[1].trim();
const fixture = JSON.parse(fs.readFileSync(fixtureFile, 'utf8'));
assert.match(fixture.database, /^kaaklat_acceptance_[a-f0-9]{12}$/);
execFileSync('C:/php-8.4.14/php.exe', ['-r', `require 'app/bootstrap.php'; $f=json_decode(file_get_contents($argv[1]),true); if(!preg_match('/^kaaklat_acceptance_[a-f0-9]{12}$/D',$f['database']))exit(1); $c=application_config()['database'];$c['database']=$f['database'];$d=database_connection($c);$d->exec("INSERT INTO users(username,display_name,role,account_status) VALUES('workspace_extra_staff','Fictional Extra Staff','librarian','active')");$d->exec("INSERT INTO borrow_requests(student_id,book_id,requested_days,request_reason) VALUES(1,6,7,'Fictional second record for keyboard and form preservation checks.')");`, fixtureFile]);
const base = 'http://127.0.0.1:8040';
const server = spawn('C:/php-8.4.14/php.exe', ['-S','127.0.0.1:8040','-t','public','tests/acceptance_router.php'], {env:{...process.env,KAAKLAT_ACCEPTANCE_DB:fixture.database},stdio:'ignore',windowsHide:true});
const chrome = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new','--remote-debugging-port=9378','--user-data-dir='+fixture.directory+'/workspace-chrome','--no-first-run','--no-proxy-server','about:blank'], {stdio:'ignore',windowsHide:true});
const pause = ms => new Promise(resolve => setTimeout(resolve, ms));
async function ready(url) { for(let i=0;i<100;i++){try{return await fetch(url);}catch{}await pause(100);}throw Error('Not ready'); }
let browser;
(async () => {
    await ready(base+'/login.php');
    const tabs = await (await ready('http://127.0.0.1:9378/json/list')).json();
    browser = await connect_browser_protocol(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);
    const {call,ev,key,screenshot,errors} = browser;
    await call('Page.enable'); await call('Runtime.enable');
    async function until(expression) { for(let i=0;i<100;i++){try{if(await ev(expression))return;}catch{}await pause(50);}throw Error('Timed out: '+expression); }
    async function go(path) { await call('Page.navigate',{url:base+path}); await until('document.readyState==="complete" && location.pathname==='+JSON.stringify(new URL(base+path).pathname)); await pause(80); }
    async function login(role) {
        await call('Network.clearBrowserCookies'); await go('/login.php');
        await ev("document.querySelector('[name=username]').value="+JSON.stringify(fixture[role])+";document.querySelector('[name=password]').value="+JSON.stringify(fixture.password)+";document.querySelector('[name=password]').form.requestSubmit()");
        await until("location.pathname==='/' && !!document.querySelector('.dashboard')");
    }
    const signature = "JSON.stringify([...document.querySelectorAll('main form[method=post]')].map(f=>JSON.stringify({action:f.getAttribute('action'),fields:[...f.elements].map(e=>[e.tagName,e.name,e.id,e.value,e.checked])})).sort())";
    const pages = {admin:['/staff.php','/students.php?status=active','/audit.php','/recover_account.php','/about.php','/logout.php'],librarian:['/review_requests.php','/pickups.php','/returns.php','/manage_books.php','/students.php'],active:['/requests.php','/profile.php']};
    let layouts=0, interactions=0;
    for(const [role,paths] of Object.entries(pages)) {
        await login(role);
        for(const path of paths) {
            await go(path);
            assert.ok(await ev("document.body.classList.contains('workspace-screen')"));
            assert.equal(await ev("[...document.querySelectorAll('[id]')].map(n=>n.id).filter((id,i,a)=>a.indexOf(id)!==i).length"),0,'Unique DOM IDs');
            for(const theme of ['light','dark']) {
                await ev("document.documentElement.dataset.theme="+JSON.stringify(theme));
                await pause(180); // Let the existing bounded color transition settle for screenshots.
                for(const width of [1440,768,360]) {
                    await call('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});
                    assert.ok(await ev('document.documentElement.scrollWidth<=document.documentElement.clientWidth'),'No overflow '+path+' '+width);
                    layouts++;
                }
                await call('Emulation.setDeviceMetricsOverride',{width:1440,height:1100,deviceScaleFactor:1,mobile:false});
                await screenshot(fixture.directory+'/workspace-'+role+'-'+path.replace(/\W/g,'')+'-'+theme+'.png');
            }
            await call('Emulation.setDeviceMetricsOverride',{width:1440,height:1100,deviceScaleFactor:1,mobile:false});
            if(await ev("!!document.querySelector('.workspace-record')")) {
                const before = await ev(signature);
                const target = await ev("Math.min(1,document.querySelectorAll('.workspace-record').length-1)");
                await ev("document.querySelectorAll('.workspace-record > summary')["+target+"].focus()");
                await key('Enter');
                assert.ok(await ev("document.querySelector('.workspace-inspector').contains(document.activeElement)"),'Keyboard selection focuses visible detail heading');
                assert.equal(await ev(signature),before,'Original fields, CSRF, actions and values preserved');
                await ev("document.querySelector('.workspace-back').focus()"); await key('Enter');
                assert.ok(await ev("document.activeElement.matches('.workspace-record > summary') && document.querySelector('.workspace-inspector').hidden"),'Close returns focus to the selected row');
                await key('Enter'); interactions+=3;
            }
            if(['/staff.php','/review_requests.php','/profile.php','/about.php'].includes(path)) await screenshot(fixture.directory+'/workspace-'+role+'-'+path.replace(/\W/g,'')+'.png');
        }
    }
    await login('admin'); await go('/staff.php');
    await ev("document.querySelector('.workspace-create').open=true;document.querySelector('[name=username]').value='invalid!';document.querySelector('.workspace-create form').requestSubmit()");
    await until("document.readyState==='complete' && !!document.querySelector('.error-summary')");
    assert.ok(await ev("document.activeElement.matches('.error-summary') && document.querySelector('.workspace-create').open && document.querySelector('[name=username]').value==='invalid!'"),'Creation errors remain expanded, focusable and retained'); interactions++;
    await login('librarian'); await go('/review_requests.php');
    await ev("document.querySelectorAll('.workspace-record > summary')[1].click(); document.querySelector('.workspace-inspector [name=days]').value='8';document.querySelector('.workspace-inspector [name=note]').value='';document.querySelector('.workspace-inspector button[value=approve]').click()");
    await until("document.readyState==='complete' && !!document.querySelector('.error-summary')");
    assert.ok(await ev("document.activeElement.matches('.error-summary') && document.querySelector('.workspace-inspector [name=days]').value==='8'"),'Server error selects the affected second row and retains input');
    const errorLink = await ev("document.querySelector('.error-summary a').getAttribute('href')");
    await ev("document.querySelector('.workspace-record > summary').click();document.querySelector('.error-summary a').click()");
    await pause(80);
    assert.ok(await ev("document.querySelector('.workspace-inspector').contains(document.getElementById("+JSON.stringify(errorLink.slice(1))+")) && document.activeElement.id==="+JSON.stringify(errorLink.slice(1))), 'Error link reveals and focuses the correct field'); interactions+=2;
    await go('/returns.php?q=Fictional');
    await ev("document.querySelector('.workspace-inspector form').requestSubmit()");
    await until("document.readyState==='complete' && !!document.querySelector('.error-summary')");
    assert.ok(await ev("document.activeElement.matches('.error-summary') && document.querySelector('[name=q]').value==='Fictional'"),'Receipt error retains search and summary focus'); interactions++;
    await call('Emulation.setDeviceMetricsOverride',{width:360,height:1000,deviceScaleFactor:1,mobile:false});
    await go('/review_requests.php');
    assert.ok(await ev("document.querySelector('.workspace-inspector').hidden"),'Narrow screens initially show the compact list');
    await ev("document.querySelector('.workspace-record > summary').focus()"); await key('Enter');
    assert.ok(await ev("document.querySelector('.workspace-inspector').contains(document.activeElement)"),'Narrow keyboard selection reaches the action panel');
    await screenshot(fixture.directory+'/workspace-librarian-phone.png');
    await ev("document.querySelector('.workspace-back').focus()"); await key('Enter');
    assert.ok(await ev("document.activeElement.matches('.workspace-record > summary')")); interactions+=3;
    await call('Emulation.setScriptExecutionDisabled',{value:true});
    for(const path of ['/review_requests.php','/pickups.php','/returns.php','/students.php']) {
        await go(path);
        assert.ok(await ev("document.querySelectorAll('main form[method=post]').length>0 && !document.querySelector('.workspace-directory')"),'Original forms remain usable without JavaScript: '+path);
        interactions++;
    }
    await call('Emulation.setScriptExecutionDisabled',{value:false});
    await login('admin');
    await call('Emulation.setScriptExecutionDisabled',{value:true}); await go('/staff.php');
    await ev("document.querySelector('.workspace-create > summary').focus()"); await key('Enter');
    assert.ok(await ev("document.querySelector('.workspace-create').open && document.querySelectorAll('main form[method=post]').length>1"),'Staff creation and original account forms remain reachable without JavaScript'); interactions++;
    await call('Emulation.setScriptExecutionDisabled',{value:false});
    assert.equal(errors.length,0,'No browser runtime exceptions');
    const report={database:fixture.database,directory:fixture.directory,layouts,interactions,runtimeErrors:errors.length,checks:['all twelve pages','light/dark responsive geometry','unique IDs','keyboard record selection and return focus','original form identity and values','second-row server error association','error-summary links','receipt search retention','JavaScript-disabled original forms'],limitations:['Actual browser 400% zoom, screen reader and real phone acceptance remain manual.']};
    fs.writeFileSync(fixture.directory+'/workspace-browser-results.json',JSON.stringify(report,null,2));
    console.log(JSON.stringify(report,null,2));
})().catch(error=>{console.error(error);process.exitCode=1;}).finally(()=>{browser?.ws.close();chrome.kill();server.kill();});
