<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>MyFamalicão - Roteiro Interativo</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <!-- Phosphor Icons para ícones modernos -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="ui_notifications.css">
    <style>
        .user-profile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(59, 130, 246, 0.1);
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            background: var(--primary);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
        }
        .user-name {
            font-weight: 600;
            font-size: 14px;
        }
        .btn-logout {
            color: var(--danger);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .btn-logout:hover {
            background: #fee2e2;
        }
    </style>
</head>
<body>

    <!-- Mapa Container -->
    <div id="map"></div>

    <!-- Painel Lateral Glassmorphism -->
    <aside id="sidebar" class="sidebar">
        <!-- Botão Voltar -->
        <div class="sidebar-nav-top">
            <a href="index" class="btn-side-nav"><i class="ph-bold ph-house"></i> Início</a>
            <a href="comunidade" class="btn-side-nav"><i class="ph-bold ph-users"></i> Comunidade</a>
            <a href="meus_locais" class="btn-side-nav"><i class="ph-bold ph-heart-straight"></i> Meus Locais</a>
        </div>
        
        <header class="sidebar-header">
            <div class="logo">
                <i class="ph-fill ph-map-pin-line"></i>
                <h1>MyFamalicão</h1>
            </div>
            <p>Descobre, cria e explora rotas em Vila Nova de Famalicão.</p>
        </header>

        <!-- Perfil de Utilizador e Logout -->
        <div class="user-profile">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="ph-bold ph-user-circle"></i>
                </div>
                <div>
                    <span style="font-size: 11px; color: var(--text-muted); display: block; line-height: 1;">Autenticado como</span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                </div>
            </div>
            <a href="logout" class="btn-logout" title="Terminar Sessão">
                <i class="ph-bold ph-sign-out"></i>
            </a>
        </div>

        <section class="route-section">
            <div class="route-header">
                <h2>O teu Roteiro</h2>
                <span id="route-count" class="badge">0 locais</span>
            </div>
            
            <ul id="route-list" class="route-list">
                <li class="empty-state">
                    <i class="ph ph-mask-happy"></i>
                    <p>Clica num ponto no mapa para o adicionar à tua rota.</p>
                </li>
            </ul>

            <div class="action-buttons">
                <button id="btn-export-google" class="btn btn-primary" disabled>
                    <i class="ph-bold ph-google-logo"></i>
                    Abrir no Google Maps
                </button>
                <div class="btn-group">
                    <button id="btn-audio-main" class="btn btn-secondary" onclick="playFullRouteAudio()">
                        <i class="ph-fill ph-speaker-high"></i>
                        Áudio-Guia
                    </button>
                    <button id="btn-clear" class="btn btn-danger" disabled>
                        <i class="ph-bold ph-trash"></i>
                    </button>
                </div>
            </div>
        </section>
    </aside>

    <!-- Botão de Ajuda Flutuante -->
    <button class="btn-help" onclick="document.getElementById('helpModal').style.display='flex'">
        <i class="ph-bold ph-question"></i> Ajuda
    </button>

    <!-- Botão de Localização Flutuante -->
    <button class="btn-location" onclick="locateUser()" title="A minha localização">
        <i class="ph-bold ph-crosshair"></i>
    </button>

    <!-- Modal de Ajuda -->
    <div id="helpModal" class="modal-overlay">
        <div class="modal-content">
            <button class="modal-close" onclick="document.getElementById('helpModal').style.display='none'">
                <i class="ph-bold ph-x"></i>
            </button>
            <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: var(--primary);">
                <i class="ph-fill ph-info"></i> Como usar o Mapa
            </h2>
            
            <div class="help-section">
                <h3>🗺️ Criar o teu Roteiro</h3>
                <p>Clica nos marcadores azuis (Pontos de Interesse) e arrasta o botão <strong>"Adicionar à Rota"</strong>. Podes ver a tua rota selecionada no painel lateral esquerdo.</p>
            </div>

            <div class="help-section">
                <h3>⭐ Criar os teus próprios Locais</h3>
                <ol style="margin-left: 20px; color: var(--text-muted); line-height: 1.6;">
                    <li>Clica em qualquer <strong>lugar vazio no mapa</strong> (onde não existam marcadores azuis).</li>
                    <li>Vai aparecer um formulário. Preenche o <strong>Nome</strong> e a <strong>Descrição</strong> do teu local.</li>
                    <li>Clica em <strong>"Guardar"</strong>.</li>
                    <li>Parabéns! O teu local (estrela vermelha) foi guardado na tua conta e podes adicioná-lo à tua rota.</li>
                </ol>
            </div>

            <div class="help-section">
                <h3>🚗 Exportar para o GPS</h3>
                <p>Quando tiveres a tua rota construída no painel (com os locais que escolheste), clica em <strong>"Abrir no Google Maps"</strong>. O sistema vai gerar automaticamente um itinerário no GPS com a ordem que escolheste!</p>
            </div>
            
            <button class="btn btn-primary" style="width: 100%; margin-top: 10px;" onclick="document.getElementById('helpModal').style.display='none'">
                Entendido, vamos lá explorar!
            </button>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="ui_notifications.js"></script>
    <script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
