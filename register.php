<?php
require_once "db_connect.php";

$full_name = $username = $password = $confirm_password = $language = $email = "";
$full_name_err = $username_err = $password_err = $confirm_password_err = $email_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Full Name Validation
    if (empty(trim($_POST["full_name"]))) {
        $full_name_err = "Introduz o teu nome completo.";
    }
    else {
        $full_name = trim($_POST["full_name"]);
    }

    // Email Validation
    if (empty(trim($_POST["email"]))) {
        $email_err = "Introduz o teu endereço de email.";
    }
    elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Introduz um endereço de email válido.";
    }
    else {
        $sql = "SELECT id FROM users WHERE email = :email";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $param_email = trim($_POST["email"]);
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $email_err = "Este email já está registado.";
                }
                else {
                    $email = trim($_POST["email"]);
                }
            }
            else {
                echo "Ops! Algo deu errado com a verificação de email.";
            }
            unset($stmt);
        }
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

    // Language Validation
    if (empty(trim($_POST["language"]))) {
        $language = "pt";
    }
    else {
        $language = trim($_POST["language"]);
    }

    if (empty($full_name_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err)) {
        $sql = "INSERT INTO users (full_name, username, password, email, verification_code, is_verified, language) VALUES (:full_name, :username, :password, :email, :verification_code, 0, :language)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindParam(":full_name", $param_full_name, PDO::PARAM_STR);
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":verification_code", $param_vcode, PDO::PARAM_STR);
            $stmt->bindParam(":language", $param_language, PDO::PARAM_STR);

            $param_full_name = $full_name;
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_email = $email;
            $param_vcode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $param_language = $language;

            if ($stmt->execute()) {
                require_once "includes/mailer.php";
                sendVerificationEmail($param_email, $param_vcode);

                header("location: verify?u=" . urlencode($username));
                exit;
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
    <?php include "translation_header.php"; ?>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Registo - MyFamalicão</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="auth_style.css">
    <link rel="stylesheet" href="ui_notifications.css">
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
                    <label>E-mail</label>
                    <div class="input-wrapper <?php echo(!empty($email_err)) ? 'has-error' : ''; ?>">
                        <i class="ph ph-envelope"></i>
                        <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" placeholder="O teu email...">
                    </div>
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
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
                    <label>Idioma Preferido</label>
                    <div class="input-wrapper">
                        <i class="ph ph-translate"></i>
                        <select name="language" class="form-control">
                            <option value="pt" selected>Português</option>
                            <option value="en">English (Inglês)</option>
                            <option value="fr">Français (Francês)</option>
                            <option value="es">Español (Espanhol)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width:100%">Criar Conta <i class="ph-bold ph-arrow-right"></i></button>
                    <button type="reset" class="btn btn-secondary" style="width:100%; margin-top: 10px;">Limpar</button>
                </div>
                <p class="auth-link">Já tens uma conta? <a href="login">Faz Login</a>.</p>
            </form>
        </div>
    </div>
    <script src="ui_notifications.js"></script>
    <?php include "translation_footer.php"; ?>
    <?php
$any_err = "";
if (!empty($username_err))
    $any_err = $username_err;
elseif (!empty($email_err))
    $any_err = $email_err;
elseif (!empty($password_err))
    $any_err = $password_err;
elseif (!empty($confirm_password_err))
    $any_err = $confirm_password_err;
elseif (!empty($full_name_err))
    $any_err = $full_name_err;

if (!empty($any_err)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            myFama.alert("Erro no Registo", "<?php echo $any_err; ?>", "error");
        });
    </script>
    <?php
endif; ?>
</body>
</html>
