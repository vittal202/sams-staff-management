/**
 * Toast Notification System for OrgChart Pro
 */

const toastStyles = `
#toast-container {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    pointer-events: none;
}

.toast-item {
    background: white;
    padding: 1rem 1.25rem;
    border-radius: 1rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    display: flex;
    items-center: center;
    gap: 0.75rem;
    min-width: 300px;
    max-width: 400px;
    pointer-events: auto;
    animation: toast-slide-in 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid #e8ebf3;
}

.dark .toast-item {
    background: #1e293b;
    border-color: #334155;
    color: white;
}

.toast-item.success { border-left: 4px solid #10b981; }
.toast-item.error { border-left: 4px solid #ef4444; }
.toast-item.info { border-left: 4px solid #16439c; }

.toast-icon { font-size: 1.25rem; }
.toast-message { font-size: 0.875rem; font-weight: 500; }

@keyframes toast-slide-in {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes toast-slide-out {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

.toast-item.fade-out {
    animation: toast-slide-out 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
`;

// Inject styles function
function injectToastStyles() {
    if (!document.getElementById('toast-styles')) {
        const styleSheet = document.createElement("style");
        styleSheet.id = 'toast-styles';
        styleSheet.innerText = toastStyles;
        document.head.appendChild(styleSheet);
    }
}

// Ensure container exists function
function ensureToastContainer() {
    if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        if (document.body) {
            document.body.appendChild(container);
        } else {
            // Fallback if body doesn't exist yet (rare for showToast call)
            document.addEventListener('DOMContentLoaded', () => {
                if (!document.getElementById('toast-container')) {
                    document.body.appendChild(container);
                }
            });
        }
    }
}

window.showToast = function (message, type = 'info') {
    injectToastStyles();
    ensureToastContainer();

    const container = document.getElementById('toast-container');
    if (!container) {
        console.warn('Toast container not ready. Retrying in 100ms...');
        setTimeout(() => window.showToast(message, type), 100);
        return;
    }

    const toast = document.createElement('div');
    toast.className = `toast-item ${type}`;

    const icons = {
        success: 'check_circle',
        error: 'cancel',
        info: 'info'
    };

    toast.innerHTML = `
        <span class="material-symbols-outlined toast-icon ${type === 'success' ? 'text-emerald-500' : type === 'error' ? 'text-rose-500' : 'text-primary'}" style="font-variation-settings: 'FILL' 1">
            ${icons[type] || 'info'}
        </span>
        <span class="toast-message">${message}</span>
    `;

    container.appendChild(toast);

    // Auto-remove after 4 seconds
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
};
