<?php
// Backend/api/get_employee_details.php
require_once "../../Backend/auth/auth_check.php";
require_once "../../Backend/config/db.php";
checkAccess(); // Ensure user is logged in

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'Missing employee ID']);
    exit();
}

try {
    $query = "SELECT u.full_name, u.email, r.role_name, d.name as dept_name, d.icon, d.color_class 
              FROM users u 
              LEFT JOIN roles r ON u.role_id = r.id 
              LEFT JOIN departments d ON u.department_id = d.id 
              WHERE u.id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $employee = $stmt->fetch();

    if ($employee) {
        echo json_encode($employee);
    } else {
        echo json_encode(['error' => 'Employee not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>