<?php
session_start();
require_once "db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Não autenticado"]);
    exit;
}

$user_id = $_SESSION["id"];

try {
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Obter todas as conquistas do utilizador logado
        $sql = "SELECT badge_key, unlocked_at FROM user_badges WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $badges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "badges" => $badges]);
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $json_input = file_get_contents("php://input");
        $data = json_decode($json_input);

        if (!$data || !isset($data->badge_key)) {
            echo json_encode(["status" => "error", "message" => "Dados inválidos"]);
            exit;
        }

        $badge_key = trim($data->badge_key);

        if (empty($badge_key)) {
            echo json_encode(["status" => "error", "message" => "Chave de conquista inválida"]);
            exit;
        }

        // Tentar inserir (se já existir, a UNIQUE KEY evita duplicação)
        $sql = "INSERT IGNORE INTO user_badges (user_id, badge_key) VALUES (:user_id, :badge_key)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'badge_key' => $badge_key
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Nova conquista desbloqueada! 🎉", "unlocked" => true]);
        } else {
            echo json_encode(["status" => "success", "message" => "Conquista já estava desbloqueada.", "unlocked" => false]);
        }
        exit;
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro de Base de Dados: " . $e->getMessage()]);
}
?>
