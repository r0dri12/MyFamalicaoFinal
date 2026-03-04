<?php
session_start();
require_once "db_connect.php";

header('Content-Type: application/json');

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    echo json_encode(["status" => "error", "message" => "Não autenticado"]);
    exit;
}

$user_id = $_SESSION["id"];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Receber os dados do fetch (JSON)
    $data = json_decode(file_get_contents("php://input"));
    
    if(isset($data->name) && isset($data->lat) && isset($data->lng)){
        $sql = "INSERT INTO custom_pois (user_id, name, description, latitude, longitude) VALUES (:user_id, :name, :description, :lat, :lng)";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam(":name", $data->name, PDO::PARAM_STR);
            $stmt->bindParam(":description", $data->description, PDO::PARAM_STR);
            $stmt->bindParam(":lat", $data->lat, PDO::PARAM_STR);
            $stmt->bindParam(":lng", $data->lng, PDO::PARAM_STR);
            
            if($stmt->execute()){
                $new_id = $conn->lastInsertId();
                echo json_encode([
                    "status" => "success", 
                    "message" => "Ponto criado com sucesso",
                    "poi" => [
                        "id" => "custom_" . $new_id,
                        "name" => $data->name,
                        "description" => $data->description,
                        "lat" => $data->lat,
                        "lng" => $data->lng
                    ]
                ]);
            } else{
                echo json_encode(["status" => "error", "message" => "Erro ao guardar na base de dados"]);
            }
            unset($stmt);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Dados incompletos"]);
    }
} else if($_SERVER["REQUEST_METHOD"] == "GET"){
    // Buscar todos os POIs personalizados do utilizador
    $sql = "SELECT id, name, description, latitude as lat, longitude as lng FROM custom_pois WHERE user_id = :user_id";
    
    if($stmt = $conn->prepare($sql)){
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        if($stmt->execute()){
            $pois = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format ID for JS
            foreach($pois as &$poi) {
                $poi['id'] = 'custom_' . $poi['id'];
                $poi['type'] = 'Meu Local';
                $poi['image'] = 'https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'; // Default custom image
                $poi['coords'] = [(float)$poi['lat'], (float)$poi['lng']];
            }
            
            echo json_encode(["status" => "success", "pois" => $pois]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erro ao carregar os teus locais"]);
        }
    }
}
unset($conn);
?>
