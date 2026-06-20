<?php
session_start();
require_once "db_connect.php";

header('Content-Type: application/json');

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode(["status" => "error", "message" => "Nao autenticado"]);
    exit;
}

$user_id = $_SESSION["id"];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $data = json_decode(file_get_contents("php://input"));
    if(isset($data->name) && isset($data->lat) && isset($data->lng)){
        $type = isset($data->type) ? $data->type : 'Outro';
        $is_public = isset($data->is_public) ? (int)$data->is_public : 0;
        $img = 'https://images.unsplash.com/photo-1524661135-423995f22d0b';
        $sql = "INSERT INTO custom_pois (user_id, name, description, latitude, longitude, type, image, is_public) VALUES (:user_id, :name, :description, :lat, :lng, :type, :image, :is_public)";
        if($stmt = $conn->prepare($sql)){
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam(":name", $data->name, PDO::PARAM_STR);
            $stmt->bindParam(":description", $data->description, PDO::PARAM_STR);
            $stmt->bindParam(":lat", $data->lat, PDO::PARAM_STR);
            $stmt->bindParam(":lng", $data->lng, PDO::PARAM_STR);
            $stmt->bindParam(":type", $type, PDO::PARAM_STR);
            $stmt->bindParam(":image", $img, PDO::PARAM_STR);
            $stmt->bindParam(":is_public", $is_public, PDO::PARAM_INT);
            if($stmt->execute()){
                $new_id = $conn->lastInsertId();
                 echo json_encode(["status" => "success", "message" => "Ponto criado", "poi" => ["id" => "custom_".$new_id, "name" => $data->name, "description" => $data->description, "lat" => $data->lat, "lng" => $data->lng, "type" => $type, "image" => $img, "is_public" => $is_public, "was_cloned" => 0]]);
             } else { echo json_encode(["status" => "error", "message" => "Erro ao guardar"]); }
            unset($stmt);
        }
    } else { echo json_encode(["status" => "error", "message" => "Dados incompletos"]); }
} else if($_SERVER["REQUEST_METHOD"] == "GET"){
    $sql = "SELECT id, name, description, latitude as lat, longitude as lng, type, image, was_cloned FROM custom_pois WHERE user_id = :user_id";
    if($stmt = $conn->prepare($sql)){
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        if($stmt->execute()){
            $pois = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $default_img = 'https://images.unsplash.com/photo-1524661135-423995f22d0b';
            foreach($pois as &$poi) {
                $poi["id"] = "custom_".$poi["id"];
                if(empty($poi["type"])) $poi["type"] = "Outro";
                if(empty($poi["image"])) $poi["image"] = $default_img;
                $poi["coords"] = [(float)$poi["lat"], (float)$poi["lng"]];
                $poi["was_cloned"] = (int)($poi["was_cloned"] ?? 0);
            }
            echo json_encode(["status" => "success", "pois" => $pois]);
        } else { echo json_encode(["status" => "error", "message" => "Erro ao carregar"]); }
    }
}
unset($conn);
?>