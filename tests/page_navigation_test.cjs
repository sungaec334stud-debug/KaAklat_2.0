// Navigation observers must never take ownership from native links or forms.
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const documentEvents={},windowEvents={},classes=new Set(),motion={matches:true,addEventListener(type,fn){this.change=fn}};
const root={classList:{add:n=>classes.add(n),remove:n=>classes.delete(n)}};
const source=fs.readFileSync(__dirname+'/../public/assets/page-navigation.js','utf8');
vm.runInNewContext(source,{document:{documentElement:root,addEventListener:(t,f)=>documentEvents[t]=f},window:{matchMedia:()=>motion,addEventListener:(t,f)=>windowEvents[t]=f},location:{href:'http://127.0.0.1:8000/login.php',origin:'http://127.0.0.1:8000',pathname:'/login.php',search:''},URL,setTimeout(){throw Error('No navigation delay allowed')},fetch(){throw Error('No router fetch allowed')}});
let checks=0;function check(fn){fn();checks++}
function click(options={}){const link={href:'http://127.0.0.1:8000/register.php',target:'',hasAttribute:()=>false,...options.link};const event={defaultPrevented:false,button:0,target:{closest:()=>link},preventDefault(){throw Error('Native link was intercepted')},...options.event};documentEvents.click(event);return event}
function skipped(){let count=0;windowEvents.pageswap({viewTransition:{ready:Promise.resolve(),skipTransition(){count++}}});return count}
check(()=>{click();assert.ok(classes.has('page-navigation-pending'));assert.equal(skipped(),0)});
check(()=>{documentEvents.keydown({key:'Escape'});assert.ok(!classes.has('page-navigation-pending'));assert.equal(skipped(),1)});
check(()=>{click();windowEvents.pageshow();assert.ok(!classes.has('page-navigation-pending'))});
check(()=>{click();documentEvents.submit();assert.ok(!classes.has('page-navigation-pending'));assert.equal(skipped(),1)});
for(const modifier of ['ctrlKey','metaKey','shiftKey','altKey'])check(()=>{click({event:{[modifier]:true}});assert.ok(!classes.has('page-navigation-pending'));assert.equal(skipped(),1)});
for(const options of [{event:{button:1}},{event:{defaultPrevented:true}},{link:{href:'https://example.invalid/register.php'}},{link:{hasAttribute:()=>true}},{link:{target:'_blank'}},{link:{href:'http://127.0.0.1:8000/login.php#recovery'}},{link:{href:'http://127.0.0.1:8000/catalog.php'}}])check(()=>{click(options);assert.ok(!classes.has('page-navigation-pending'));assert.equal(skipped(),1)});
check(()=>{motion.matches=false;click();assert.ok(!classes.has('page-navigation-pending'));assert.equal(skipped(),1)});
check(()=>{motion.matches=true;click();motion.change();assert.ok(!classes.has('page-navigation-pending'));assert.equal(skipped(),1)});
check(()=>{let stopped=0;windowEvents.pagereveal({viewTransition:{ready:Promise.resolve(),finished:new Promise(()=>{}),skipTransition(){stopped++}}});motion.matches=false;motion.change();assert.equal(stopped,1)});
console.log(`PASS: ${checks} native navigation, form, modified-link, motion and pending cleanup checks.`);
