<?php
// get_all_permissions.php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM permissions ORDER BY id ASC");
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($permissions);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>