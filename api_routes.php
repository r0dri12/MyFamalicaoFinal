<?php
session_start();
require_once "db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Não autenticado"]);
    exit;
}

$user_id = $_SESSION["id"];
$method = $_SERVER["REQUEST_METHOD"];

try {
    if ($method == "POST") {
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        if (!$data || !isset($data->name) || !isset($data->items) || empty($data->items)) {
            echo json_encode(["status" => "error", "message" => "Dados inválidos"]);
            exit;
        }

        $conn->beginTransaction();

        $sql = "INSERT INTO saved_routes (user_id, route_name) VALUES (:user_id, :name)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'name' => $data->name]);
        $route_id = $conn->lastInsertId();

        $sql_item = "INSERT INTO route_items (route_id, poi_id, order_index) VALUES (:route_id, :poi_id, :idx)";
        $stmt_item = $conn->prepare($sql_item);

        foreach ($data->items as $index => $poi_id) {
            $stmt_item->execute([
                'route_id' => $route_id,
                'poi_id' => $poi_id,
                'idx' => $index
            ]);
        }

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Rota guardada com sucesso!"]);
    } 
    elseif ($method == "GET") {
        if (isset($_GET['id'])) {
            // Get single route details
            $route_id = (int)$_GET['id'];
            $sql = "SELECT poi_id FROM route_items WHERE route_id = :route_id ORDER BY order_index ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['route_id' => $route_id]);
            $items = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode(["status" => "success", "items" => $items]);
        } else {
            // List all routes
            $sql = "SELECT id, route_name, created_at FROM saved_routes WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(["status" => "success", "routes" => $routes]);
        }
    } 
    elseif ($method == "DELETE") {
        $json = file_get_contents("php://input");
        $data = json_decode($json);
        $route_id = $data->id ?? 0;

        if (!$route_id) {
            echo json_encode(["status" => "error", "message" => "ID inválido"]);
            exit;
        }

        $sql = "DELETE FROM saved_routes WHERE id = :id AND user_id = :user_id";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute(['id' => $route_id, 'user_id' => $user_id])) {
            echo json_encode(["status" => "success", "message" => "Rota eliminada"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erro ao eliminar"]);
        }
    }
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(["status" => "error", "message" => "Erro de BD: " . $e->getMessage()]);
}
?>
