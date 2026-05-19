<?php
// update_role_permissions.php
header('Content-Type: application/json');
require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['role_id']) || !isset($data['permissions'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$roleId = $data['role_id'];
$permissionIds = $data['permissions']; // Array of permission IDs

try {
    $pdo->beginTransaction();

    // 1. Remove existing permissions for this role
    $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$roleId]);

    // 2. Insert new permissions
    if (!empty($permissionIds)) {
        $insertStmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permissionIds as $permId) {
            $insertStmt->execute([$roleId, $permId]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>