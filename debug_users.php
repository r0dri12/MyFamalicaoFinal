<?php
require_once "db_connect.php";
$stmt = $conn->query("SELECT id, username, is_admin FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('user_check.txt', print_r($users, true));
echo "Done";
?>
