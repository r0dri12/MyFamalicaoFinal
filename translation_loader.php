<?php
// translation_loader.php
// Este componente deve ser incluído no início do <body> de todas as páginas.

// Sync admin status
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    require_once "db_connect.php";
    try {
        $stmtSync = $conn->prepare("SELECT is_admin FROM users WHERE id = :id");
        $stmtSync->execute(['id' => $_SESSION["id"]]);
        $userSync = $stmtSync->fetch(PDO::FETCH_ASSOC);
        if ($userSync) {
            $_SESSION["is_admin"] = $userSync['is_admin'];
        }
    } catch (Exception $e) {
    }
}
?>
<div id="global-loader" class="global-loader">
    <div class="loader-content">
        <div class="loader-logo">
            <i class="ph-fill ph-map-pin-line"></i>
        </div>
        <div class="loader-spinner"></div>
        <p class="loader-text">A preparar a tua experiência...</p>
    </div>
</div>

<style>
.global-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background: rgba(248, 250, 252, 0.98);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    transform: none !important;
    transition: opacity 0.5s ease, visibility 0.5s;
}

.global-loader.fade-out {
    opacity: 0;
    visibility: hidden;
}

.loader-content {
    text-align: center;
    animation: loaderBounce 2s infinite ease-in-out;
}

.loader-logo {
    font-size: 48px;
    color: var(--primary);
    margin-bottom: 20px;
}

.loader-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(59, 130, 246, 0.1);
    border-top: 3px solid var(--primary);
    border-radius: 50%;
    margin: 0 auto 16px;
    animation: spin 1s linear infinite;
}

.loader-text {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-main);
    letter-spacing: 0.5px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes loaderBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
</style>

<script>
// Esconder o loader quando a página estiver totalmente carregada
window.addEventListener('load', function() {
    const loader = document.getElementById('global-loader');
    if (loader) {
        setTimeout(() => {
            loader.classList.add('fade-out');
        }, 300); // Pequeno delay para garantir fluidez
    }
});

// Mostrar loader ao sair da página para transição suave
window.addEventListener('beforeunload', function() {
    const loader = document.getElementById('global-loader');
    if (loader) {
        loader.classList.remove('fade-out');
    }
});
</script>
