<?php
require_once "db_connect.php";

$tables = ['users', 'custom_pois', 'poi_likes', 'poi_comments'];

foreach ($tables as $table) {
    echo "Table: $table\n";
    try {
        $stmt = $conn->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  " . $row['Field'] . " - " . $row['Type'] . "\n";
        }
    } catch (Exception $e) {
        echo "  Table '$table' error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
?>
