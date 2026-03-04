<?php
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: map");
    exit;
}

require_once "db_connect.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Introduz o teu nome de utilizador.";
    }
    else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Introduz a tua palavra-passe.";
    }
    else {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password FROM users WHERE username = :username";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);

            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row["id"];
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        if (password_verify($password, $hashed_password)) {
                            session_start();

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            header("location: map");
                        }
                        else {
                            $login_err = "Nome de utilizador ou palavra-passe incorretos.";
                        }
                    }
                }
                else {
                    $login_err = "Nome de utilizador ou palavra-passe incorretos.";
                }
            }
            else {
                echo "Ops! Algo deu errado. Tenta novamente mais tarde.";
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
    <title>Login - MyFamalicão</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
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
            <h2>Bem-vindo de volta!</h2>
            <p>Faz login para acederes ao Roteiro Interativo.</p>


            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Utilizador</label>
                    <div class="input-wrapper <?php echo(!empty($username_err)) ? 'has-error' : ''; ?>">
                        <i class="ph ph-user"></i>
                        <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" placeholder="O teu utilizador...">
                    </div>
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>    
                <div class="form-group">
                    <label>Palavra-passe</label>
                    <div class="input-wrapper <?php echo(!empty($password_err)) ? 'has-error' : ''; ?>">
                        <i class="ph ph-lock-key"></i>
                        <input type="password" name="password" class="form-control" placeholder="A tua palavra-passe...">
                    </div>
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width:100%">Entrar Seguro <i class="ph-bold ph-arrow-right"></i></button>
                </div>
                <p class="auth-link">Não tens conta? <a href="register">Regista-te agora</a>.</p>
            </form>
        </div>
    </div>
    <script src="ui_notifications.js"></script>
    <?php if (!empty($login_err)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            myFama.alert("Erro de Login", "<?php echo $login_err; ?>", "error");
        });
    </script>
    <?php
endif; ?>
</body>
</html>
