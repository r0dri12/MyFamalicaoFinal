<?php
require_once "db_connect.php";

$full_name = $username = $password = $confirm_password = "";
$full_name_err = $username_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Full Name Validation
    if (empty(trim($_POST["full_name"]))) {
        $full_name_err = "Introduz o teu nome completo.";
    }
    else {
        $full_name = trim($_POST["full_name"]);
    }

    // Username Validation
    if (empty(trim($_POST["username"]))) {
        $username_err = "Introduz um nome de utilizador.";
    }
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        $username_err = "O nome de utilizador só pode conter letras, números e underscores.";
    }
    else {
        $sql = "SELECT id FROM users WHERE username = :username";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);

            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $username_err = "Este nome de utilizador já está em uso.";
                }
                else {
                    $username = trim($_POST["username"]);
                }
            }
            else {
                echo "Ops! Algo deu errado.";
            }
            unset($stmt);
        }
    }

    // Password Validation
    if (empty(trim($_POST["password"]))) {
        $password_err = "Introduz uma palavra-passe.";
    }
    elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "A palavra-passe deve ter pelo menos 6 caracteres.";
    }
    else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Por favor, confirma a palavra-passe.";
    }
    else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "As palavras-passe não coincidem.";
        }
    }

    if (empty($full_name_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (full_name, username, password) VALUES (:full_name, :username, :password)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindParam(":full_name", $param_full_name, PDO::PARAM_STR);
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);

            $param_full_name = $full_name;
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);

            if ($stmt->execute()) {
                header("location: login");
            }
            else {
                echo "Ops! Algo deu errado. Tenta mais tarde.";
            }
            unset($stmt);
        }
    }
    unset($conn);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Registo - MyFamalicão</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="auth_style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="logo">
                <i class="ph-fill ph-map-pin-line"></i>
                <h1>MyFamalicão</h1>
            </div>
            <h2>Cria a tua conta</h2>
            <p>Junta-te a nós e começa a planear os teus roteiros.</p>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <div class="input-wrapper <?php echo(!empty($full_name_err)) ? 'has-error' : ''; ?>">
                        <i class="ph ph-identification-badge"></i>
                        <input type="text" name="full_name" class="form-control" value="<?php echo $full_name; ?>" placeholder="O teu nome completo...">
                    </div>
                    <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Nome de Utilizador</label>
                    <div class="input-wrapper <?php echo(!empty($username_err)) ? 'has-error' : ''; ?>">
                        <i class="ph ph-user"></i>
                        <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" placeholder="Escolhe um utilizador...">
                    </div>
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>    
                <div class="form-group">
                    <label>Palavra-passe</label>
                    <div class="input-wrapper <?php echo(!empty($password_err)) ? 'has-error' : ''; ?>">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo de 6 caracteres...">
                    </div>
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Confirmar Palavra-passe</label>
                    <div class="input-wrapper <?php echo(!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                        <i class="ph ph-check-circle"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Repete a palavra-passe...">
                    </div>
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width:100%">Criar Conta <i class="ph-bold ph-arrow-right"></i></button>
                    <button type="reset" class="btn btn-secondary" style="width:100%; margin-top: 10px;">Limpar</button>
                </div>
                <p class="auth-link">Já tens uma conta? <a href="login">Faz Login</a>.</p>
            </form>
        </div>
    </div>
</body>
</html>
