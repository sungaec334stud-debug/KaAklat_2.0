// Browser-independent behavior tests for the shared header theme owner.
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync(__dirname+'/../public/assets/theme.js','utf8');
let checks=0;
function boot(saved,dark=false,blocked=false){
 const listeners={},events={},media={matches:dark,addEventListener:(_,fn)=>media.change=fn};
 const storage={value:saved,getItem(){if(blocked)throw Error('blocked');return this.value},setItem(key,value){if(blocked)throw Error('blocked');assert.equal(key,'kaaklat.appearance');this.value=value}};
 const buttons=[0,1].map(()=>({hidden:true,disabled:true,attrs:{},setAttribute(k,v){this.attrs[k]=v},addEventListener(k,v){this[k]=v}}));
 const status={textContent:''},root={dataset:{},classList:{add(){}}};
 const document={documentElement:root,addEventListener:(k,fn)=>listeners[k]=fn,querySelector:()=>status,querySelectorAll:()=>buttons};
 vm.runInNewContext(source,{document,window:{matchMedia:()=>media,localStorage:storage,addEventListener:(k,v)=>events[k]=v}});
 listeners.DOMContentLoaded();return {root,buttons,status,storage,media,events};
}
function check(fn){fn();checks++}
const light=boot(null);
check(()=>assert.equal(light.root.dataset.theme,'light'));
check(()=>assert.equal(light.buttons[0].hidden,false));
check(()=>assert.equal(light.buttons[0].disabled,false));
check(()=>assert.equal(light.buttons[0].attrs['aria-label'],'Switch to dark mode'));
light.buttons[0].click();
check(()=>assert.equal(light.root.dataset.theme,'dark'));
check(()=>assert.equal(light.storage.value,'dark'));
check(()=>assert.equal(light.buttons[1].attrs['aria-label'],'Switch to light mode'));
light.buttons[1].click();check(()=>assert.equal(light.root.dataset.theme,'light'));
check(()=>assert.equal(boot('dark').root.dataset.theme,'dark'));
const legacy=boot('system',true);check(()=>assert.equal(legacy.root.dataset.theme,'dark'));
legacy.buttons[0].click();legacy.media.matches=true;legacy.media.change();
check(()=>assert.equal(legacy.root.dataset.theme,'light'));
const follow=boot(null,true);follow.media.matches=false;follow.media.change();
check(()=>assert.equal(follow.root.dataset.theme,'light'));
check(()=>assert.equal(boot('forged',true).root.dataset.theme,'dark'));
const denied=boot(null,true,true);denied.buttons[0].click();
check(()=>assert.equal(denied.root.dataset.theme,'light'));
check(()=>assert.match(denied.status.textContent,/could not be saved/i));
light.events.storage({key:'kaaklat.appearance',newValue:'dark'});
check(()=>assert.equal(light.root.dataset.theme,'dark'));
light.events.storage({key:'kaaklat.appearance',newValue:null});
check(()=>assert.equal(light.root.dataset.theme,'light'));
light.events.storage({key:'other',newValue:'dark'});check(()=>assert.equal(light.root.dataset.theme,'light'));
for(let i=0;i<9;i++)light.buttons[0].click();check(()=>assert.equal(light.root.dataset.theme,'dark'));
console.log(`PASS: ${checks} header appearance, persistence, legacy, cross-tab, rapid and blocked-storage checks.`);
