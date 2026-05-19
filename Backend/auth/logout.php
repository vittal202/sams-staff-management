<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userObj = new User($pdo);

try {
    // 1. Clear current session from DB if logged in
    if (isset($_SESSION['raw_user_id'])) {
        $userObj->deleteSession(session_id());
    }

    // 2. Clear token from DB if cookie exists
    if (isset($_COOKIE['remember_me'])) {
        $stmt = $pdo->prepare("DELETE FROM tokens WHERE token = ?");
        $stmt->execute([$_COOKIE['remember_me']]);
    }
} catch (Exception $e) {
    // Log error but proceed to destroy local session
    error_log("Logout Cleanup Error: " . $e->getMessage());
}

// 3. Clear Cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// 4. Destroy PHP Session
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

header("Location: ../../Frontend/login.php");
exit();
?>