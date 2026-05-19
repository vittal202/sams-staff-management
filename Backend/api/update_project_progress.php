<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../auth/auth_check.php';
checkAccess();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $input['project_id'] ?? null;
    $progress = isset($input['progress']) ? (int) $input['progress'] : null;

    if (!$projectId || $progress === null) {
        echo json_encode(['success' => false, 'message' => 'Project ID and progress are required.']);
        exit;
    }

    if ($progress < 0 || $progress > 100) {
        echo json_encode(['success' => false, 'message' => 'Progress must be between 0 and 100.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE projects SET progress = ? WHERE id = ?");
        $stmt->execute([$progress, $projectId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Progress updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or project not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>