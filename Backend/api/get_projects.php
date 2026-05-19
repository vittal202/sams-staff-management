<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../auth/auth_check.php';
checkAccess();

try {
    $stmt = $pdo->query("
        SELECT p.*, u.full_name AS handler_name
        FROM projects p
        LEFT JOIN users u ON p.handled_by = u.id
        ORDER BY p.created_at DESC
    ");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($projects);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>