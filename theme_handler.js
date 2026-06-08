(function () {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

window.addEventListener('DOMContentLoaded', () => {
    updateThemeToggleUI();
});

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);

    updateThemeToggleUI();

    if (window.myFama) {
        const msg = newTheme === 'dark' ? "Modo Escuro ativado! 🌙" : "Modo Claro ativado! ☀️";
        myFama.toast(msg, "info");
    }
}

function updateThemeToggleUI() {
    const toggleBtn = document.getElementById('theme-toggle-btn');
    if (!toggleBtn) return;

    const currentTheme = document.documentElement.getAttribute('data-theme');
    if (currentTheme === 'dark') {
        toggleBtn.innerHTML = '<i class="ph-bold ph-sun"></i> Mudar para Modo Claro';
        toggleBtn.classList.add('dark-active');
    } else {
        toggleBtn.innerHTML = '<i class="ph-bold ph-moon"></i> Mudar para Modo Escuro';
        toggleBtn.classList.remove('dark-active');
    }
}

// Tornar global para acesso nos links
window.toggleTheme = toggleTheme;

// Registar o Service Worker para suporte a PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
            .then(reg => console.log('Service Worker registado com sucesso! scope:', reg.scope))
            .catch(err => console.error('Falha ao registar o Service Worker:', err));
    });
}

