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
    <title>Destaques - MyFamalicão</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="main_style.css">
    <link rel="stylesheet" href="ui_notifications.css">
    <style>
        .highlights-header {
            text-align: center;
            padding: 80px 20px 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        .highlights-header h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
        }
        .highlights-header p {
            font-size: 18px;
            color: var(--text-muted);
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto 80px;
            padding: 0 24px;
        }
        
        .gallery-item {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            background: var(--surface);
            border: 1px solid var(--border);
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.2);
        }
        
        .gallery-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        
        .gallery-content {
            padding: 24px;
        }
        
        .gallery-tag {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .gallery-content h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }
        
        .gallery-content p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: white;
            text-align: center;
            padding: 80px 24px;
            border-radius: 30px;
            margin: 0 24px 80px;
        }
        
        .cta-section h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        
        .cta-section p {
            color: #94a3b8;
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto 32px;
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
                <a href="sobre">Sobre a PAP</a>
                <a href="destaques" class="active">Destaques</a>
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <a href="comunidade">Comunidade</a>
                <a href="meus_locais">Meus Locais</a>
                <?php endif; ?>
            </div>

            <div class="nav-actions">
                <div class="nav-auth">
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <a href="settings" class="user-greeting" style="font-weight: 600; color: var(--text-main);"><i class="ph-bold ph-user-circle" style="font-size: 18px; vertical-align: middle;"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?></a>
                        <a href="map" class="btn btn-primary-sm">Abrir Mapa</a>
                        <?php if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1): ?>
                        <a href="admin" class="btn-admin-special">Admin</a>
                        <?php endif; ?>
                        <a href="logout" class="btn btn-danger-sm"><i class="ph-bold ph-sign-out"></i></a>
                    <?php
else: ?>
                        <a href="login" class="btn btn-outline">Entrar</a>
                        <a href="register" class="btn btn-primary-sm">Criar Conta</a>
                    <?php
endif; ?>
                </div>

            </div>
        </div>
    </nav>

    <div class="highlights-header">
        <h1>Pontos de Interesse</h1>
        <p>Descobre os locais mais emblemáticos e visitados de Vila Nova de Famalicão. Adiciona-os ao teu roteiro personalizado.</p>
    </div>

    <!-- Grelha de Destaques -->
    <div class="gallery-grid">
        <div class="gallery-item">
            <img class="gallery-img" src="https://www.parquedadevesa.com/op/image/?co=1113&h=ccd6c" alt="Parque da Devesa">
            <div class="gallery-content">
                <span class="gallery-tag">Natureza</span>
                <h3>Parque da Devesa</h3>
                <p>O pulmão verde da cidade. Um vasto parque urbano perfeito para caminhadas, desporto e momentos de lazer em contacto com a natureza e as margens do rio Pelhe.</p>
            </div>
        </div>

        <div class="gallery-item">
            <img class="gallery-img" src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Palacete_Br%C3%A3o_da_Troviqueira.jpg" alt="Museu Bernardino Machado">
            <div class="gallery-content">
                <span class="gallery-tag">Cultura</span>
                <h3>Museu Bernardino Machado</h3>
                <p>Instalado no Palacete Barão da Trovisqueira, dedica-se à preservação da memória da Primeira República e da vida de um dos seus presidentes mais carismáticos.</p>
            </div>
        </div>

        <div class="gallery-item">
            <img class="gallery-img" src="https://images.mnstatic.com/83/ff/83ff5471cac7e0b42b062e0a16024e7c.jpg" alt="Igreja Matriz Nova">
            <div class="gallery-content">
                <span class="gallery-tag">Monumento</span>
                <h3>Igreja Matriz Nova</h3>
                <p>Um dos monumentos religiosos mais imponentes da região, com uma arquitetura impressionante que domina o centro da cidade.</p>
            </div>
        </div>
        
        <div class="gallery-item">
            <img class="gallery-img" src="https://rpac.pt/_rpac/wp-content/uploads/2023/09/Fundacao-Cupertino-Miranda-Exterior-2-FCM_2_ext.jpg" alt="Fundação Cupertino de Miranda">
            <div class="gallery-content">
                <span class="gallery-tag">Arte Surrealista</span>
                <h3>Fundação Cupertino de Miranda</h3>
                <p>Possui o acervo mais importante de arte surrealista em Portugal. A sua torre azulejada é um dos ex-libris de Famalicão.</p>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="cta-section">
        <h2>Pronto para criar a tua rota?</h2>
        <p>Acede ao nosso mapa interativo, seleciona os teus locais favoritos e recebe o trajeto otimizado diretamente no teu GPS.</p>
        
        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            <a href="map" class="btn btn-primary" style="background-color: white; color: #0f172a;">Abrir Mapa Interativo</a>
        <?php
else: ?>
            <a href="register" class="btn btn-primary">Criar a tua Conta</a>
        <?php
endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> MyFamalicão. PAP de Rodrigo de Frutuoso.</p>
    </footer>

    <script src="ui_notifications.js"></script>
    <?php include "translation_footer.php"; ?>
</body>
</html>
