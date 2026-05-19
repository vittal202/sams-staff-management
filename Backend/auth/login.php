<?php
session_start();
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT u.*, COALESCE(u.job_title, r.role_name) AS role_name
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Secure password check: Only allow hashed passwords
    if ($user && password_verify($password, $user['password'])) {

        // ── 2FA Check ────────────────────────────────────────────────────
        if (!empty($user['two_fa_enabled']) && !empty($user['two_fa_pin'])) {
            // Store a temporary session — NOT the full auth session
            $_SESSION['2fa_user_id'] = $user['id'];
            unset($_SESSION['user_id']); // ensure no partial auth
            header("Location: ../../Frontend/auth/two_fa.php");
            exit();
        }
        // ─────────────────────────────────────────────────────────────────

        // No 2FA — complete login normally
        require_once "../config/encryption.php";
        require_once "../classes/User.php";

        session_regenerate_id(true);
        $newSessionId = session_id();

        $userObj = new User($pdo);
        $userObj->addSession($user['id'], $newSessionId);

        $_SESSION['user_id']     = encrypt($user['id']);
        $_SESSION['raw_user_id'] = $user['id'];
        $_SESSION['email']       = $user['email'];
        $_SESSION['username']    = $user['username'];
        $_SESSION['full_name']   = $user['full_name'];
        $_SESSION['role_id']     = $user['role_id'];
        $_SESSION['role_name']   = $user['role_name'];
        $_SESSION['avatar_url']  = $user['avatar_url'];

        header("Location: ../../Frontend/dashboard/index.php");
        exit();

    } else {
        header("Location: ../../Frontend/login.php?error=invalid_credentials");
        exit();
    }
}
?>