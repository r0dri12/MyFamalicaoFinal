<?php
require_once "db_connect.php";

try {
    $sql = "ALTER TABLE users ADD COLUMN language VARCHAR(5) DEFAULT 'pt'";
    $conn->exec($sql);
    echo "Sucesso: Coluna 'language' adicionada à tabela 'users'.";
}
catch (PDOException $e) {
    echo "Erro (ou coluna já existe): " . $e->getMessage();
}
?>
