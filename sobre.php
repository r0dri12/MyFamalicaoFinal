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
    <title>Sobre a PAP - MyFamalicão</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="main_style.css">
    <link rel="stylesheet" href="ui_notifications.css">
    <style>
        .about-section {
            padding: 80px 20px;
            max-width: 900px;
            margin: 0 auto;
        }
        .about-header {
            text-align: center;
            margin-bottom: 60px;
        }
        .about-header h1 {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 16px;
            color: var(--text-main);
        }
        .about-header p {
            font-size: 18px;
            color: var(--text-muted);
        }
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }
        .about-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid var(--border);
        }
        .about-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary);
        }
        .about-card p {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 16px;
        }
        .student-info {
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 60px;
        }
        .student-info h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .student-info p {
            opacity: 0.9;
            font-size: 16px;
        }
        @media (max-width: 768px) {
            .about-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include "translation_loader.php"; ?>

    <!-- Navegação Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index" class="nav-logo">
                <i class="ph-fill ph-map-pin-line"></i>
                <span>MyFamalicão</span>
            </a>

            <div class="nav-links">
                <a href="index">Início</a>
                <a href="sobre" class="active">Sobre a PAP</a>
                <a href="destaques">Destaques</a>
                <a href="comunidade">Comunidade</a>
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <a href="map">Mapa</a>
                <a href="meus_locais">Meus Locais</a>
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

    <div class="about-section">
        <div class="about-header">
            <h1>Prova de Aptidão Profissional</h1>
            <p>Curso Profissional de Técnico de Gestão e Programação de Sistemas Informáticos (2025/2026)</p>
        </div>

        <div class="student-info">
            <h2>Rodrigo Afonso Loureiro de Frutuoso</h2>
            <p>Nº Processo: 37866 - Agrupamento de Escolas Camilo Castelo Branco</p>
        </div>

        <div class="about-grid">
            <div class="about-card">
                <h3><i class="ph-fill ph-target"></i> Objetivos do Projeto</h3>
                <p>O foco central da PAP é criar uma plataforma de utilidade pública para promover a cidade de Vila Nova de Famalicão e apoiar o turismo local e regional.</p>
                <p>Através duma interface intuitiva, os visitantes podem delinear percursos turísticos organizados de forma digital.</p>
            </div>
            
            <div class="about-card">
                <h3><i class="ph-fill ph-code"></i> Tecnologias Aplicadas</h3>
                <p>Embora idealizado inicialmente para MAUI C#, o projeto evoluiu para uma <strong>Progressive Web App (PWA)</strong> garantindo acesso universal (Android, iOS e Desktop) sem necessidade de instalação.</p>
                <p>É focado em <strong>Acessibilidade e Usabilidade</strong>, adotando as frameworks de design web mais atualizadas (HTML5, CSS3, ES6 e PHP backend).</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> MyFamalicão. PAP de Rodrigo de Frutuoso.</p>
    </footer>

    <script src="ui_notifications.js"></script>
    <?php include "translation_footer.php"; ?>
</body>
</html>
