<?php
require_once "db_connect.php";

try {
    $sql = "CREATE TABLE IF NOT EXISTS user_badges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        badge_key VARCHAR(50) NOT NULL,
        unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_badge (user_id, badge_key),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql);
    echo "Sucesso: Tabela 'user_badges' criada ou já existente na base de dados.";
}
catch (PDOException $e) {
    echo "Erro ao criar tabela 'user_badges': " . $e->getMessage();
}
?>
