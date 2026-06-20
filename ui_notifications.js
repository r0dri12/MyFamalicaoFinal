/**
 * MyFamalicão - Modern Notification System
 * Replaces native alert() and confirm()
 */

const myFama = {
    _init() {
        // Create toast container
        if (!document.getElementById('fama-toast-container')) {
            const container = document.createElement('div');
            container.id = 'fama-toast-container';
            document.body.appendChild(container);
        }

        // Create modal container
        if (!document.getElementById('fama-modal-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'fama-modal-overlay';
            overlay.innerHTML = `
                <div class="fama-modal">
                    <div class="fama-modal-icon"></div>
                    <h2 id="fama-modal-title">Título</h2>
                    <p id="fama-modal-message">Mensagem detalhada aqui.</p>
                    <div class="fama-modal-actions">
                        <button id="fama-btn-cancel" class="fama-btn fama-btn-secondary">Cancelar</button>
                        <button id="fama-btn-confirm" class="fama-btn fama-btn-primary">OK</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
    },

    /**
     * Show a beautiful alert
     */
    alert(title, message, type = 'info') {
        this._init();
        const overlay = document.getElementById('fama-modal-overlay');
        const iconDiv = overlay.querySelector('.fama-modal-icon');
        const titleH2 = document.getElementById('fama-modal-title');
        const messageP = document.getElementById('fama-modal-message');
        const btnConfirm = document.getElementById('fama-btn-confirm');
        const btnCancel = document.getElementById('fama-btn-cancel');

        titleH2.textContent = title;
        messageP.textContent = message;
        btnCancel.style.display = 'none';
        btnConfirm.className = 'fama-btn fama-btn-primary';
        btnConfirm.textContent = 'Entendido';

        // Set Icon and Theme
        let iconHtml = '<i class="ph-fill ph-info"></i>';
        let bgClass = 'info-bg';
        if (type === 'success') {
            iconHtml = '<i class="ph-fill ph-check-circle"></i>';
            bgClass = 'success-bg';
        } else if (type === 'error') {
            iconHtml = '<i class="ph-fill ph-warning-circle"></i>';
            bgClass = 'error-bg';
            btnConfirm.className = 'fama-btn fama-btn-danger';
        } else if (type === 'warning') {
            iconHtml = '<i class="ph-fill ph-warning"></i>';
            bgClass = 'warning-bg';
        }

        iconDiv.innerHTML = iconHtml;
        iconDiv.className = `fama-modal-icon ${bgClass}`;

        overlay.style.display = 'flex';

        return new Promise((resolve) => {
            btnConfirm.onclick = () => {
                overlay.style.display = 'none';
                resolve(true);
            };
        });
    },

    /**
     * Show a confirmation dialog
     */
    confirm(title, message, options = {}) {
        this._init();
        const overlay = document.getElementById('fama-modal-overlay');
        const iconDiv = overlay.querySelector('.fama-modal-icon');
        const titleH2 = document.getElementById('fama-modal-title');
        const messageP = document.getElementById('fama-modal-message');
        const btnConfirm = document.getElementById('fama-btn-confirm');
        const btnCancel = document.getElementById('fama-btn-cancel');

        titleH2.textContent = title;
        messageP.textContent = message;
        btnCancel.style.display = 'block';
        btnCancel.textContent = options.cancelText || 'Cancelar';
        btnConfirm.textContent = options.confirmText || 'Confirmar';
        btnConfirm.className = options.isDanger ? 'fama-btn fama-btn-danger' : 'fama-btn fama-btn-primary';

        iconDiv.innerHTML = options.isDanger ? '<i class="ph-fill ph-trash"></i>' : '<i class="ph-fill ph-question"></i>';
        iconDiv.className = `fama-modal-icon ${options.isDanger ? 'error-bg' : 'info-bg'}`;

        overlay.style.display = 'flex';

        return new Promise((resolve) => {
            btnConfirm.onclick = () => {
                overlay.style.display = 'none';
                resolve(true);
            };
            btnCancel.onclick = () => {
                overlay.style.display = 'none';
                resolve(false);
            };
        });
    },

    /**
     * Show a quick toast message
     */
    toast(message, type = 'success', duration = 4000) {
        this._init();
        const container = document.getElementById('fama-toast-container');

        const toast = document.createElement('div');
        toast.className = 'fama-toast';

        let iconHtml = '<i class="ph-fill ph-check-circle"></i>';
        let bgClass = 'success-bg';
        let title = 'Sucesso';

        if (type === 'error') {
            iconHtml = '<i class="ph-fill ph-warning-circle"></i>';
            bgClass = 'error-bg';
            title = 'Erro';
        } else if (type === 'info') {
            iconHtml = '<i class="ph-fill ph-info"></i>';
            bgClass = 'info-bg';
            title = 'Informação';
        }

        toast.innerHTML = `
            <div class="fama-toast-icon ${bgClass}">${iconHtml}</div>
            <div class="fama-toast-content">
                <div class="fama-toast-title">${title}</div>
                <div class="fama-toast-message">${message}</div>
            </div>
        `;

        container.appendChild(toast);

        // Auto remove
        setTimeout(() => {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
};

// Initialize on load
document.addEventListener('DOMContentLoaded', () => myFama._init());
