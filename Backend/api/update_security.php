<?php
// Backend/api/update_security.php
require_once "../../Backend/auth/auth_check.php";
require_once "../../Backend/config/db.php";
checkAccess();

header('Content-Type: application/json');

$userId = $_SESSION['raw_user_id'];
$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? ($_POST['action'] ?? '');

// ── Change Password ──────────────────────────────────────────────────────────
if ($action === 'update_password') {
    $currentPassword = $input['current_password'] ?? ($_POST['current_password'] ?? '');
    $newPassword     = $input['new_password']     ?? ($_POST['new_password']     ?? '');
    $confirmPassword = $input['confirm_password'] ?? ($_POST['confirm_password'] ?? '');

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'All password fields are required.']); exit;
    }
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match.']); exit;
    }
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters.']); exit;
    }

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        $hash    = $user['password'];
        $isMatch = (password_get_info($hash)['algo'] !== 0)
            ? password_verify($currentPassword, $hash)
            : ($currentPassword === $hash);

        if ($isMatch) {
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
                ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
            echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }

// ── Set 2FA PIN (enables 2FA) ────────────────────────────────────────────────
} elseif ($action === 'set_2fa_pin') {
    $pin        = $input['pin']         ?? ($_POST['pin']         ?? '');
    $confirmPin = $input['confirm_pin'] ?? ($_POST['confirm_pin'] ?? '');

    if (!preg_match('/^\d{6}$/', $pin)) {
        echo json_encode(['success' => false, 'message' => 'PIN must be exactly 6 digits.']); exit;
    }
    if ($pin !== $confirmPin) {
        echo json_encode(['success' => false, 'message' => 'PINs do not match.']); exit;
    }

    $pdo->prepare("UPDATE users SET two_fa_pin = ?, two_fa_enabled = 1 WHERE id = ?")
        ->execute([password_hash($pin, PASSWORD_DEFAULT), $userId]);

    echo json_encode(['success' => true, 'message' => '2FA PIN set successfully.']);

// ── Toggle 2FA (disable only) ────────────────────────────────────────────────
} elseif ($action === 'toggle_2fa') {
    $status = (int)($input['status'] ?? ($_POST['status'] ?? 0));

    if ($status === 0) {
        $pdo->prepare("UPDATE users SET two_fa_enabled = 0, two_fa_pin = NULL WHERE id = ?")
            ->execute([$userId]);
        echo json_encode(['success' => true, 'message' => '2FA disabled.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Use set_2fa_pin to enable 2FA.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
exit();
?>