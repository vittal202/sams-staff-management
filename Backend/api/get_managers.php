<?php
// Backend/api/get_managers.php - Fetch all users as potential managers
require_once __DIR__ . "/../auth/auth_check.php";
require_once __DIR__ . "/../config/db.php";
checkAccess();

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, full_name, COALESCE(job_title, 'Employee') as role_name FROM users ORDER BY full_name ASC");
    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($managers);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>