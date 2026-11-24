const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync(__dirname+'/../public/assets/entry.js','utf8');let checks=0;
for(const route of ['/login.php','/register.php','/','/reset_password.php']){
 const handlers={},replacements=[];const href='http://127.0.0.1:8000'+route;
 vm.runInNewContext(source,{document:{querySelectorAll:()=>[]},window:{addEventListener:(type,handler)=>handlers[type]=handler},location:{pathname:route,href,replace:url=>replacements.push(url)}});
 if(['/login.php','/register.php'].includes(route)){
  handlers.pageshow({persisted:false});assert.equal(replacements.length,0);checks++;
  handlers.pageshow({persisted:true});assert.deepEqual(replacements,[href]);checks++;
 }else{assert.equal(handlers.pageshow,undefined);checks++;}
}
console.log(`PASS: ${checks} cached-entry-only revalidation checks; normal history is untouched.`);
