<?php
// Backend/auth/protect.php - Middleware for Auto-login and Session Protection
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../config/encryption.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 2FA Guard ─────────────────────────────────────────────────────────────
// If a user passed the password check but hasn't entered their PIN yet,
// redirect them to the PIN entry page (unless they're already there).
if (
    isset($_SESSION['2fa_user_id']) &&
    !isset($_SESSION['user_id']) &&
    strpos($_SERVER['SCRIPT_FILENAME'] ?? '', 'two_fa.php') === false
) {
    header("Location: ../../Frontend/auth/two_fa.php");
    exit();
}
// ──────────────────────────────────────────────────────────────────────────


$userObj = new User($pdo);

// 1. If session already exists, user is logged in
if (isset($_SESSION['user_id'])) {
    // Decrypt the user ID for usage in the application logic
    $decryptedUserId = decrypt($_SESSION['user_id']);
    $rawUserId = $_SESSION['raw_user_id'] ?? $decryptedUserId;

    // Ensure we have a valid ID
    if (!$decryptedUserId) {
        session_unset();
        session_destroy();
        header("Location: ../../Frontend/login.php");
        exit();
    }

    // --- GLOBAL SESSION VERIFICATION ---
    // Check if current session exists in database
    if (!$userObj->isValidSession($rawUserId, session_id())) {
        // Session was invalidated globally or cleared
        $_SESSION = [];
        session_destroy();
        header("Location: ../../Frontend/login.php?error=session_expired");
        exit();
    }
}
// 2. If session does not exist but cookie exists, attempt auto-login
elseif (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $userId = $userObj->validateToken($token);

    if ($userId) {
        $user = $userObj->findById($userId);
        if ($user) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            $newSessionId = session_id();

            // Track new session in DB
            $userObj->addSession($user['id'], $newSessionId);

            // Restore session (Encrypting ID)
            $_SESSION['user_id'] = encrypt($user['id']);
            $_SESSION['raw_user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_name'] = $user['role_name'] ?? 'Employee';
            $_SESSION['avatar_url'] = $user['avatar_url'];
            if (isset($user['role_id']))
                $_SESSION['role_id'] = $user['role_id'];

            // Rotate token for security
            $newToken = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));
            $userObj->storeToken($userId, $newToken, $expiry);

            setcookie(
                'remember_me',
                $newToken,
                [
                    'expires' => time() + (30 * 24 * 60 * 60),
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
        } else {
            // Invalid user ID in token table
            setcookie('remember_me', '', time() - 3600, '/');
            header("Location: ../../Frontend/login.php");
            exit();
        }
    } else {
        // Invalid or expired token
        setcookie('remember_me', '', time() - 3600, '/'); // Clear invalid cookie
        header("Location: ../../Frontend/login.php");
        exit();
    }
}
// 3. No session and no cookie
else {
    header("Location: ../../Frontend/login.php");
    exit();
}
?>