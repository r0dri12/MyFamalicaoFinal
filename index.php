<?php
session_start();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'pt'; ?>">
<head>
    <?php include "translation_header.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>MyFamalicão - O teu Guia Interativo</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <link rel="stylesheet" href="main_style.css">
    <link rel="stylesheet" href="ui_notifications.css">
</head>
<body>

    <!-- Navegação Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index" class="nav-logo">
                <i class="ph-fill ph-map-pin-line"></i>
                <span>MyFamalicão</span>
            </a>

            <div class="nav-links">
                <a href="index" class="active">Início</a>
                <a href="sobre">Sobre a PAP</a>
                <a href="destaques">Destaques</a>
                <a href="comunidade">Comunidade</a>
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <a href="map">Mapa</a>
                <a href="meus_locais">Meus Locais</a>
                <?php
else: ?>
                <a href="login" class="nav-btn">Entrar</a>
                <?php
endif; ?>
            </div>

            <div class="nav-auth">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="settings" class="user-greeting" style="font-weight: 600; color: var(--text-main);"><i class="ph-bold ph-user-circle" style="font-size: 18px; vertical-align: middle;"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?></a>
                    <a href="map" class="btn btn-primary-sm">Abrir Mapa</a>
                    <a href="logout" class="btn btn-danger-sm"><i class="ph-bold ph-sign-out"></i></a>
                <?php
else: ?>
                    <a href="login" class="btn btn-outline">Entrar</a>
                    <a href="register" class="btn btn-primary-sm">Criar Conta</a>
                <?php
endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section / Cabeçalho -->
    <header class="hero">
        <div class="hero-content">
            <span class="badge">Projeto PAP 2025/2026</span>
            <h1>Descobre Vila Nova de Famalicão<br><span>ao teu ritmo.</span></h1>
            <p>A primeira Web App que te permite criar roteiros personalizados pela cidade e abri-los diretamente no teu GPS.</p>
            
            <div class="hero-actions">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="map" class="btn btn-hero btn-primary">
                        <i class="ph-bold ph-map-trifold"></i> Começar a Explorar
                    </a>
                <?php
else: ?>
                    <a href="register" class="btn btn-hero btn-primary">
                        Junta-te a nós <i class="ph-bold ph-arrow-right"></i>
                    </a>
                <?php
endif; ?>
                <a href="sobre" class="btn btn-hero btn-secondary">Saber Mais</a>
            </div>
        </div>
        <div class="hero-image">
            <!-- Imagem de fundo representativa ou mockup da app -->
            <img src="https://imagens.supercasa.pt/Z1280x960/OAYES/S5/C312/WP8338/Tphoto/ID92200000-0000-0500-0000-00000ecc2fb4.jpg" alt="Famalicão">
        </div>
    </header>

    <!-- Funcionalidades / Features -->
    <section class="features">
        <div class="feature-card">
            <div class="feature-icon"><i class="ph-fill ph-map-pin-plus"></i></div>
            <h3>Cria a tua Rota</h3>
            <p>Seleciona os pontos que queres visitar e adiciona-os ao teu roteiro personalizado.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="ph-fill ph-export"></i></div>
            <h3>Exporta para o GPS</h3>
            <p>Gera um link automático para abrires a tua rota no Google Maps e começares a viagem.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="ph-fill ph-speaker-high"></i></div>
            <h3>Áudio-Guia</h3>
            <p>Acessibilidade para todos! Ouve a história de cada ponto por onde passas. <span class="soon-badge">Em Breve</span></p>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> MyFamalicão. PAP de Rodrigo de Frutuoso.</p>
    </footer>

    <script src="ui_notifications.js"></script>
    <?php include "translation_footer.php"; ?>
</body>
</html>
