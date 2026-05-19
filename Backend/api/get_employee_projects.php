<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../auth/auth_check.php';
checkAccess();

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, name, status, progress
        FROM projects
        WHERE handled_by = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($projects);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
