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
            color: #0f172a;
            margin-bottom: 8px;
        }

        .community-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .poi-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }

        .poi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.1);
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
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #3b82f6;
        }

        .poi-content {
            padding: 20px;
            flex-grow: 1;
        }

        .poi-owner {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .poi-content h3 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .poi-content p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .poi-actions {
            display: flex;
            gap: 16px;
            padding: 16px 20px;
            border-top: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        .action-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover { color: #3b82f6; }
        .action-btn.liked { color: #ef4444; }
        .action-btn.liked i { font-variation-settings: 'FILL' 1; }

        .comments-section {
            display: none;
            border-top: 1px solid #f1f5f9;
            padding: 20px;
            background: #fff;
        }

        .comment-item {
            margin-bottom: 12px;
            font-size: 13px;
        }

        .comment-user { font-weight: 700; color: #0f172a; margin-right: 4px; }
        .comment-text { color: #475569; }

        .comment-form {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .comment-form input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
        }

        .comment-form button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
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
                <a href="index">Início</a>
                <a href="sobre">Sobre a PAP</a>
                <a href="destaques">Destaques</a>
                <a href="comunidade" class="active">Comunidade</a>
                <a href="map">Mapa</a>
                <a href="meus_locais">Meus Locais</a>
            </div>
        </div>
    </nav>

    <div class="community-container">
        <div class="community-header">
            <h1>Comunidade MyFamalicão</h1>
            <p>Descobre locais partilhados por outros exploradores.</p>
        </div>

        <div id="community-grid" class="community-grid">
            <!-- Os pontos serão carregados aqui via JS -->
        </div>
    </div>

    <script src="ui_notifications.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', loadCommunity);

        async function loadCommunity() {
            const grid = document.getElementById('community-grid');
            grid.innerHTML = '<p style="text-align:center; grid-column: 1/-1;">A carregar locais mágicos...</p>';

            try {
                const response = await fetch('api_social.php');
                const data = await response.json();

                if (data.status === 'success') {
                    if (data.pois.length === 0) {
                        grid.innerHTML = '<p style="text-align:center; grid-column: 1/-1;">Ainda não há locais públicos. Sê o primeiro a partilhar!</p>';
                        return;
                    }

                    grid.innerHTML = '';
                    data.pois.forEach(poi => {
                        const card = createPoiCard(poi);
                        grid.appendChild(card);
                    });
                }
            } catch (err) {
                console.error(err);
                myFama.toast("Erro ao carregar a comunidade.", "error");
            }
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
                <div class="poi-content">
                    <div class="poi-owner"><i class="ph ph-user-circle"></i> por ${poi.owner_name}</div>
                    <h3>${poi.name}</h3>
                    <p>${poi.description}</p>
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
    </script>
    <?php include "translation_footer.php"; ?>
</body>
</html>
