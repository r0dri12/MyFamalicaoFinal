<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login");
    exit;
}

// Auto-Translate logic based on User Preference
$userLang = $_SESSION["language"] ?? 'pt';
?>
<!DOCTYPE html>
<html lang="<?php echo $userLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>MyFamalicão - Mapa Interativo</title>
    <?php include "translation_header.php"; ?>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <!-- Leaflet Routing Machine CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    
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
            color: var(--text-main);
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
            background: var(--border);
        }

        /* Language Selector Styling */
        .lang-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 24px;
            padding: 10px;
            background: var(--secondary);
            border-radius: 12px;
            border: 1px solid var(--border);
        }
        .lang-btn {
            width: 100%;
            padding: 8px 4px;
            border: 1px solid var(--border);
            background: var(--surface);
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
        }
        .lang-btn img {
            width: 20px;
            height: 15px;
            object-fit: cover;
            border-radius: 2px;
            filter: grayscale(0.2);
        }
        .lang-btn span {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
        }
        .lang-btn:hover {
            border-color: var(--primary);
            background: var(--secondary);
        }
        .lang-btn.active {
            border-color: var(--primary);
            background: var(--primary);
        }
        .lang-btn.active span {
            color: white;
        }
        .lang-btn.active img {
            filter: none;
        }

        /* Hide Google Translate original UI components */
        #google_translate_element {
            opacity: 0;
            position: absolute;
            z-index: -1;
            width: 0;
            height: 0;
            overflow: hidden;
        }
        .goog-te-banner-frame.skiptranslate, 
        .goog-te-gadget-icon,
        .goog-te-banner,
        iframe.skiptranslate,
        #goog-gt-tt,
        .goog-te-balloon-frame {
            display: none !important;
            visibility: hidden !important;
        }
        body {
            top: 0px !important;
        }
        .goog-te-gadget-simple {
            background-color: transparent !important;
            border: none !important;
        }
    </style>
