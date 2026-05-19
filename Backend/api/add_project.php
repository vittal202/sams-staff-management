<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../auth/auth_check.php';
checkAccess();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'Planning';
    $progress = (int) ($_POST['progress'] ?? 0);
    $handledBy = !empty($_POST['handled_by']) ? (int) $_POST['handled_by'] : null;

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Project name is required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO projects (name, description, progress, status, handled_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $progress, $status, $handledBy]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>