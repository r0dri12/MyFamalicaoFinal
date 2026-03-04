<?php
session_start();
require_once "db_connect.php";

header('Content-Type: application/json');

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode(["status" => "error", "message" => "Não autenticado"]);
    exit;
}

$user_id = $_SESSION["id"];
$data = json_decode(file_get_contents("php://input"));

if(isset($data->full_name)){
    $sql = "UPDATE users SET full_name = :full_name WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":full_name", $data->full_name, PDO::PARAM_STR);
    $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
    
    if($stmt->execute()){
        echo json_encode(["status" => "success", "message" => "Perfil atualizado"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao atualizar perfil"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Dados inválidos"]);
}

unset($conn);
?>
