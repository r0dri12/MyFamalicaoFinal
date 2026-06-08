<?php
session_start();
require_once "db_connect.php";

header('Content-Type: application/json');

// Check if logged in and is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Não autenticado"]);
    exit;
}

// Fetch is_admin from DB for security
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION["id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['is_admin'] != 1) {
    echo json_encode(["status" => "error", "message" => "Acesso negado"]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

try {
    if ($method == "GET") {
        $type = $_GET['type'] ?? 'all';

        $data = [];
        if ($type == 'all' || $type == 'users') {
            $stmt = $conn->query("SELECT id, username, full_name, is_admin, created_at FROM users ORDER BY created_at DESC");
            $data['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if ($type == 'all' || $type == 'routes') {
            $stmt = $conn->query("SELECT r.*, u.username as owner_name FROM saved_routes r JOIN users u ON r.user_id = u.id WHERE r.is_public = 1 ORDER BY r.created_at DESC");
            $data['routes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if ($type == 'all' || $type == 'pois') {
            $stmt = $conn->query("SELECT p.*, u.username as owner_name FROM custom_pois p JOIN users u ON p.user_id = u.id WHERE p.is_public = 1 ORDER BY p.id DESC");
            $data['pois'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(["status" => "success", "data" => $data]);
    } 
    elseif ($method == "POST") {
        $input = json_decode(file_get_contents("php://input"));
        $action = $input->action ?? '';

        if ($action == 'delete_user') {
            $id = $input->id;
            if ($id == $_SESSION["id"]) {
                echo json_encode(["status" => "error", "message" => "Não podes eliminar a tua própria conta"]);
                exit;
            }
            $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(["status" => "success", "message" => "Utilizador eliminado"]);
        } 
        elseif ($action == 'toggle_admin') {
            $id = $input->id;
            if ($id == $_SESSION["id"]) {
                echo json_encode(["status" => "error", "message" => "Não podes tirar o teu próprio acesso admin"]);
                exit;
            }
            $stmt = $conn->prepare("UPDATE users SET is_admin = 1 - is_admin WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(["status" => "success", "message" => "Acesso alterado"]);
        }
        elseif ($action == 'delete_route') {
            $id = $input->id;
            $stmt = $conn->prepare("DELETE FROM saved_routes WHERE id = :id AND is_public = 1");
            $stmt->execute(['id' => $id]);
            echo json_encode(["status" => "success", "message" => "Rota eliminada"]);
        }
        elseif ($action == 'delete_poi') {
            $id = $input->id;
            $stmt = $conn->prepare("DELETE FROM custom_pois WHERE id = :id AND is_public = 1");
            $stmt->execute(['id' => $id]);
            echo json_encode(["status" => "success", "message" => "Ponto eliminado"]);
        }
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro de BD: " . $e->getMessage()]);
}
?>
