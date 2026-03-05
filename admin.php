<?php
session_start();
require_once "db_connect.php";

// Access Control
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index");
    exit;
}

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION["id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['is_admin'] != 1) {
    echo "<h1>Acesso Negado</h1><p>Não tens permissões administrativas.</p><a href='index'>Voltar ao Início</a>";
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
    <title>Painel de Administração - MyFamalicão</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="main_style.css">
    <link rel="stylesheet" href="ui_notifications.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 24px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .admin-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
        }

        .admin-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 2px;
        }

        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: none;
            font-size: 15px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border-radius: 12px 12px 0 0;
            transition: all 0.2s;
            position: relative;
        }

        .tab-btn:hover { color: #0f172a; background: #f8fafc; }

        .tab-btn.active {
            color: #3b82f6;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #3b82f6;
        }

        .admin-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .admin-table th {
            padding: 16px 24px;
            background: #f8fafc;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .admin-table td {
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
            font-size: 14px;
            color: #334155;
            vertical-align: middle;
        }

        .admin-table tr:hover td { background: #fcfdfe; }

        .status-badge {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-admin { background: #eff6ff; color: #3b82f6; }
        .badge-user { background: #f1f5f9; color: #64748b; }

        .actions-cell {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 18px;
        }

        .btn-delete { background: #fee2e2; color: #ef4444; }
        .btn-delete:hover { background: #ef4444; color: white; }

        .btn-toggle { background: #f1f5f9; color: #475569; }
        .btn-toggle:hover { background: #3b82f6; color: white; }

        #admin-content {
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
    </style>
</head>
<body>
    <?php include "translation_loader.php"; ?>

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
                <a href="comunidade">Comunidade</a>
                <a href="meus_locais">Meus Locais</a>
            </div>

            <div class="nav-actions">
                <div class="nav-auth">
                    <a href="settings" class="user-greeting" style="font-weight: 600; color: var(--text-main);">
                        <i class="ph-bold ph-user-circle" style="font-size: 18px; vertical-align: middle;"></i> 
                        <span class="greeting-name"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                    </a>
                    <a href="logout" class="btn btn-danger-sm">
                        <i class="ph-bold ph-sign-out"></i>
                    </a>
                    <?php if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1): ?>
                    <a href="admin" class="btn-admin-special" style="margin-left: 10px;">
                        <i class="ph-bold ph-shield-check"></i> Admin (Ativo)
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Gestão do Sistema</h1>
        </div>

        <div class="admin-tabs">
            <button class="tab-btn active" data-tab="users" onclick="switchTab('users')"><i class="ph-bold ph-users"></i> Utilizadores</button>
            <button class="tab-btn" data-tab="routes" onclick="switchTab('routes')"><i class="ph-bold ph-map-trifold"></i> Rotas</button>
            <button class="tab-btn" data-tab="pois" onclick="switchTab('pois')"><i class="ph-bold ph-map-pin"></i> Locais Partilhados</button>
        </div>

        <div class="admin-card">
            <div id="users-panel" class="tab-panel active">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Utilizador</th>
                            <th>Cargo</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="users-list">
                        <!-- Carregado via JS -->
                    </tbody>
                </table>
            </div>

            <div id="routes-panel" class="tab-panel">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nome da Rota</th>
                            <th>Proprietário</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="routes-list">
                        <!-- Carregado via JS -->
                    </tbody>
                </table>
            </div>

            <div id="pois-panel" class="tab-panel">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Local</th>
                            <th>Tipo</th>
                            <th>Publicado por</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="pois-list">
                        <!-- Carregado via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="ui_notifications.js"></script>
    <script>
        let adminData = { users: [], routes: [], pois: [] };

        async function loadAdminData(type = 'all') {
            try {
                const res = await fetch(`api_admin.php?type=${type}`);
                const result = await res.json();
                if (result.status === 'success') {
                    if (type === 'all' || type === 'users') {
                        adminData.users = result.data.users;
                        renderUsers();
                    }
                    if (type === 'all' || type === 'routes') {
                        adminData.routes = result.data.routes;
                        renderRoutes();
                    }
                    if (type === 'all' || type === 'pois') {
                        adminData.pois = result.data.pois;
                        renderPois();
                    }
                }
            } catch (err) {
                myFama.toast("Erro ao carregar dados.", "error");
            }
        }

        function renderUsers() {
            const list = document.getElementById('users-list');
            list.innerHTML = adminData.users.map(u => `
                <tr>
                    <td><strong>${u.username}</strong><br><small style="color:#64748b">${u.full_name}</small></td>
                    <td><span class="status-badge ${u.is_admin == 1 ? 'badge-admin' : 'badge-user'}">${u.is_admin == 1 ? 'Admin' : 'Membro'}</span></td>
                    <td>${new Date(u.created_at).toLocaleDateString()}</td>
                    <td class="actions-cell">
                        <button class="btn-action btn-toggle" title="Alternar Admin" onclick="toggleAdmin(${u.id})"><i class="ph-bold ph-arrows-counter-clockwise"></i></button>
                        <button class="btn-action btn-delete" title="Eliminar Utilizador" onclick="deleteItem('delete_user', ${u.id})"><i class="ph-bold ph-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }

        function renderRoutes() {
            const list = document.getElementById('routes-list');
            list.innerHTML = adminData.routes.map(r => `
                <tr>
                    <td><strong>${r.route_name}</strong></td>
                    <td>${r.owner_name}</td>
                    <td>${new Date(r.created_at).toLocaleDateString()}</td>
                    <td class="actions-cell">
                        <button class="btn-action btn-delete" onclick="deleteItem('delete_route', ${r.id})"><i class="ph-bold ph-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }

        function renderPois() {
            const list = document.getElementById('pois-list');
            list.innerHTML = adminData.pois.map(p => `
                <tr>
                    <td><strong>${p.name}</strong></td>
                    <td>${p.type}</td>
                    <td>${p.owner_name}</td>
                    <td class="actions-cell">
                        <button class="btn-action btn-delete" onclick="deleteItem('delete_poi', ${p.id})"><i class="ph-bold ph-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }

        function switchTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');

            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
            document.getElementById(`${tabId}-panel`).classList.add('active');
        }

        async function toggleAdmin(id) {
            try {
                const res = await fetch('api_admin.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'toggle_admin', id: id })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    myFama.toast(data.message, "success");
                    loadAdminData('users');
                } else {
                    myFama.alert("Erro", data.message, "error");
                }
            } catch (err) { }
        }

        async function deleteItem(action, id) {
            const confirmed = await myFama.confirm("Eliminar Item", "Tens a certeza que queres eliminar este item permanentemente?");
            if (!confirmed) return;

            try {
                const res = await fetch('api_admin.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: action, id: id })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    myFama.toast(data.message, "info");
                    loadAdminData();
                } else {
                    myFama.alert("Erro", data.message, "error");
                }
            } catch (err) { }
        }

        document.addEventListener('DOMContentLoaded', () => loadAdminData());
    </script>
</body>
</html>
