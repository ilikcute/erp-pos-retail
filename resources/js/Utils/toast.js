// Lightweight toast notifications (no external deps)
const COLORS = {
    success: { bg: '#10b981', icon: '✓' },
    error:   { bg: '#ef4444', icon: '✕' },
    warning: { bg: '#f59e0b', icon: '!' },
    info:    { bg: '#6366f1', icon: 'i' },
};

function ensureContainer() {
    let el = document.getElementById('toast-container');
    if (!el) {
        el = document.createElement('div');
        el.id = 'toast-container';
        el.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;pointer-events:none;';
        document.body.appendChild(el);
    }
    return el;
}

function show(message, type = 'info', duration = 3000) {
    if (typeof document === 'undefined') return;
    const { bg, icon } = COLORS[type] || COLORS.info;
    const c = ensureContainer();
    const t = document.createElement('div');
    t.style.cssText = 'display:flex;align-items:center;gap:.5rem;background:' + bg + ';color:#fff;padding:.65rem 1rem;border-radius:.75rem;box-shadow:0 8px 24px rgba(0,0,0,.18);font-size:.875rem;font-weight:600;max-width:340px;opacity:0;transform:translateX(20px);transition:all .2s ease;pointer-events:auto;';
    t.innerHTML = '<span style="font-weight:800">' + icon + '</span><span>' + String(message) + '</span>';
    c.appendChild(t);
    requestAnimationFrame(() => { t.style.opacity = '1'; t.style.transform = 'translateX(0)'; });
    setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translateX(20px)';
        setTimeout(() => t.remove(), 200);
    }, duration);
}

const toast = {
    success: (m, d) => show(m, 'success', d),
    error:   (m, d) => show(m, 'error', d),
    warning: (m, d) => show(m, 'warning', d),
    info:    (m, d) => show(m, 'info', d),
};

export default toast;
