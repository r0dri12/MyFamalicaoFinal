<?php
session_start();
require_once "db_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Não autenticado"]);
    exit;
}

$user_id = $_SESSION["id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if it's the action-based logic from meus_locais
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $poi_id = $_POST['id'] ?? null;

        if (!$poi_id) {
            echo json_encode(["status" => "error", "message" => "ID do local em falta."]);
            exit;
        }

        // Action: DELETE
        if ($action === 'delete') {
            $sql = "DELETE FROM custom_pois WHERE id = :id AND user_id = :user_id";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bindParam(":id", $poi_id, PDO::PARAM_INT);
                $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Local eliminado."]);
                }
                else {
                    echo json_encode(["status" => "error", "message" => "Erro ao eliminar local."]);
                }
            }
            exit;
        }

        // Action: EDIT
        if ($action === 'edit') {
            $name = $_POST['name'] ?? '';
            $desc = $_POST['description'] ?? '';
            $type = $_POST['type'] ?? 'Outro';

            // Handle image upload if provided
            $image_query_part = "";
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($file_extension, $allowed)) {
                    // Read file contents and convert to base64
                    $image_data = file_get_contents($_FILES["image"]["tmp_name"]);
                    $base64 = base64_encode($image_data);

                    // Determine MIME type
                    $mime = "image/jpeg";
                    if ($file_extension === 'png')
                        $mime = "image/png";
                    else if ($file_extension === 'gif')
                        $mime = "image/gif";
                    else if ($file_extension === 'webp')
                        $mime = "image/webp";

                    // Create base64 string readable by HTML <img> tag
                    $target_file = "data:" . $mime . ";base64," . $base64;
                    $image_query_part = ", image = :image";
                }
                else {
                    echo json_encode(["status" => "error", "message" => "Formato de imagem inválido."]);
                    exit;
                }
            }

            $is_public = isset($_POST['is_public']) ? (int)$_POST['is_public'] : 0;

            $sql = "UPDATE custom_pois SET name = :name, description = :description, type = :type, is_public = :is_public {$image_query_part} WHERE id = :id AND user_id = :user_id";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":description", $desc, PDO::PARAM_STR);
                $stmt->bindParam(":type", $type, PDO::PARAM_STR);
                $stmt->bindParam(":is_public", $is_public, PDO::PARAM_INT);
                $stmt->bindParam(":id", $poi_id, PDO::PARAM_INT);
                $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);

                if (!empty($image_query_part)) {
                    $stmt->bindParam(":image", $target_file, PDO::PARAM_STR);
                }

                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Local atualizado com sucesso."]);
                }
                else {
                    echo json_encode(["status" => "error", "message" => "Erro ao atualizar local."]);
                }
            }
            exit;
        }
    }
}

echo json_encode(["status" => "error", "message" => "Ação inválida."]);
?>
