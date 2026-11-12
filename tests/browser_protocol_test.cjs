const assert = require('node:assert/strict');
const {connect_browser_protocol} = require('./browser_protocol.cjs');

class FakeSocket {
    static instance;
    sent = [];
    constructor() { FakeSocket.instance = this; queueMicrotask(() => this.onopen()); }
    send(value) { this.sent.push(JSON.parse(value)); }
    receive(value) { this.onmessage({data: JSON.stringify(value)}); }
}
global.WebSocket = FakeSocket;
(async () => {
    const client = await connect_browser_protocol('ws://fictional');
    const socket = FakeSocket.instance;
    const first = client.call('First'), second = client.call('Second');
    socket.receive({id: 2, result: {value: 'second'}});
    socket.receive({id: 1, result: {value: 'first'}});
    assert.deepEqual(await first, {value: 'first'});
    assert.deepEqual(await second, {value: 'second'});
    socket.receive({method: 'Runtime.exceptionThrown', params: {fictional: true}});
    assert.equal(client.errors.length, 1);
    const failure = client.call('Fail');
    socket.receive({id: 3, error: {message: 'Fictional protocol failure'}});
    await assert.rejects(failure);
    const evaluation = client.ev('1+1');
    assert.equal(socket.sent.at(-1).params.awaitPromise, true);
    socket.receive({id: 4, result: {result: {value: 2}}});
    assert.equal(await evaluation, 2);
    const invalid = client.ev('throw Error()');
    socket.receive({id: 5, result: {exceptionDetails: {}, result: {description: 'Fictional script failure'}}});
    await assert.rejects(invalid, /Fictional script failure/);
    const press = client.key('Enter');
    assert.equal(socket.sent.at(-1).params.text, '\r');
    socket.receive({id: 6, result: {}});
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(socket.sent.at(-1).params.type, 'keyUp');
    socket.receive({id: 7, result: {}});
    await press;
    const legacy = await connect_browser_protocol('ws://fictional', {awaitPromise: false});
    const plain = legacy.ev('true');
    assert.equal(FakeSocket.instance.sent.at(-1).params.awaitPromise, false);
    FakeSocket.instance.receive({id: 1, result: {result: {value: true}}});
    assert.equal(await plain, true);
    console.log('PASS: Browser protocol response matching, errors, evaluation modes and native Enter dispatch.');
})().catch(error => { console.error(error); process.exitCode = 1; });
