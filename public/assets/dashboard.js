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
    const isConnected = state === 'connected';
    const isSleeping = Boolean(session.sleeping);

    const statusBadge = document.getElementById('statusBadge');
    const statusIndicator = document.getElementById('statusIndicator');
    const statusText = document.getElementById('statusText');
    const topPhoneText = document.getElementById('topPhoneText');
    const phoneText = document.getElementById('phoneText');
    const nameText = document.getElementById('nameText');
    const lastConnectedText = document.getElementById('lastConnectedText');
    
    const connectBtn = document.getElementById('connectBtn');
    const disconnectBtn = document.getElementById('disconnectBtn');
    const resetBtn = document.getElementById('resetBtn');
    const sleepBtn = document.getElementById('sleepBtn');
    const wakeupBtn = document.getElementById('wakeupBtn');
    
    const profileContainer = document.getElementById('profileContainer');
    const qrContainer = document.getElementById('qrContainer');
    const profileImage = document.getElementById('profileImage');
    const profileName = document.getElementById('profileName');
    
    const qrImage = document.getElementById('qrImage');
    const qrPlaceholder = document.getElementById('qrPlaceholder');

    // Overview Stats Update
    if (statusText) statusText.textContent = isConnected ? (isSleeping ? 'Sleeping' : 'Online') : 'Offline';
    if (topPhoneText) topPhoneText.textContent = session.phone || 'None';
    if (statusIndicator) {
        const pings = statusIndicator.querySelectorAll('span');
        pings.forEach(p => {
            p.className = p.className.replace(/bg-\w+-\d+/g, isConnected ? (isSleeping ? 'bg-amber-500' : 'bg-emerald-500') : 'bg-slate-500');
        });
    }

    // Detail Panel Update
    if (statusBadge) {
        statusBadge.textContent = state;
        statusBadge.className = 'px-2.5 py-1 text-[11px] font-semibold rounded-full border shadow-sm flex items-center gap-1.5 ' + (isConnected ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200');
    }
    
    if (phoneText) phoneText.textContent = session.phone || 'Not connected';
    if (nameText) nameText.textContent = session.pushName || 'Unknown';
    if (profileName) profileName.textContent = session.pushName || 'Connected';
    if (lastConnectedText && session.connected_at) lastConnectedText.textContent = session.connected_at;

    // View Toggles
    if (isConnected) {
        if (profileContainer) profileContainer.classList.remove('hidden');
        if (qrContainer) qrContainer.classList.add('hidden');
        
        // Buttons
        if (connectBtn) connectBtn.classList.add('hidden');
        if (disconnectBtn) disconnectBtn.classList.remove('hidden'); if(resetBtn) resetBtn.classList.remove('hidden');
        if (sleepBtn) isSleeping ? sleepBtn.classList.add('hidden') : sleepBtn.classList.remove('hidden');
        if (wakeupBtn) !isSleeping ? wakeupBtn.classList.add('hidden') : wakeupBtn.classList.remove('hidden');

        // Profile Image
        if (profileImage && session.profilePicture) {
            profileImage.src = session.profilePicture;
        } else if (profileImage) {
            profileImage.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(session.pushName || 'W') + '&background=0D8ABC&color=fff';
        }
    } else {
        if (profileContainer) profileContainer.classList.add('hidden');
        if (qrContainer) qrContainer.classList.remove('hidden');
        
        // Buttons
        if (connectBtn) connectBtn.classList.remove('hidden');
        if (disconnectBtn) disconnectBtn.classList.add('hidden'); if(resetBtn) resetBtn.classList.add('hidden');
        if (sleepBtn) sleepBtn.classList.add('hidden');
        if (wakeupBtn) wakeupBtn.classList.add('hidden');

        // QR Code Handling
        if (session.qr && qrImage && qrPlaceholder) {
            qrImage.src = session.qr;
            qrImage.classList.remove('hidden');
            qrPlaceholder.classList.add('hidden');
        } else if (qrImage && qrPlaceholder) {
            qrImage.classList.add('hidden');
            qrPlaceholder.classList.remove('hidden');
            const span = qrPlaceholder.querySelector('span');
            if (span) span.textContent = state === 'qr' ? 'Waiting for QR...' : 'Click connect to generate QR code';
        }
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


// Immediate execution for UI Toggles (since script is at end of body)
const initUIToggles = () => {
    const messageModeSelect = document.querySelector('[data-message-mode-select]');
    if (messageModeSelect) {
        // Trigger initially in case it's not the default
        const initialMode = messageModeSelect.value;
        document.querySelectorAll('[data-message-panel]').forEach((panel) => {
            panel.hidden = panel.getAttribute('data-message-panel') !== initialMode;
        });

        messageModeSelect.addEventListener('change', function(e) {
            const selectedMode = e.target.value;
            document.querySelectorAll('[data-message-panel]').forEach((panel) => {
                panel.hidden = panel.getAttribute('data-message-panel') !== selectedMode;
            });
        });
    }

    const birthdayModeSelect = document.querySelector('[data-birthday-recipient-select]');
    if (birthdayModeSelect) {
        birthdayModeSelect.addEventListener('change', function(e) {
            const selectedMode = e.target.value;
            document.querySelectorAll('[data-birthday-panel]').forEach((panel) => {
                panel.hidden = panel.getAttribute('data-birthday-panel') !== selectedMode;
            });
        });
    }
};


const initRichTextEditor = () => {
    const editors = document.querySelectorAll('textarea.wa-editor');
    
    editors.forEach(textarea => {
        if (textarea.dataset.initialized) return;
        textarea.dataset.initialized = 'true';
        
        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'wa-editor-wrapper bg-white border border-slate-200 rounded-lg shadow-sm focus-within:ring-2 focus-within:ring-brand-wa_light/20 focus-within:border-brand-wa_med overflow-hidden transition-all flex flex-col';
        
        // Ensure textarea has no borders and no ring so it blends with wrapper
        textarea.classList.remove('border', 'border-slate-200', 'rounded-lg', 'focus:ring-2', 'focus:ring-brand-wa_light/20', 'focus:border-brand-wa_med', 'shadow-sm');
        textarea.classList.add('border-0', 'focus:ring-0', 'resize-y', 'flex-1', 'min-h-[100px]');
        if(textarea.classList.contains('mb-6')) {
            wrapper.classList.add('mb-6');
            textarea.classList.remove('mb-6');
        }

        textarea.parentNode.insertBefore(wrapper, textarea);
        
        // Create toolbar
        const toolbar = document.createElement('div');
        toolbar.className = 'wa-toolbar bg-slate-50 border-b border-slate-100 px-2 py-1.5 flex flex-wrap items-center gap-1';
        
        const presets = textarea.dataset.presets ? JSON.parse(textarea.dataset.presets) : [];
        
        // Format buttons
        const formats = [
            { id: 'bold', icon: 'B', tag: '*', title: 'Bold' },
            { id: 'italic', icon: 'I', tag: '_', title: 'Italic' },
            { id: 'strike', icon: 'S', tag: '~', title: 'Strikethrough' },
            { id: 'code', icon: '&lt;&gt;', tag: '`', title: 'Monospace' }
        ];
        
        formats.forEach(f => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-7 h-7 rounded flex items-center justify-center text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition-colors font-bold text-[13px]';
            if (f.id === 'italic') btn.className += ' italic';
            if (f.id === 'strike') btn.className += ' line-through';
            if (f.id === 'code') btn.className += ' font-mono text-[10px]';
            
            btn.innerHTML = f.icon;
            btn.title = f.title;
            
            btn.addEventListener('click', () => {
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const selected = textarea.value.substring(start, end);
                
                const before = textarea.value.substring(0, start);
                const after = textarea.value.substring(end);
                
                textarea.value = before + f.tag + selected + f.tag + after;
                textarea.focus();
                
                // Reposition cursor inside tags
                const newPos = start + f.tag.length + selected.length;
                textarea.setSelectionRange(newPos, newPos);
            });
            
            toolbar.appendChild(btn);
        });
        
        // Add divider if we have presets
        if (presets.length > 0) {
            const div = document.createElement('div');
            div.className = 'w-px h-4 bg-slate-300 mx-1';
            toolbar.appendChild(div);
            
            presets.forEach(p => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-2 py-1 rounded text-[11px] font-semibold text-slate-600 bg-white border border-slate-200 shadow-sm hover:bg-slate-50 hover:text-brand-wa_med transition-colors';
                btn.textContent = p;
                btn.title = 'Insert ' + p;
                
                btn.addEventListener('click', () => {
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const before = textarea.value.substring(0, start);
                    const after = textarea.value.substring(end);
                    
                    textarea.value = before + p + after;
                    textarea.focus();
                    const newPos = start + p.length;
                    textarea.setSelectionRange(newPos, newPos);
                });
                
                toolbar.appendChild(btn);
            });
        }
        
        wrapper.appendChild(toolbar);
        wrapper.appendChild(textarea);
    });
};


