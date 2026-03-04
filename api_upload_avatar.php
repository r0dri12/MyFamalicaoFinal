<?php
session_start();
require_once "db_connect.php";

header('Content-Type: application/json');

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode(["status" => "error", "message" => "Não autenticado"]);
    exit;
}

$user_id = $_SESSION["id"];

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["avatar"])){
    $file = $_FILES["avatar"];
    $allowed_types = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    $max_size = 5 * 1024 * 1024; // 5MB

    if($file["error"] !== 0){
        echo json_encode(["status" => "error", "message" => "Erro ao fazer upload do ficheiro."]);
        exit;
    }
    
    if(!in_array($file["type"], $allowed_types)){
        echo json_encode(["status" => "error", "message" => "Formato inválido. Usa JPG, PNG ou GIF."]);
        exit;
    }
    
    if($file["size"] > $max_size){
        echo json_encode(["status" => "error", "message" => "O ficheiro é demasiado grande (máx. 5MB)."]);
        exit;
    }
    
    $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
    $filename = "avatar_" . $user_id . "_" . time() . "." . $ext;
    $upload_dir = "uploads/avatars/";
    $upload_path = $upload_dir . $filename;
    
    if(move_uploaded_file($file["tmp_name"], $upload_path)){
        // Save path to DB
        $sql = "UPDATE users SET profile_picture = :path WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":path", $upload_path, PDO::PARAM_STR);
        $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
        
        if($stmt->execute()){
            $_SESSION["profile_picture"] = $upload_path;
            echo json_encode(["status" => "success", "path" => $upload_path]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erro ao guardar na base de dados."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Falha ao mover o ficheiro. Verifica as permissões da pasta."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Nenhum ficheiro enviado."]);
}
unset($conn);
?>
