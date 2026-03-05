<?php
require_once "db_connect.php";

$sql = "SELECT COUNT(*) as total, SUM(is_public) as public FROM custom_pois";
$stmt = $conn->query($sql);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total POIs: " . $row['total'] . "\n";
echo "Public POIs: " . $row['public'] . "\n";

$sql = "SELECT p.id, p.name, p.is_public, u.username 
        FROM custom_pois p 
        LEFT JOIN users u ON p.user_id = u.id 
        LIMIT 5";
$stmt = $conn->query($sql);
while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$res['id']} | Name: {$res['name']} | Public: {$res['is_public']} | User: " . ($res['username'] ?? 'NULL') . "\n";
}
?>