function insertFormatting(textarea, prefix, suffix) {
    textarea.focus();
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    if (start !== end) {
        // Text is selected, wrap it
        const selected = text.substring(start, end);
        const before = text.substring(0, start);
        const after = text.substring(end);
        textarea.value = before + prefix + selected + suffix + after;
        textarea.selectionStart = start + prefix.length;
        textarea.selectionEnd = end + prefix.length;
    } else {
        // No text selected, insert prefix and suffix and place cursor between them
        const before = text.substring(0, start);
        const after = text.substring(start);
        textarea.value = before + prefix + suffix + after;
        textarea.selectionStart = textarea.selectionEnd = start + prefix.length;
    }
    
    // trigger input event so frameworks/alpine know
    textarea.dispatchEvent(new Event('input'));
}

function insertTextAtCursor(textarea, insertText) {
    textarea.focus();
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const before = text.substring(0, start);
    const after = text.substring(end);
    
    textarea.value = before + insertText + after;
    textarea.selectionStart = textarea.selectionEnd = start + insertText.length;
    textarea.dispatchEvent(new Event('input'));
}


const sleepBtn = document.getElementById('sleepBtn');
const wakeupBtn = document.getElementById('wakeupBtn');

sleepBtn?.addEventListener('click', async () => {
    sleepBtn.disabled = true;
    try {
        renderStatus(await callSession('sleep', { method: 'POST' }));
    } finally {
        sleepBtn.disabled = false;
    }
});

