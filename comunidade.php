<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index");
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'pt'; ?>">
<head>
    <?php include "translation_header.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Comunidade - MyFamalicão</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <link rel="stylesheet" href="main_style.css">
    <link rel="stylesheet" href="ui_notifications.css">
    
    <style>
        .community-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 24px;
        }

        .community-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .community-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .community-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .poi-card {
            background: var(--surface);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }

        .poi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.4);
        }

        .poi-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .poi-type {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--surface);
            backdrop-filter: blur(4px);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--primary);
        }

        .poi-content {
            padding: 20px;
            flex-grow: 1;
        }

        .poi-owner {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .poi-content h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .poi-content p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .poi-actions {
            display: flex;
            gap: 16px;
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            background: var(--background);
        }

        .action-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover { color: var(--primary); }
        .action-btn.liked { color: #ef4444; }
        .action-btn.liked i { font-variation-settings: 'FILL' 1; }

        .comments-section {
            display: none;
            border-top: 1px solid var(--border);
            padding: 20px;
            background: var(--surface);
        }

        .comment-item {
            margin-bottom: 12px;
            font-size: 13px;
        }

        .comment-user { font-weight: 700; color: var(--text-main); margin-right: 4px; }
        .comment-text { color: var(--text-muted); }

        .comment-form {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .comment-form input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            background: var(--background);
            color: var(--text-main);
        }

        .comment-form button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
        }

        /* Tabs Styles */
        .community-tabs {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 32px;
        }

        .tab-btn {
            padding: 12px 24px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(59, 130, 246, 0.05);
        }

        .tab-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .route-card {
            background: var(--surface);
            border-radius: 20px;
            border: 1px solid var(--border);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: all 0.3s;
        }

        .route-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.2); }

        .route-badge { 
            background: var(--background); 
            color: var(--text-muted); 
            padding: 4px 10px; 
            border-radius: 8px; 
            font-size: 11px; 
            font-weight: 700;
            text-transform: uppercase;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .spin {
            display: inline-block;
            animation: spin 1s infinite linear;
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
                <a href="destaques">Destaques</a>
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <a href="comunidade" class="active">Comunidade</a>
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

    <div class="community-container">
        <div class="community-header">
            <h1>Comunidade MyFamalicão</h1>
            <p>Descobre locais e roteiros partilhados por outros exploradores.</p>
        </div>

        <div class="community-tabs">
            <button class="tab-btn active" onclick="switchTab('pois')"><i class="ph-bold ph-map-pin"></i> Locais</button>
            <button class="tab-btn" onclick="switchTab('routes')"><i class="ph-bold ph-map-trifold"></i> Rotas</button>
        </div>

        <div id="community-grid" class="community-grid">
            <!-- Os pontos serão carregados aqui via JS -->
        </div>
    </div>

    <script src="ui_notifications.js"></script>
    <script>
        let currentTab = 'pois';
        document.addEventListener('DOMContentLoaded', () => loadCommunity());

        function switchTab(tab) {
            currentTab = tab;
            document.querySelectorAll('.tab-btn').forEach(btn => {
                const isActive = (tab === 'pois' && btn.innerText.includes('Locais')) || 
                                (tab === 'routes' && btn.innerText.includes('Rotas'));
                btn.classList.toggle('active', isActive);
            });
            loadCommunity();
        }

        async function loadCommunity() {
            const grid = document.getElementById('community-grid');
            grid.innerHTML = '<p style="text-align:center; grid-column: 1/-1;">A carregar...</p>';

            try {
                const response = await fetch(`api_social.php?type=${currentTab}`);
                const data = await response.json();

                if (data.status === 'success') {
                    const items = currentTab === 'pois' ? data.pois : data.routes;
                    
                    if (items.length === 0) {
                        grid.innerHTML = `<p style="text-align:center; grid-column: 1/-1;">Ainda não há ${currentTab === 'pois' ? 'locais' : 'rotas'} públicos.</p>`;
                        return;
                    }

                    grid.innerHTML = '';
                    items.forEach(item => {
                        grid.appendChild(currentTab === 'pois' ? createPoiCard(item) : createRouteCard(item));
                    });
                }
            } catch (err) {
                console.error(err);
                myFama.toast("Erro ao carregar.", "error");
            }
        }

        function createRouteCard(route) {
            const div = document.createElement('div');
            div.className = 'route-card';
            
            div.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <span class="route-badge"><i class="ph ph-path"></i> ${route.points_count} Pontos</span>
                        <h3 style="margin-top:12px; font-size:20px; font-weight:800; color:#0f172a;">${route.route_name}</h3>
                    </div>
                </div>
                <div class="poi-owner"><i class="ph ph-user-circle"></i> por ${route.owner_name}</div>
                <p style="font-size:14px; color:#64748b; line-height:1.5; margin:0;">${route.description || 'Um roteiro incrível por Famalicão.'}</p>
                
                <div style="margin-top:auto; padding-top:16px; border-top:1px solid #f1f5f9;">
                    <a href="map?load_route=${route.id}" class="btn btn-primary-sm" style="width:100%;">
                        <i class="ph ph-play-circle"></i> Começar Rota
                    </a>
                </div>
            `;
            return div;
        }

        function createPoiCard(poi) {
            const div = document.createElement('div');
            div.className = 'poi-card';
            div.id = `card-${poi.id}`;
            
            const image = poi.image || 'https://images.unsplash.com/photo-1524661135-423995f22d0b';
            
            div.innerHTML = `
                <div class="poi-image" style="background-image: url('${image}')">
                    <span class="poi-type">${poi.type}</span>
                </div>
                <div class="poi-content" style="display: flex; flex-direction: column; flex-grow: 1;">
                    <div class="poi-owner"><i class="ph ph-user-circle"></i> por ${poi.owner_name}</div>
                    <h3>${poi.name}</h3>
                    <p style="flex-grow: 1;">${poi.description}</p>
                    
                    <div style="margin-top:auto; padding-top:16px; border-top:1px solid var(--border);">
                        <button class="btn btn-primary-sm" onclick="addToMyMap(${poi.id}, this)" style="width:100%; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="ph-bold ph-plus-circle"></i> Adicionar ao meu mapa
                        </button>
                    </div>
                </div>
                <div class="poi-actions">
                    <button class="action-btn ${poi.user_liked > 0 ? 'liked' : ''}" onclick="toggleLike(${poi.id})">
                        <i class="ph-fill ph-heart"></i> <span class="count">${poi.likes_count}</span>
                    </button>
                    <button class="action-btn" onclick="toggleComments(${poi.id})">
                        <i class="ph-bold ph-chat-circle"></i> ${poi.comments_count}
                    </button>
                </div>
                <div class="comments-section" id="comments-${poi.id}">
                    <div class="comments-list"></div>
                    <form class="comment-form" onsubmit="submitComment(event, ${poi.id})">
                        <input type="text" placeholder="Escreve um comentário..." required>
                        <button type="submit">Enviar</button>
                    </form>
                </div>
            `;
            return div;
        }

        async function toggleLike(id) {
            try {
                const res = await fetch('api_social.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'like', poi_id: id })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    loadCommunity(); // Reload to update counts
                }
            } catch (err) {
                myFama.toast("Erro ao processar like.", "error");
            }
        }

        function toggleComments(id) {
            const section = document.getElementById(`comments-${id}`);
            if (section.style.display === 'block') {
                section.style.display = 'none';
            } else {
                section.style.display = 'block';
                loadComments(id);
            }
        }

        async function loadComments(id) {
            const list = document.querySelector(`#comments-${id} .comments-list`);
            list.innerHTML = '<p style="font-size:12px; color:#999;">A carregar comentários...</p>';
            
            try {
                const res = await fetch('api_social.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'get_comments', poi_id: id })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    if (data.comments.length === 0) {
                        list.innerHTML = '<p style="font-size:12px; color:#999;">Sem comentários ainda.</p>';
                    } else {
                        list.innerHTML = data.comments.map(c => `
                            <div class="comment-item">
                                <span class="comment-user">${c.username}:</span>
                                <span class="comment-text">${c.comment}</span>
                            </div>
                        `).join('');
                    }
                }
            } catch (err) {
                list.innerHTML = 'Erro ao carregar.';
            }
        }

        async function submitComment(e, id) {
            e.preventDefault();
            const input = e.target.querySelector('input');
            const comment = input.value;
            
            try {
                const res = await fetch('api_social.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'comment', poi_id: id, comment: comment })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    input.value = '';
                    loadComments(id);
                    // Could also update the count on the button here
                }
            } catch (err) {
                myFama.toast("Erro ao enviar comentário.", "error");
            }
        }

        async function addToMyMap(id, btn) {
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i class="ph-bold ph-spinner-gap spin"></i> A adicionar...`;
            
            try {
                const res = await fetch('api_social.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add_to_my_map', poi_id: id })
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    myFama.toast(data.message, "success");
                    btn.className = "btn btn-success-sm";
                    btn.style.background = "#10b981";
                    btn.style.color = "white";
                    btn.style.borderColor = "#10b981";
                    btn.innerHTML = `<i class="ph-bold ph-check-circle"></i> Adicionado`;
                } else {
                    myFama.toast(data.message, "info");
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            } catch (err) {
                console.error(err);
                myFama.toast("Erro ao processar o pedido.", "error");
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    </script>
    <?php include "translation_footer.php"; ?>
</body>
</html>
