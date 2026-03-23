<?php
require_once "db_connect.php";
session_start();

$msg = "";
$status = "";
$username = $_GET['u'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_post = trim($_POST["username"]);
    $code_post = trim($_POST["code"]);

    if (empty($username_post) || empty($code_post)) {
        $status = "error";
        $msg = "Preenche o utilizador e o código.";
        $username = $username_post;
    }
    else {
        // Verifica na BD
        $sql = "SELECT id, is_verified FROM users WHERE username = :username AND verification_code = :code LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindParam(":username", $username_post, PDO::PARAM_STR);
            $stmt->bindParam(":code", $code_post, PDO::PARAM_STR);
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $row = $stmt->fetch();
                    if ($row['is_verified'] == 0) {
                        // Update
                        $update_sql = "UPDATE users SET is_verified = 1 WHERE id = :id";
                        if ($update_stmt = $conn->prepare($update_sql)) {
                            $update_stmt->bindParam(":id", $row['id'], PDO::PARAM_INT);
                            if ($update_stmt->execute()) {
                                $status = "success";
                                $msg = "Conta verificada com sucesso! Já podes fazer login.";
                            }
                            else {
                                $status = "error";
                                $msg = "Erro ao atualizar a conta.";
                            }
                            unset($update_stmt);
                        }
                    }
                    else {
                        $status = "warning";
                        $msg = "Esta conta já está verificada.";
                    }
                }
                else {
                    $status = "error";
                    $msg = "Código incorreto ou utilizador não encontrado.";
                    $username = $username_post;
                }
            }
            unset($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Verificação de Conta - MyFamalicão</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="auth_style.css">
    <style>
        .code-input {
            font-size: 1.5rem !important;
            letter-spacing: 5px !important;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="logo">
                <i class="ph-fill ph-check-circle" style="color: var(--primary);"></i>
                <h1>Verificação</h1>
            </div>
            <p style="text-align:center; margin-bottom: 20px;">Insere o código de 6 dígitos que foi enviado para o teu email.</p>
            
            <?php if (!empty($msg)): ?>
            <div style="margin-bottom: 20px; padding: 15px; border-radius: 8px; text-align:center; background: <?php echo $status == 'success' ? '#e6f4ea' : ($status == 'warning' ? '#fef7e0' : '#fce8e6'); ?>;">
                <p style="color: <?php echo $status == 'success' ? '#137333' : ($status == 'warning' ? '#b06000' : '#c5221f'); ?>; font-weight: 500; margin: 0;">
                    <?php echo $msg; ?>
                </p>
            </div>
            <?php
endif; ?>

            <?php if ($status != 'success'): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Utilizador</label>
                    <div class="input-wrapper">
                        <i class="ph ph-user"></i>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" <?php echo !empty($username) ? 'readonly style="background:#f1f3f4;"' : ''; ?> placeholder="O teu utilizador">
                    </div>
                </div>    
                <div class="form-group">
                    <label>Código de Verificação</label>
                    <div class="input-wrapper">
                        <i class="ph ph-password"></i>
                        <input type="text" name="code" class="form-control code-input" placeholder="000000" maxlength="6" autocomplete="off">
                    </div>
                </div>
                <div class="form-group" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="width:100%">Verificar Código <i class="ph-bold ph-arrow-right"></i></button>
                </div>
                <p class="auth-link" style="text-align:center; margin-top: 15px;"><a href="login">Voltar ao Login</a></p>
            </form>
            <?php
else: ?>
            <a href="login" class="btn btn-primary" style="display: block; text-align:center; width: 100%; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: 500;">
                Ir para o Login <i class="ph-bold ph-arrow-right"></i>
            </a>
            <?php
endif; ?>
        </div>
    </div>
</body>
</html>
