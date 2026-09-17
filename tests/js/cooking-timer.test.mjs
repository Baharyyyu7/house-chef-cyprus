import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const source = readFileSync(new URL('../../resources/js/app.js', import.meta.url), 'utf8')
    .replace(/^import .*;$/gm, '');

function timerHarness({ audio = true } = {}) {
    const components = new Map();
    const intervals = new Map();
    const contexts = [];
    let now = 0;
    let intervalId = 0;
    const document = {
        body: { dataset: { alpineOwner: 'app' } },
        querySelector: () => null,
        querySelectorAll: () => [],
        addEventListener() {},
    };
    const window = {
        addEventListener() {},
        setInterval(callback) {
            intervals.set(++intervalId, callback);
            return intervalId;
        },
        clearInterval(id) { intervals.delete(id); },
    };
    if (audio) {
        window.AudioContext = class {
            state = 'suspended';
            currentTime = 0;
            destination = {};
            resumed = 0;
            tones = [];
            constructor() { contexts.push(this); }
            resume() {
                this.resumed++;
                this.state = 'running';
                return Promise.resolve();
            }
            close() {
                this.state = 'closed';
                return Promise.resolve();
            }
            createGain() { return { connect() {}, gain: { value: 1 } }; }
            createOscillator() {
                return {
                    frequency: { value: 0 },
                    connect() {},
                    disconnect() {},
                    start: () => this.tones.push(this.state),
                    stop() {},
                };
            }
        };
    }
    const Alpine = { data: (name, factory) => components.set(name, factory), start() {} };
    runInNewContext(source, { document, window, Alpine, Date: { now: () => now } });
    return {
        timer: components.get('hcCookTimer')(), contexts, intervals,
        advance(milliseconds, tick = true) {
            now += milliseconds;
            if (tick) for (const callback of [...intervals.values()]) callback();
        },
    };
}

test('audio is unlocked during the start interaction and reused when the timer finishes', () => {
    const { timer, contexts, advance } = timerHarness();
    timer.baslat(1, 'Dinlendir');
    assert.equal(contexts.length, 1);
    assert.equal(contexts[0].resumed, 1);
    advance(1000);
    assert.equal(timer.bitti, true);
    assert.equal(contexts.length, 1);
    assert.deepEqual(contexts[0].tones, ['running', 'running', 'running']);
});

test('pause preserves the exact elapsed time between interval callbacks', () => {
    const { timer, advance } = timerHarness();
    timer.baslat(90, 'Karıştır');
    advance(125, false);
    timer.duraklatDevam();
    assert.equal(timer.kalan, 89.875);
    advance(5000);
    timer.duraklatDevam();
    advance(89875);
    assert.equal(timer.bitti, true);
    assert.equal(timer.gosterim, '00:00');
});

test('pausing after a throttled deadline finishes rather than extending the timer', () => {
    const { timer, advance } = timerHarness();
    timer.baslat(1, 'Çırp');
    advance(1200, false);
    timer.duraklatDevam();
    assert.equal(timer.bitti, true);
    assert.equal(timer.kalan, 0);
});

test('reset restores the initial duration without restarting and close cancels the countdown', () => {
    const { timer, advance, intervals } = timerHarness();
    timer.baslat(90, 'Çırp');
    advance(10000);
    timer.sifirla();
    assert.equal(timer.gosterim, '01:30');
    assert.equal(timer.calisiyor, false);
    assert.equal(intervals.size, 0);
    timer.duraklatDevam();
    timer.kapat();
    assert.equal(timer.acik, false);
    assert.equal(intervals.size, 0);
});

test('destroy releases the interval and audio context when Alpine removes the component', () => {
    const { timer, contexts, intervals } = timerHarness();
    timer.baslat(90, 'Çırp');
    assert.equal(typeof timer.destroy, 'function');
    timer.destroy();
    assert.equal(intervals.size, 0);
    assert.equal(contexts[0].state, 'closed');
});

test('the visible timer still finishes when browser audio is unavailable', () => {
    const { timer, advance } = timerHarness({ audio: false });
    timer.baslat(1, 'Dinlendir');
    advance(1000);
    assert.equal(timer.bitti, true);
    assert.equal(timer.calisiyor, false);
    assert.equal(timer.gosterim, '00:00');
});
