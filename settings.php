<?php
session_start();
require_once "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Get user data (now includes full_name, profile_picture, and language)
$sql = "SELECT username, full_name, profile_picture, created_at, language FROM users WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update session language if it differs (sync)
$_SESSION["language"] = $user['language'] ?? 'pt';

// Count custom POIs
$sql_count = "SELECT COUNT(*) as total FROM custom_pois WHERE user_id = :id";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bindParam(":id", $user_id, PDO::PARAM_INT);
$stmt_count->execute();
$poi_count = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

$initials = strtoupper(substr($user['username'], 0, 2));
$display_name = !empty($user['full_name']) ? $user['full_name'] : $user['username'];
$join_date = date('F Y', strtotime($user['created_at']));

// Badge logic
if ($poi_count >= 10) {
    $badge = "🏆 Guia Local";
}
elseif ($poi_count >= 5) {
    $badge = "🌟 Explorador";
}
elseif ($poi_count > 0) {
    $badge = "🎒 Turista";
}
else {
    $badge = "🌱 Novato";
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'pt'; ?>">
<head>
    <?php include "translation_header.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Definições - MyFamalicão</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="main_style.css">
    <link rel="stylesheet" href="ui_notifications.css">
    <style>
        .settings-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 24px 80px;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 32px;
        }

        @media (max-width: 768px) {
            .settings-container { grid-template-columns: 1fr; }
        }

        .settings-sidebar {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border);
            height: fit-content;
            position: sticky;
            top: 90px;
        }

        .profile-summary {
            text-align: center;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .avatar-wrapper {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 16px;
            cursor: pointer;
        }

        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25);
            overflow: hidden;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
            color: white;
            font-size: 11px;
            font-weight: 600;
            gap: 4px;
        }

        .avatar-wrapper:hover .avatar-overlay { opacity: 1; }
        .avatar-overlay i { font-size: 22px; }

        .profile-summary h2 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .profile-summary p {
            font-size: 13px;
            color: var(--text-muted);
        }

        .settings-nav {
            list-style: none;
        }

        .settings-nav li { margin-bottom: 4px; }

        .settings-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .settings-nav a:hover, .settings-nav a.active {
            background: #f8fafc;
            color: var(--primary);
        }

        .settings-nav a.danger { color: var(--danger); }
        .settings-nav a.danger:hover { background: #fee2e2; }
        .settings-nav i { font-size: 20px; }

        .settings-panel {
            background: white;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .section-title i { color: var(--primary); font-size: 22px; }

        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
        }

        .stat-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--border);
        }

        .stat-card h4 {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            background: #f8fafc;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        .form-control:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .upload-toast {
            display: none;
            align-items: center;
            gap: 10px;
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            margin-top: 16px;
        }

        .upload-toast.error { background: #fef2f2; color: var(--danger); border-color: #fecaca; }

        .page-top {
            background: white;
            padding: 32px 24px;
            border-bottom: 1px solid var(--border);
            text-align: center;
        }
        .page-top h1 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -1px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <i class="ph-fill ph-map-pin-line"></i>
                <span>MyFamalicão</span>
            </a>
            <div class="nav-links">
                <a href="index">Início</a>
                <a href="sobre">Sobre a PAP</a>
                <a href="destaques">Destaques</a>
                <a href="comunidade">Comunidade</a>
                <a href="map">Mapa</a>
                <a href="meus_locais">Meus Locais</a>
            </div>
            <div class="nav-auth" style="display: flex; gap: 12px; align-items: center;">
                <a href="settings" class="user-greeting" style="font-weight: 600;"><i class="ph-bold ph-user-circle" style="font-size: 18px; vertical-align: middle;"></i> <?php echo htmlspecialchars($user['username']); ?></a>
                <a href="logout" class="btn btn-danger-sm" title="Sair"><i class="ph-bold ph-sign-out"></i></a>
            </div>
        </div>
    </nav>

    <div class="page-top">
        <h1>Definições da Conta</h1>
        <p style="color: var(--text-muted); margin-top: 6px;">Faz a gestão do teu perfil e preferências.</p>
    </div>

    <div class="settings-container">

        <!-- Sidebar -->
        <aside class="settings-sidebar">
            <div class="profile-summary">
                <!-- Avatar clicável -->
                <div class="avatar-wrapper" onclick="document.getElementById('avatar-input').click()">
                    <div class="avatar" id="avatarDisplay">
                        <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>?v=<?php echo time(); ?>" alt="Avatar">
                        <?php
else: ?>
                            <?php echo $initials; ?>
                        <?php
endif; ?>
                    </div>
                    <div class="avatar-overlay">
                        <i class="ph-bold ph-camera"></i>
                        Alterar
                    </div>
                </div>
                <!-- Hidden file input -->
                <input type="file" id="avatar-input" accept="image/*" style="display:none" onchange="uploadAvatar(this)">

                <h2><?php echo htmlspecialchars($display_name); ?></h2>
                <p style="font-style: italic;">@<?php echo htmlspecialchars($user['username']); ?></p>
                <p style="margin-top: 4px;">Membro desde <?php echo $join_date; ?></p>
            </div>

            <ul class="settings-nav">
                <li><a href="#perfil" class="active"><i class="ph-bold ph-identification-card"></i> O Meu Perfil</a></li>
                <li><a href="#atividade"><i class="ph-bold ph-chart-bar"></i> A Minha Atividade</a></li>
                <li style="margin-top: 28px;"><a href="logout" class="danger"><i class="ph-bold ph-sign-out"></i> Terminar Sessão</a></li>
            </ul>
        </aside>

        <!-- Conteúdo -->
        <main>

            <!-- Perfil -->
            <div class="settings-panel" id="perfil">
                <h3 class="section-title"><i class="ph-fill ph-identification-card"></i> Detalhes do Perfil</h3>
                
                <!-- Upload Toast -->
                <div class="upload-toast" id="uploadToast"></div>

                <form onsubmit="return false;">
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" class="form-control" id="fullNameInput" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" placeholder="O teu nome completo...">
                    </div>
                    <div class="form-group">
                        <label>Nome de Utilizador</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">O nome de utilizador não pode ser alterado.</small>
                    </div>
                    <div class="form-group">
                        <label>Idioma Preferido</label>
                        <select id="languageInput" class="form-control">
                            <option value="pt" <?php echo($user['language'] == 'pt') ? 'selected' : ''; ?>>Português</option>
                            <option value="en" <?php echo($user['language'] == 'en') ? 'selected' : ''; ?>>English (Inglês)</option>
                            <option value="fr" <?php echo($user['language'] == 'fr') ? 'selected' : ''; ?>>Français (Francês)</option>
                            <option value="es" <?php echo($user['language'] == 'es') ? 'selected' : ''; ?>>Español (Espanhol)</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="saveProfile()" style="width: 100%;">
                        <i class="ph-bold ph-floppy-disk"></i> Guardar Alterações
                    </button>
                </form>
            </div>

            <!-- Atividade -->
            <div class="settings-panel" id="atividade">
                <h3 class="section-title"><i class="ph-fill ph-chart-pie-slice"></i> A Minha Atividade</h3>
                <div class="stat-cards">
                    <div class="stat-card">
                        <h4>Locais Criados</h4>
                        <div class="value"><?php echo $poi_count; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>Distintivo</h4>
                        <div class="value" style="font-size: 20px; padding-top: 4px;"><?php echo $badge; ?></div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> MyFamalicão. PAP de Rodrigo de Frutuoso.</p>
    </footer>

    <script>
        // Upload avatar
        function uploadAvatar(input) {
            const file = input.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append("avatar", file);

            const toast = document.getElementById("uploadToast");
            toast.style.display = "flex";
            toast.textContent = "A carregar...";
            toast.className = "upload-toast";

            fetch("api_upload_avatar.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === "success") {
                    toast.textContent = "✅ Foto de perfil atualizada!";
                    // Update avatar preview
                    const avatarDiv = document.getElementById("avatarDisplay");
                    avatarDiv.innerHTML = `<img src="${data.path}?v=${Date.now()}" alt="Avatar">`;
                } else {
                    toast.className = "upload-toast error";
                    toast.textContent = "❌ Erro: " + data.message;
                }
                setTimeout(() => { toast.style.display = "none"; }, 4000);
            });
        }

        // Save profile (full name & language)
        function saveProfile() {
            const fullName = document.getElementById("fullNameInput").value;
            const language = document.getElementById("languageInput").value;
            const toast = document.getElementById("uploadToast");

            fetch("api_update_profile.php", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({ 
                    full_name: fullName,
                    language: language 
                })
            })
            .then(res => res.json())
            .then(data => {
                toast.style.display = "flex";
                if(data.status === "success") {
                    toast.className = "upload-toast";
                    toast.textContent = "✅ Perfil atualizado com sucesso!";
                    
                    // Set translation cookie instantly
                    if (language !== 'pt') {
                        const langPath = '/pt/' + language;
                        document.cookie = "googtrans=" + langPath + "; path=/";
                        document.cookie = "googtrans=" + langPath + "; domain=" + document.domain + "; path=/";
                    } else {
                        document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                        document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=" + document.domain + "; path=/;";
                    }
                    
                    // Reload after a short delay to apply translation across the UI
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    toast.className = "upload-toast error";
                    toast.textContent = "❌ Erro: " + data.message;
                }
                setTimeout(() => { toast.style.display = "none"; }, 4000);
            });
        }
    </script>
    <script src="ui_notifications.js"></script>
    <?php include "translation_footer.php"; ?>
</body>
</html>
