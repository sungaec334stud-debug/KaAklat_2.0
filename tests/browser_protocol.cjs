// Test-only Chrome protocol plumbing. Each suite owns its fixtures/processes/waits.
const fs = require('node:fs');

async function connect_browser_protocol(url, {awaitPromise = true} = {}) {
    const ws = new WebSocket(url);
    await new Promise((resolve, reject) => { ws.onopen = resolve; ws.onerror = reject; });
    let id = 0;
    const pending = new Map(), errors = [];
    ws.onmessage = ({data}) => {
        const message = JSON.parse(data);
        if (message.method === 'Runtime.exceptionThrown') errors.push(message.params);
        const request = pending.get(message.id);
        if (!request) return;
        pending.delete(message.id);
        if (message.error) request.reject(message.error);
        else request.resolve(message.result);
    };
    const call = (method, params = {}) => new Promise((resolve, reject) => {
        const key = ++id;
        pending.set(key, {resolve, reject});
        ws.send(JSON.stringify({id: key, method, params}));
    });
    async function ev(expression) {
        const result = await call('Runtime.evaluate', {expression, returnByValue: true, awaitPromise});
        if (result.exceptionDetails) throw Error(result.result.description);
        return result.result.value;
    }
    async function key(key, code = key, n = 13) {
        await call('Input.dispatchKeyEvent', {type: 'keyDown', key, code, windowsVirtualKeyCode: n,
            text: key === 'Enter' ? '\r' : key === ' ' ? ' ' : undefined});
        await call('Input.dispatchKeyEvent', {type: 'keyUp', key, code, windowsVirtualKeyCode: n});
    }
    async function screenshot(path) {
        const result = await call('Page.captureScreenshot', {format: 'png'});
        fs.writeFileSync(path, Buffer.from(result.data, 'base64'));
    }
    return {ws, call, ev, key, screenshot, errors};
}

module.exports = {connect_browser_protocol};
