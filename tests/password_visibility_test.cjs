// Exercise the real visibility controller without storing any credentials.
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync(__dirname+'/../public/assets/entry.js','utf8');
let checks=0;const fields={password:{type:'password',value:'Fictional phrase 123',selectionStart:4,selectionEnd:8},secret:{type:'password',value:'a'.repeat(64)}};
const buttons=['password','secret','missing'].map(id=>({dataset:{password:id},hidden:true,markup:'<svg>fictional eye structure</svg>',attrs:{'aria-label':'Show '+(id==='secret'?'recovery code':'password'),'aria-pressed':'false'},getAttribute(k){return this.attrs[k]},setAttribute(k,v){this.attrs[k]=v},addEventListener(k,v){this[k]=v},set textContent(v){throw Error('The animated eye markup must not be replaced by Show/Hide text')}}));
vm.runInNewContext(source,{document:{querySelectorAll:selector=>selector==='[data-password]'?buttons:[],getElementById:id=>fields[id]}});
function check(fn){fn();checks++}
check(()=>assert.equal(buttons[0].hidden,false));
check(()=>assert.equal(buttons[2].hidden,true));
buttons[0].click();
check(()=>assert.equal(fields.password.type,'text'));
check(()=>assert.equal(fields.secret.type,'password'));
check(()=>assert.equal(buttons[0].attrs['aria-pressed'],'true'));
check(()=>assert.equal(buttons[0].attrs['aria-label'],'Hide password'));
check(()=>assert.equal(fields.password.value,'Fictional phrase 123'));
check(()=>assert.equal(fields.password.selectionStart,4));
check(()=>assert.equal(fields.password.selectionEnd,8));
check(()=>assert.equal(buttons[0].markup,'<svg>fictional eye structure</svg>'));
buttons[0].click();check(()=>assert.equal(fields.password.type,'password'));
check(()=>assert.equal(buttons[0].attrs['aria-pressed'],'false'));
check(()=>assert.equal(buttons[0].attrs['aria-label'],'Show password'));
buttons[1].click();check(()=>assert.equal(fields.secret.type,'text'));
check(()=>assert.equal(buttons[1].attrs['aria-label'],'Hide recovery code'));
check(()=>assert.equal(fields.secret.value,'a'.repeat(64)));
for(let i=0;i<11;i++)buttons[0].click();check(()=>assert.equal(fields.password.type,'text'));
console.log(`PASS: ${checks} independent eye visibility, accessibility, immutable markup and value-preservation checks.`);
