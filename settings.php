<?php
session_start();
require_once "db_connect.php";

// Redirect if not logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Get user data
$sql = "SELECT username, created_at FROM users WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Count custom POIs
$sql_count = "SELECT COUNT(*) as total FROM custom_pois WHERE user_id = :id";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bindParam(":id", $user_id, PDO::PARAM_INT);
$stmt_count->execute();
$poi_count = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

// Set default profile picture based on username
$initials = strtoupper(substr($user['username'], 0, 2));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Definições - MyFamalicão</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <link rel="stylesheet" href="main_style.css">
    <style>
        .settings-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 24px;
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
        }

        .profile-summary {
            text-align: center;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 24px;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            margin: 0 auto 16px;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.2);
        }

        .profile-summary h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .profile-summary p {
            font-size: 13px;
            color: var(--text-muted);
        }

        .settings-nav {
            list-style: none;
        }

        .settings-nav li {
            margin-bottom: 8px;
        }

        .settings-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
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

        .settings-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid var(--border);
        }

        .settings-section-title {
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        
        .settings-section-title i { color: var(--primary); }

        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: #f8fafc;
            padding: 24px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--border);
        }

        .stat-card h4 {
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-card .value {
            font-size: 32px;
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
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            background: #f8fafc;
        }

        .form-control:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .page-header {
            background: white;
            padding: 40px 24px;
            border-bottom: 1px solid var(--border);
            text-align: center;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -1px;
        }

    </style>
</head>
<body>

    <!-- Navegação -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <i class="ph-fill ph-map-pin-line"></i>
                <span>MyFamalicão</span>
            </a>
            <div class="nav-links">
                <a href="index.php">Início</a>
                <a href="sobre.php">Sobre a PAP</a>
                <a href="destaques.php">Destaques</a>
                <a href="map.php">Mapa</a>
            </div>
            <div class="nav-auth" style="display: flex; gap: 16px; align-items: center;">
                <span class="user-greeting">Olá, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="settings.php" class="btn btn-outline" style="padding: 10px 14px;"><i class="ph-bold ph-gear"></i> Definições</a>
                <a href="logout.php" class="btn btn-danger-sm" title="Sair"><i class="ph-bold ph-sign-out"></i></a>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <h1>Definições da Conta</h1>
        <p style="color: var(--text-muted); margin-top: 8px;">Faz a gestão do teu perfil e preferências</p>
    </div>

    <div class="settings-container">
        
        <!-- Menu Lateral Lateral -->
        <aside class="settings-sidebar">
            <div class="profile-summary">
                <div class="avatar"><?php echo $initials; ?></div>
                <h2><?php echo htmlspecialchars($_SESSION["username"]); ?></h2>
                <p>Membro desde <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
            </div>
            
            <ul class="settings-nav">
                <li><a href="#perfil" class="active"><i class="ph-bold ph-user"></i> O Meu Perfil</a></li>
                <li><a href="#estatisticas"><i class="ph-bold ph-chart-bar"></i> A Minha Atividade</a></li>
                <li><a href="#seguranca"><i class="ph-bold ph-lock-key"></i> Segurança (Em breve)</a></li>
                <li style="margin-top: 32px;"><a href="logout.php" class="danger"><i class="ph-bold ph-sign-out"></i> Terminar Sessão</a></li>
            </ul>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="settings-content">
            
            <h3 class="settings-section-title" id="estatisticas"><i class="ph-fill ph-chart-pie-slice"></i> A Minha Atividade</h3>
            <div class="stat-cards">
                <div class="stat-card">
                    <h4>Locais Criados</h4>
                    <div class="value"><?php echo $poi_count; ?></div>
                </div>
                <div class="stat-card">
                    <h4>Distintivo</h4>
                    <div class="value" style="font-size: 24px; margin-top: 8px;">
                        <?php 
                            if($poi_count >= 10) echo "🏆 Guia Local";
                            elseif($poi_count >= 5) echo "🌟 Explorador";
                            elseif($poi_count > 0) echo "🎒 Turista";
                            else echo "🌱 Novato";
                        ?>
                    </div>
                </div>
            </div>

            <h3 class="settings-section-title" id="perfil" style="margin-top: 48px;"><i class="ph-fill ph-identification-card"></i> Detalhes do Perfil</h3>
            
            <form>
                <div class="form-group">
                    <label>Nome de Utilizador</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user["username"]); ?>" disabled>
                    <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">O nome de utilizador não pode ser alterado após o registo.</small>
                </div>

                <div class="form-group">
                    <label>Email (Opcional)</label>
                    <input type="email" class="form-control" placeholder="Adicionar um email à tua conta...">
                </div>

                <button type="button" class="btn btn-primary" onclick="alert('Definições atualizadas com sucesso! (Demonstração)')">
                    Guardar Alterações
                </button>
            </form>

        </main>
    </div>

    <!-- Footer -->
    <footer style="margin-top: 60px;">
        <p>&copy; <?php echo date("Y"); ?> MyFamalicão. PAP de Rodrigo de Frutuoso.</p>
    </footer>

    <script>
        // Interatividade simples do menu de settings
        const navLinks = document.querySelectorAll('.settings-nav a:not(.danger)');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
