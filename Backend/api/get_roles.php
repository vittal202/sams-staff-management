<?php
// get_roles.php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    // Fetch all roles
    $stmt = $pdo->query("SELECT * FROM roles ORDER BY id ASC");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch permissions for each role
    foreach ($roles as &$role) {
        $stmt = $pdo->prepare("
            SELECT p.permission_name 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ");
        $stmt->execute([$role['id']]);
        $role['permissions'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    echo json_encode($roles);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>