<?php
// Backend/api/verify_2fa_pin.php
require_once __DIR__ . '/../../Backend/config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Must have a pending 2FA session (user passed password but not PIN yet)
if (!isset($_SESSION['2fa_user_id'])) {
    header("Location: ../../Frontend/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../Frontend/auth/two_fa.php");
    exit();
}

$pin    = trim($_POST['pin'] ?? '');
$userId = (int) $_SESSION['2fa_user_id'];

$stmt = $pdo->prepare("SELECT two_fa_pin FROM users WHERE id = ?");
$stmt->execute([$userId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row && password_verify($pin, $row['two_fa_pin'] ?? '')) {
    // PIN correct — promote temp session to full session
    $_SESSION['2fa_passed'] = true;

    // Fetch full user data to complete session
    $userStmt = $pdo->prepare("
        SELECT u.*, COALESCE(u.job_title, r.role_name) AS role_name
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE u.id = ?
    ");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    require_once __DIR__ . '/../../Backend/config/encryption.php';
    require_once __DIR__ . '/../../Backend/classes/User.php';

    session_regenerate_id(true);
    $newSessionId = session_id();

    $userObj = new User($pdo);
    $userObj->addSession($user['id'], $newSessionId);

    $_SESSION['user_id']    = encrypt($user['id']);
    $_SESSION['raw_user_id'] = $user['id'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['full_name']  = $user['full_name'];
    $_SESSION['role_id']    = $user['role_id'];
    $_SESSION['role_name']  = $user['role_name'] ?? 'Employee';
    $_SESSION['avatar_url'] = $user['avatar_url'];

    // Clear temp 2FA flag
    unset($_SESSION['2fa_user_id']);

    header("Location: ../../Frontend/dashboard/index.php");
    exit();
} else {
    header("Location: ../../Frontend/auth/two_fa.php?error=1");
    exit();
}
?>
