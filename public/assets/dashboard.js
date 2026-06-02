const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
const statusBadge = document.querySelector('#statusBadge');
const qrImage = document.querySelector('#qrImage');
const qrPlaceholder = document.querySelector('#qrPlaceholder');
const phoneText = document.querySelector('#phoneText');
const nameText = document.querySelector('#nameText');
const connectBtn = document.querySelector('#connectBtn');
const disconnectBtn = document.querySelector('#disconnectBtn');
const testForm = document.querySelector('#testForm');
const testResult = document.querySelector('#testResult');

async function callSession(action, options = {}) {
    const response = await fetch(`session.php?action=${encodeURIComponent(action)}`, {
        method: options.method || 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf,
        },
        body: options.body ? JSON.stringify(options.body) : undefined,
    });
    return response.json();
}

function renderStatus(data) {
    const session = data.session || {};
    const state = session.state || 'idle';

    statusBadge.textContent = state;
    statusBadge.className = `device-state-badge ${state}`;
    phoneText.textContent = session.phone || 'Not connected';
    nameText.textContent = session.pushName || 'Unknown';

    if (session.qr) {
        qrImage.src = session.qr;
        qrImage.hidden = false;
        qrPlaceholder.hidden = true;
    } else {
        qrImage.hidden = true;
        qrPlaceholder.hidden = false;
        qrPlaceholder.querySelector('strong').textContent = state === 'connected' ? 'Device connected' : 'No QR yet';
        qrPlaceholder.querySelector('span').textContent = state === 'connected'
            ? 'Your API can send messages through this linked device.'
            : 'Click connect to create a new linked-device QR.';
    }
}

async function refreshStatus() {
    try {
        renderStatus(await callSession('status'));
    } catch (error) {
        statusBadge.textContent = 'worker off';
        statusBadge.className = 'device-state-badge error';
    }
}

connectBtn?.addEventListener('click', async () => {
    connectBtn.disabled = true;
    try {
        renderStatus(await callSession('start', { method: 'POST' }));
    } finally {
        connectBtn.disabled = false;
    }
});

disconnectBtn?.addEventListener('click', async () => {
    disconnectBtn.disabled = true;
    try {
        renderStatus(await callSession('logout', { method: 'POST' }));
    } finally {
        disconnectBtn.disabled = false;
    }
});

testForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = new FormData(testForm);
    testResult.textContent = 'Sending...';

    try {
        const result = await callSession('send-test', {
            method: 'POST',
            body: {
                to: form.get('to'),
                message: form.get('message'),
            },
        });
        testResult.textContent = result.ok
            ? `Message queued as #${result.message_id}. The worker will send it in the background.`
            : (result.error || 'Message failed.');
    } catch (error) {
        testResult.textContent = 'Could not reach the worker.';
    }
});

refreshStatus();
setInterval(refreshStatus, 3000);
