<?php
session_start();
require_once "db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$username = $_SESSION["username"];

// Search query logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = "user_id = :user_id";
if (!empty($search)) {
    $where_clause .= " AND (name LIKE :search OR description LIKE :search OR type LIKE :search)";
}

// Get user's POIs
$sql = "SELECT id, name, description, latitude, longitude, type, image FROM custom_pois WHERE " . $where_clause . " ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
if (!empty($search)) {
    $search_param = "%{$search}%";
    $stmt->bindParam(":search", $search_param, PDO::PARAM_STR);
}
$stmt->execute();
$pois = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'pt'; ?>">
<head>
    <?php include "translation_header.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Os Meus Locais - MyFamalicão</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="main_style.css">
    <link rel="stylesheet" href="ui_notifications.css">
    <style>
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
        .container {
            max-width: 1200px;
            margin: 40px auto 80px;
            padding: 0 24px;
        }

        /* Controls Area */
        .controls-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .search-box {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }
        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 20px;
        }
        .search-input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            background: white;
            transition: all 0.2s;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        /* POI Grid */
        .poi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        .poi-card {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }
        .poi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.06);
        }

        .poi-img-wrapper {
            position: relative;
            height: 180px;
            background: #f1f5f9;
        }
        .poi-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .poi-tag {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(4px);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .poi-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .poi-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .poi-desc {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }
        
        .poi-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }
        .poi-coords {
            font-size: 12px;
            color: var(--text-muted);
            font-family: monospace;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .poi-actions {
            display: flex;
            gap: 8px;
        }
        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 16px;
        }
        .btn-edit { background: #eff6ff; color: var(--primary); }
        .btn-edit:hover { background: var(--primary); color: white; }
        .btn-delete { background: #fef2f2; color: var(--danger); }
        .btn-delete:hover { background: var(--danger); color: white; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            border: 1px dashed var(--border);
            grid-column: 1 / -1;
        }
        .empty-state i {
            font-size: 48px;
            color: var(--text-light);
            margin-bottom: 16px;
        }
        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .empty-state p {
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h2 {
            font-size: 18px;
            font-weight: 700;
            display: flex; margin: 0;
            align-items: center;
            gap: 8px;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
        }
        .modal-close:hover { background: #f1f5f9; color: var(--text-main); }
        
        .modal-body {
            padding: 24px;
            overflow-y: auto;
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
            font-family: inherit;
            font-size: 14px;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        
        .image-preview {
            width: 100%;
            height: 160px;
            border-radius: 10px;
            border: 1px dashed var(--border);
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 12px;
            position: relative;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }
        .image-preview .placeholder {
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .image-preview .placeholder i { font-size: 32px; }
        
        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8fafc;
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
                <a href="meus_locais" class="active">Meus Locais</a>
            </div>
            <div class="nav-auth" style="display: flex; gap: 12px; align-items: center;">
                <a href="settings" class="user-greeting" style="font-weight: 600;"><i class="ph-bold ph-user-circle" style="font-size: 18px; vertical-align: middle;"></i> <?php echo htmlspecialchars($username); ?></a>
                <a href="map" class="btn btn-primary-sm">Mapa</a>
                <a href="logout" class="btn btn-danger-sm" title="Sair"><i class="ph-bold ph-sign-out"></i></a>
            </div>
        </div>
    </nav>

    <div class="page-top">
        <h1>Os Meus Locais</h1>
        <p style="color: var(--text-muted); margin-top: 6px;">Gere os pontos de interesse que criaste no mapa.</p>
    </div>

    <div class="container">
        <div class="controls-area">
            <form action="meus_locais.php" method="GET" class="search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" class="search-input" placeholder="Pesquisar por nome, tipo ou descrição..." value="<?php echo htmlspecialchars($search); ?>">
            </form>
            <a href="map" class="btn btn-primary"><i class="ph-bold ph-plus"></i> Novo Local no Mapa</a>
        </div>

        <div class="poi-grid">
            <?php if (count($pois) > 0): ?>
                <?php foreach ($pois as $poi): ?>
                    <div class="poi-card" id="poi-card-<?php echo $poi['id']; ?>">
                        <div class="poi-img-wrapper">
                            <span class="poi-tag"><?php echo htmlspecialchars($poi['type']); ?></span>
                            <img src="<?php echo htmlspecialchars($poi['image']); ?>" alt="Local" class="poi-img">
                        </div>
                        <div class="poi-content">
                            <h3 class="poi-title"><?php echo htmlspecialchars($poi['name']); ?></h3>
                            <p class="poi-desc"><?php echo nl2br(htmlspecialchars($poi['description'])); ?></p>
                        </div>
                        <div class="poi-footer">
                            <div class="poi-coords">
                                <i class="ph-bold ph-map-pin"></i> 
                                <?php echo substr($poi['latitude'], 0, 7) . ', ' . substr($poi['longitude'], 0, 7); ?>
                            </div>
                            <div class="poi-actions">
                                <button class="btn-icon btn-edit" title="Editar Local" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($poi)); ?>)">
                                    <i class="ph-bold ph-pencil-simple"></i>
                                </button>
                                <button class="btn-icon btn-delete" title="Eliminar Local" onclick="deletePoi(<?php echo $poi['id']; ?>)">
                                    <i class="ph-bold ph-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php
    endforeach; ?>
            <?php
else: ?>
                <div class="empty-state">
                    <i class="ph ph-map-trifold"></i>
                    <h3>Nenhum local encontrado</h3>
                    <p>Ainda não criaste nenhum ponto ou as pesquisas não retornaram resultados.</p>
                    <a href="map" class="btn btn-primary">Começar a Explorar</a>
                </div>
            <?php
endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="ph-fill ph-pencil-simple"></i> Editar Local</h2>
                <button class="modal-close" onclick="closeEditModal()"><i class="ph-bold ph-x"></i></button>
            </div>
            <div class="modal-body">
                <form id="editForm" onsubmit="return false;">
                    <input type="hidden" id="editId">
                    
                    <div class="form-group">
                        <label>Imagem do Local</label>
                        <div class="image-preview" onclick="document.getElementById('editImageInput').click()" style="cursor: pointer;">
                            <img id="editImagePreview" src="" alt="Preview">
                            <div class="placeholder" id="editImagePlaceholder">
                                <i class="ph ph-image"></i>
                                <span>Clica para alterar a foto</span>
                            </div>
                        </div>
                        <input type="file" id="editImageInput" accept="image/*" style="display:none" onchange="previewImage(this)">
                        <input type="hidden" id="editImageUrl"> <!-- Stores standard/existing url if not uploading new -->
                    </div>

                    <div class="form-group">
                        <label>Nome do Local</label>
                        <input type="text" class="form-control" id="editName" required autofocus>
                    </div>

                    <div class="form-group">
                        <label>Tipo de Local</label>
                        <select class="form-control" id="editType">
                            <option value="Restaurante">Restaurante</option>
                            <option value="Monumento">Monumento</option>
                            <option value="Natureza">Natureza</option>
                            <option value="Alojamento">Alojamento</option>
                            <option value="Miradouro">Miradouro</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea class="form-control" id="editDesc" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                <button class="btn btn-primary" onclick="savePoiChanges()">Guardar Alterações</button>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> MyFamalicão. PAP de Rodrigo de Frutuoso.</p>
    </footer>

    <script>
        // Modal functions
        function openEditModal(poi) {
            document.getElementById('editId').value = poi.id;
            document.getElementById('editName').value = poi.name;
            document.getElementById('editDesc').value = poi.description;
            
            // Set type if exists in dropdown, else Outro
            const typeSelect = document.getElementById('editType');
            let typeExists = Array.from(typeSelect.options).some(opt => opt.value === poi.type);
            typeSelect.value = typeExists ? poi.type : 'Outro';

            // Set Image
            const imgPreview = document.getElementById('editImagePreview');
            const placeholder = document.getElementById('editImagePlaceholder');
            
            if(poi.image) {
                imgPreview.src = poi.image;
                imgPreview.style.display = 'block';
                placeholder.style.display = 'none';
                document.getElementById('editImageUrl').value = poi.image;
            } else {
                imgPreview.style.display = 'none';
                placeholder.style.display = 'flex';
            }
            
            // Clear file input
            document.getElementById('editImageInput').value = '';

            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Image Preview
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgPreview = document.getElementById('editImagePreview');
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                    document.getElementById('editImagePlaceholder').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Save Changes Let's create an API endpoint for it
        function savePoiChanges() {
            const id = document.getElementById('editId').value;
            const name = document.getElementById('editName').value;
            const type = document.getElementById('editType').value;
            const desc = document.getElementById('editDesc').value;
            const fileInput = document.getElementById('editImageInput');

        if(!name.trim()) { 
            myFama.toast("O nome é obrigatório!", "error"); 
            return; 
        }

            const formData = new FormData();
            formData.append('action', 'edit');
            formData.append('id', id);
            formData.append('name', name);
            formData.append('type', type);
            formData.append('description', desc);
            
            if(fileInput.files.length > 0) {
                formData.append('image', fileInput.files[0]);
            }

            fetch('api_manage_poi.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    myFama.toast("Local atualizado com sucesso!", "success");
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    myFama.alert("Erro ao Editar", data.message, "error");
                }
            })
            .catch(err => {
                console.error(err);
                myFama.toast("Erro na comunicação com o servidor.", "error");
            });
        }

        // Delete POI
        async function deletePoi(id) {
            const confirmed = await myFama.confirm(
                "Eliminar Local", 
                "Tens a certeza que queres eliminar este local? Esta ação não pode ser desfeita.",
                { isDanger: true, confirmText: "Sim, Eliminar" }
            );

            if(confirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('api_manage_poi.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        myFama.toast("Local removido com sucesso!", "info");
                        document.getElementById('poi-card-' + id).remove();
                        if(document.querySelectorAll('.poi-card').length === 0) {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } else {
                        myFama.alert("Erro ao Eliminar", data.message, "error");
                    }
                })
                .catch(err => {
                    console.error(err);
                    myFama.toast("Erro na comunicação com o servidor.", "error");
                });
            }
        }
    </script>
    <script src="ui_notifications.js"></script>
    <?php include "translation_footer.php"; ?>
</body>
</html>
