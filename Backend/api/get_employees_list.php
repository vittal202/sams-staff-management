<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../auth/auth_check.php';
checkAccess();

try {
    $stmt = $pdo->query("
        SELECT u.id, u.full_name, r.role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        ORDER BY u.full_name ASC
    ");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($employees);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
