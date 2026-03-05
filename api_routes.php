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

        $is_public = isset($data->is_public) ? (int)$data->is_public : 0;
        $description = isset($data->description) ? $data->description : null;

        $sql = "INSERT INTO saved_routes (user_id, route_name, is_public, description) VALUES (:user_id, :name, :is_public, :description)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id, 
            'name' => $data->name,
            'is_public' => $is_public,
            'description' => $description
        ]);
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
            // Check if route exists and is either public or owned by user
            $sql_check = "SELECT id FROM saved_routes WHERE id = :route_id AND (user_id = :user_id OR is_public = 1)";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->execute(['route_id' => $route_id, 'user_id' => $user_id]);
            
            if (!$stmt_check->fetch()) {
                echo json_encode(["status" => "error", "message" => "Rota não encontrada ou privada"]);
                exit;
            }

            $sql = "SELECT poi_id FROM route_items WHERE route_id = :route_id ORDER BY order_index ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['route_id' => $route_id]);
            $item_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $full_items = [];
            foreach ($item_ids as $p_id) {
                if (strpos($p_id, 'custom_') === 0) {
                    $real_id = (int)str_replace('custom_', '', $p_id);
                    $s = $conn->prepare("SELECT id, name, description, latitude as lat, longitude as lng, type, image FROM custom_pois WHERE id = :id");
                    $s->execute(['id' => $real_id]);
                    $details = $s->fetch(PDO::FETCH_ASSOC);
                    if ($details) {
                        $details['id'] = 'custom_' . $details['id'];
                        $details['coords'] = [(float)$details['lat'], (float)$details['lng']];
                        $full_items[] = $details;
                    }
                } else {
                    // It's a hardcoded ID. We just return the ID and script.js will handle it.
                    // Or we could return a placeholder. For now, let's just return the ID
                    // so script.js can find it in its local 'pois' array.
                    $full_items[] = ["id" => $p_id, "is_hardcoded" => true];
                }
            }
            
            echo json_encode(["status" => "success", "items" => $full_items]);
        } else {
            // List all routes
            $sql = "SELECT id, route_name, is_public, description, created_at FROM saved_routes WHERE user_id = :user_id ORDER BY created_at DESC";
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