wakeupBtn?.addEventListener('click', async () => {
    wakeupBtn.disabled = true;
    try {
        renderStatus(await callSession('wake', { method: 'POST' }));
    } finally {
        wakeupBtn.disabled = false;
    }
});
const resetBtn = document.getElementById('resetBtn');
resetBtn?.addEventListener('click', async () => {
    if(!confirm('Are you sure you want to reset the WhatsApp session? This will force a logout.')) return;
    resetBtn.disabled = true;
    try {
        renderStatus(await callSession('logout', { method: 'POST' }));
    } finally {
        resetBtn.disabled = false;
    }
});



// Animated Toast UI
window.showToast = function(message, type = 'error') {
    const toast = document.createElement('div');
    const isError = type === 'error';
    const borderColor = isError ? 'border-rose-500' : 'border-emerald-500';
    const iconColor = isError ? 'text-rose-500 bg-rose-100' : 'text-emerald-500 bg-emerald-100';
    const svgIcon = isError 
        ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
    
    toast.className = 'fixed top-4 right-4 z-50 flex items-center w-full max-w-sm p-4 mb-4 text-slate-700 bg-white rounded-xl shadow-2xl border-l-4 transition-all duration-300 transform translate-x-full opacity-0 ' + borderColor;
    
    toast.innerHTML = '<div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg ' + iconColor + '">' + svgIcon + '</div><div class="ml-3 text-[13px] font-medium text-slate-700">' + message + '</div>';
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 10);
    
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
};

// Replace old alert with toast for phone validation
const initPhoneInputs = () => {
    const phoneInputs = document.querySelectorAll('input[name="manual_number"], input[name="phone"], input[name="recipient_phone"]');
    if (phoneInputs.length === 0 || typeof window.intlTelInput !== 'function') return;

    let defaultCountry = (window.AppConfig?.defaultCountryCode || "in").toLowerCase();
    
    // If the user provided a numeric dial code (like '91' instead of 'in')
    if (/^\d+$/.test(defaultCountry)) {
        if (typeof window.intlTelInputGlobals !== 'undefined') {
            const countries = window.intlTelInputGlobals.getCountryData();
            const match = countries.find(c => c.dialCode === defaultCountry);
            if (match) defaultCountry = match.iso2;
        } else {
            // Fallback common mappings if globals not available
            const map = { '91': 'in', '1': 'us', '44': 'gb', '61': 'au', '971': 'ae', '92': 'pk' };
            defaultCountry = map[defaultCountry] || 'in';
        }
    }

    phoneInputs.forEach(input => {
        const iti = window.intlTelInput(input, {
            initialCountry: defaultCountry, 
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js",
            separateDialCode: true,
            nationalMode: true
        });

        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (input.value.trim() !== '' && input.offsetParent !== null) {
                    if (!iti.isValidNumber()) {
                        e.preventDefault();
                        const errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];
                        const errorCode = iti.getValidationError();
                        window.showToast("Phone number error: " + (errorMap[errorCode] || "Invalid number"), "error");
                        input.focus();
                        return false;
                    }
                    input.value = iti.getNumber();
                }
            });
        }
    });
};

// Compose Message Form AJAX Logic
const composeForm = document.querySelector('form[action*="message-send"]');
if (composeForm) {
    composeForm.addEventListener('submit', async (e) => {
        if (e.defaultPrevented) return;
        
        e.preventDefault();
        
        const submitBtn = composeForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 w-4 h-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Queuing...';
        
        try {
            const formData = new FormData(composeForm);
            
            const response = await fetch(composeForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (response.ok && result.ok) {
                window.showToast(result.message || 'Message successfully queued!', 'success');
                composeForm.reset();
                const status = document.getElementById('attachmentStatus'); if (status) status.textContent = 'No file selected';
                const editor = composeForm.querySelector('textarea.wa-editor');
                if(editor) editor.value = '';
            } else {
                window.showToast(result.error || 'Failed to queue message.', 'error');
            }
        } catch (error) {
            window.showToast('Network error while queueing message.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}
// Initialize all features safely
const initAttachmentUI = () => {
    const input = document.getElementById('messageAttachment');
    const status = document.getElementById('attachmentStatus');
    if (input && status) {
        input.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                status.textContent = this.files[0].name;
            } else {
                status.textContent = 'No file selected';
            }
        });
    }
};
const runInits = () => {
    if (typeof initAttachmentUI === 'function') initAttachmentUI();
    if (typeof initUIToggles === 'function') initUIToggles();
    if (typeof initPhoneInputs === 'function') initPhoneInputs();
    if (typeof initRichTextEditor === 'function') initRichTextEditor();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runInits);
} else {
    runInits();
}