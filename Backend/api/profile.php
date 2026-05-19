<?php
// api/profile.php
require_once '../config/db.php';
require_once '../auth/auth_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$stmt = $pdo->prepare('SELECT id, full_name, email, role_id FROM users WHERE id = ?');
$stmt->execute([$_SESSION['raw_user_id']]);
$user = $stmt->fetch();

echo json_encode($user);
?>