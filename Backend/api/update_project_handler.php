<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../auth/auth_check.php';
checkAccess();

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $input['project_id'] ?? null;
    $handledBy  = isset($input['handled_by']) ? (int) $input['handled_by'] : null;

    if (!$projectId) {
        echo json_encode(['success' => false, 'message' => 'Project ID is required.']);
        exit;
    }

    try {
        // Allow clearing the assignment by passing null / 0
        $handledByValue = ($handledBy && $handledBy > 0) ? $handledBy : null;
        $stmt = $pdo->prepare("UPDATE projects SET handled_by = ? WHERE id = ?");
        $stmt->execute([$handledByValue, $projectId]);

        echo json_encode(['success' => true, 'message' => 'Handler updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
