<?php
// api/permissions.php
require_once '../config/db.php';
require_once '../auth/auth_check.php';

checkAccess();

header('Content-Type: application/json');

// Simplified: Return permissions for the logged-in role
$stmt = $pdo->prepare('
    SELECT p.permission_name 
    FROM permissions p 
    JOIN role_permissions rp ON p.id = rp.permission_id 
    WHERE rp.role_id = ?
');
$stmt->execute([$_SESSION['role_id']]);
$permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode(['role_id' => $_SESSION['role_id'], 'permissions' => $permissions]);
?>