</head>
<body>
    <?php include "translation_loader.php"; ?>

    <!-- Mapa Container -->
    <div id="map"></div>

    <!-- Painel Lateral Glassmorphism -->
    <aside id="sidebar" class="sidebar">
        <!-- Handle para Mobile -->
        <div class="mobile-handle"></div>
        
        <!-- Botão Voltar -->
        <div class="sidebar-nav-top">
            <a href="index" class="btn-side-nav"><i class="ph-bold ph-house"></i> Início</a>
            <a href="comunidade" class="btn-side-nav"><i class="ph-bold ph-users"></i> Comunidade</a>
            <a href="meus_locais" class="btn-side-nav"><i class="ph-bold ph-heart-straight"></i> Meus Locais</a>
        </div>
        


        <!-- Perfil de Utilizador e Logout -->
        <div class="user-profile">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="ph-bold ph-user-circle"></i>
                </div>
                <div>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                </div>
            </div>
            <a href="logout" class="btn-logout" title="Terminar Sessão">
                <i class="ph-bold ph-sign-out"></i>
            </a>
        </div>

        <!-- Tradução Automática (Invisível mas 'presente' para o DOM) -->


        <section class="route-section">
            <div class="route-header">
                <h2>O teu Roteiro</h2>
                <span id="route-count" class="badge">0 locais</span>
                <button class="btn-icon" id="btn-history" title="Histórico de Rotas" onclick="openHistoryModal()" style="background:var(--primary); color:white; width:30px; height:30px; border-radius:8px;">
                    <i class="ph-bold ph-clock-counter-clockwise"></i>
                </button>
            </div>
            
            <div id="route-summary" class="route-summary" style="display:none;">
                <div>
                    <span id="route-distance"><i class="ph-bold ph-map-trifold"></i> -- km</span>
                    <span id="route-time"><i class="ph-bold ph-clock"></i> -- min</span>
                </div>
            </div>
            
            <ul id="route-list" class="route-list">
                <li class="empty-state">
                    <i class="ph-fill ph-map-trifold"></i>
                    <p>Clica num ponto no mapa para o adicionar à tua rota.</p>
                </li>
            </ul>

            <div class="action-buttons">
                <button id="btn-export-google" class="btn btn-primary" disabled>
                    <i class="ph-bold ph-google-logo"></i>
                    Abrir no Google Maps
                </button>
                <button id="btn-save-route" class="btn btn-success" disabled onclick="openSaveRouteModal()" style="background:#10b981; color:white;">
                    <i class="ph-bold ph-floppy-disk"></i>
                    Guardar Rota
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

    <!-- Botão para Reabrir Rotas (Mobile) -->
    <button id="btn-reopen-routes" class="btn-routes-toggle" title="Abrir Roteiro">
        <i class="ph-bold ph-list-bullets"></i>
        <span>Roteiro</span>
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

            <div class="help-section">
                <h3>🔊 Áudio-Guia</h3>
                <p>Podes ouvir a descrição de qualquer local clicando no ícone de <strong>altifalante</strong>. No painel lateral, podes também ativar o áudio-guia para todo o teu roteiro!</p>
            </div>

            <div class="help-section">
                <h3>🌍 Mudar Idioma</h3>
                <p>Usa os botões de bandeira no painel lateral para traduzir instantaneamente todo o mapa e descrições para o teu idioma preferido.</p>
            </div>

            <div class="help-section">
                <h3>📍 Minha Localização</h3>
                <p>Clica no botão de <strong>mira</strong> no canto inferior direito para centrares o mapa na tua posição atual em tempo real.</p>
            </div>

            <div class="help-section">
                <h3>👥 Comunidade</h3>
                <p>Ao criares um local, podes marcar a opção <strong>"Tornar público"</strong> para que outros exploradores o vejam na página da Comunidade!</p>
            </div>
            
            <button class="btn btn-primary" style="width: 100%; margin-top: 10px;" onclick="document.getElementById('helpModal').style.display='none'">
                Entendido, vamos lá explorar!
            </button>
        </div>
    </div>

    <!-- Modal de Início de Rota -->
    <div id="startRouteModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px; text-align:center;">
            <button class="modal-close" onclick="document.getElementById('startRouteModal').style.display='none'">
                <i class="ph-bold ph-x"></i>
            </button>
            <i class="ph-fill ph-map-pin-line" style="font-size:48px; color:var(--primary); margin-bottom:16px;"></i>
            <h2 style="margin-bottom: 8px; color: var(--primary);">Como queres começar?</h2>
            <p style="color:var(--text-muted); margin-bottom:24px; font-size:14px;">Define o ponto de partida para o teu roteiro em Famalicão.</p>
            
            <div style="display:flex; flex-direction:column; gap:12px;">
                <button class="btn btn-primary" onclick="confirmStartRoute('gps')" style="background:var(--primary);">
                    <i class="ph-bold ph-crosshair"></i> A Minha Localização Atual
                </button>
                <button id="btn-start-selected" class="btn btn-secondary" onclick="confirmStartRoute('selected')">
                    <i class="ph-bold ph-map-pin"></i> Este Local no Mapa
                </button>
                <button id="btn-start-browse" class="btn btn-outline" onclick="document.getElementById('startRouteModal').style.display='none'" style="border:1px solid var(--border); padding:12px; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer;">
                    Eu escolho no mapa...
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para Guardar Rota -->
    <div id="saveRouteModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <button class="modal-close" onclick="document.getElementById('saveRouteModal').style.display='none'">
                <i class="ph-bold ph-x"></i>
            </button>
            <h2 style="margin-bottom: 20px; color: var(--primary);"><i class="ph-fill ph-floppy-disk"></i> Guardar Rota</h2>
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; font-size:14px;">Nome do Roteiro</label>
                <input type="text" id="route-name-input" placeholder="Ex: Passeio de Domingo" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:10px; font-family:inherit;">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; font-size:14px;">Descrição (Opcional)</label>
                <textarea id="route-desc-input" placeholder="Breve descrição do teu percurso..." style="width:100%; padding:12px; border:1px solid var(--border); border-radius:10px; font-family:inherit; height: 80px; resize: none;"></textarea>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px;">
                    <input type="checkbox" id="route-public-checkbox" style="width:18px; height:18px;"> 
                    <span>Tornar público para a comunidade</span>
                </label>
            </div>
            <button class="btn btn-primary" onclick="confirmSaveRoute()" style="width:100%; background:#10b981;">Guardar Roteiro</button>
        </div>
    </div>

    <!-- Modal de Histórico de Rotas -->
    <div id="historyModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <button class="modal-close" onclick="document.getElementById('historyModal').style.display='none'">
                <i class="ph-bold ph-x"></i>
            </button>
            <h2 style="margin-bottom: 20px; color: var(--primary);"><i class="ph-fill ph-clock-counter-clockwise"></i> Minhas Rotas</h2>
            <div id="history-list" style="max-height: 400px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">
                <!-- Carregado via JS -->
                <p style="text-align:center; color:var(--text-muted);">A carregar histórico...</p>
            </div>
        </div>
    </div>

    <!-- Tradução Scripts -->
    <script type="text/javascript">
        // Ao carregar a página, marcar o botão correto com base no cookie
        document.addEventListener('DOMContentLoaded', function() {
            var cookies = document.cookie.split(';');
            var currentLang = 'pt';
            for(var i=0; i < cookies.length; i++) {
                if(cookies[i].trim().indexOf('googtrans=') === 0) {
                    currentLang = cookies[i].split('/').pop();
                    break;
                }
            }
            
            document.querySelectorAll('.lang-btn').forEach(btn => {
                const onclickAttr = btn.getAttribute('onclick');
                if(onclickAttr && onclickAttr.includes("'" + currentLang + "'")) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        });
    </script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- Leaflet Routing Machine JS -->
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script src="ui_notifications.js"></script>
    <script src="script.js?v=<?php echo time(); ?>"></script>
    <?php include "translation_footer.php"; ?>
</body>
</html>
