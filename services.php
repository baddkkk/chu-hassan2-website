<?php
header("Content-Type: application/json");
require 'db.php';

$stmt = $pdo->query("SELECT * FROM services ORDER BY name");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $services
]);
