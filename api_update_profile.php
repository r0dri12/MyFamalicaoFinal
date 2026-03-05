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

if (isset($data->full_name) || isset($data->language)) {
    $updates = [];
    $params = [":id" => $user_id];

    if (isset($data->full_name)) {
        $updates[] = "full_name = :full_name";
        $params[":full_name"] = $data->full_name;
    }
    if (isset($data->language)) {
        $updates[] = "language = :language";
        $params[":language"] = $data->language;
    }

    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = :id";
    $stmt = $conn->prepare($sql);

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }

    if ($stmt->execute()) {
        if (isset($data->language))
            $_SESSION["language"] = $data->language;
        echo json_encode(["status" => "success", "message" => "Perfil atualizado"]);
    }
    else {
        $errorInfo = $stmt->errorInfo();
        echo json_encode(["status" => "error", "message" => "Erro na BD: " . ($errorInfo[2] ?? 'Erro desconhecido')]);
    }
}
else {
    echo json_encode(["status" => "error", "message" => "Dados inválidos"]);
}

unset($conn);
?>
