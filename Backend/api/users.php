<?php
// api/users.php
require_once '../config/db.php';
require_once '../auth/auth_check.php';

// Only Board and CEO can view all users
checkAccess();
if ($_SESSION['role_id'] > 2) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Permission denied']);
    exit();
}

header('Content-Type: application/json');

$stmt = $pdo->query('SELECT u.id, u.full_name, u.email, r.role_name FROM users u JOIN roles r ON u.role_id = r.id');
$users = $stmt->fetchAll();

echo json_encode($users);
?>