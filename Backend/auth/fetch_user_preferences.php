<?php
// Backend/auth/fetch_user_preferences.php
require_once __DIR__ . "/../config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$compactView = 0;

if (isset($_SESSION['raw_user_id'])) {
    $stmt = $pdo->prepare("
        SELECT u.compact_view, u.full_name, u.avatar_url,
               COALESCE(u.job_title, r.role_name) AS role_name
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['raw_user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $compactView = $user['compact_view'];
        // Sync session data to ensure current values if they changed in DB
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role_name'] = $user['role_name'] ?? 'Employee';
        $_SESSION['avatar_url'] = $user['avatar_url'];
    }
}
?>