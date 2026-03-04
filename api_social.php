<?php
session_start();
require_once "db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Não autenticado"]);
    exit;
}

$user_id = $_SESSION["id"];
$data = json_decode(file_get_contents("php://input"));

try {
    // GET: Listar todos os pontos públicos com estatísticas sociais
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $sql = "SELECT p.*, u.username as owner_name,
                (SELECT COUNT(*) FROM poi_likes WHERE poi_id = p.id) as likes_count,
                (SELECT COUNT(*) FROM poi_likes WHERE poi_id = p.id AND user_id = :current_user_id) as user_liked,
                (SELECT COUNT(*) FROM poi_comments WHERE poi_id = p.id) as comments_count
                FROM custom_pois p
                JOIN users u ON p.user_id = u.id
                WHERE p.is_public = 1
                ORDER BY p.id DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":current_user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $pois = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "pois" => $pois]);
    }

    // POST: Ações sociais
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $action = $data->action ?? '';
        $poi_id_raw = $data->poi_id ?? '';
        $poi_id = (int)str_replace('custom_', '', $poi_id_raw);

        if (empty($poi_id)) {
            echo json_encode(["status" => "error", "message" => "ID do ponto inválido"]);
            exit;
        }

        if ($action == 'like') {
            // Verificar se já deu like
            $check_sql = "SELECT id FROM poi_likes WHERE user_id = :user_id AND poi_id = :poi_id";
            $stmt = $conn->prepare($check_sql);
            $stmt->execute(['user_id' => $user_id, 'poi_id' => $poi_id]);

            if ($stmt->rowCount() > 0) {
                // Remover like
                $sql = "DELETE FROM poi_likes WHERE user_id = :user_id AND poi_id = :poi_id";
                $message = "Like removido";
            }
            else {
                // Adicionar like
                $sql = "INSERT INTO poi_likes (user_id, poi_id) VALUES (:user_id, :poi_id)";
                $message = "Like adicionado";
            }

            $stmt = $conn->prepare($sql);
            if ($stmt->execute(['user_id' => $user_id, 'poi_id' => $poi_id])) {
                echo json_encode(["status" => "success", "message" => $message]);
            }
            else {
                echo json_encode(["status" => "error", "message" => "Erro ao processar like"]);
            }
        }

        elseif ($action == 'comment') {
            $comment = $data->comment ?? '';
            if (empty(trim($comment))) {
                echo json_encode(["status" => "error", "message" => "Comentário vazio"]);
                exit;
            }

            $sql = "INSERT INTO poi_comments (user_id, poi_id, comment) VALUES (:user_id, :poi_id, :comment)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute(['user_id' => $user_id, 'poi_id' => $poi_id, 'comment' => $comment])) {
                echo json_encode(["status" => "success", "message" => "Comentário adicionado"]);
            }
            else {
                echo json_encode(["status" => "error", "message" => "Erro ao comentar"]);
            }
        }

        elseif ($action == 'get_comments') {
            $sql = "SELECT c.*, u.username 
                    FROM poi_comments c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.poi_id = :poi_id 
                    ORDER BY c.created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['poi_id' => $poi_id]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["status" => "success", "comments" => $comments]);
        }
    }
}
catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro de BD: " . $e->getMessage()]);
}

?>
