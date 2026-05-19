<?php
// Backend/api/upload_profile_picture.php
require_once "../../Backend/auth/auth_check.php";
require_once "../../Backend/config/db.php";
checkAccess(); // Ensure user is logged in

header('Content-Type: application/json');

$userId = $_SESSION['raw_user_id'];

if (!isset($_FILES['profile_picture'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    exit();
}

$file = $_FILES['profile_picture'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxSize = 2 * 1024 * 1024; // 2MB

// Validation
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed.']);
    exit();
}

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 2MB limit.']);
    exit();
}

// Ensure Upload Directory Exists
$uploadDir = __DIR__ . '/../../Frontend/assets/uploads/avatars/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate Unique Filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'user_' . $userId . '_' . time() . '.' . $ext;
$targetPath = $uploadDir . $filename;
$publicUrl = '../assets/uploads/avatars/' . $filename;

// Check and Add 'avatar_url' column if missing
try {
    $columns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('avatar_url', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN `avatar_url` VARCHAR(255) DEFAULT NULL");
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Update Database
        $stmt = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
        $stmt->execute([$publicUrl, $userId]);

        echo json_encode(['success' => true, 'message' => 'Profile picture updated.', 'url' => $publicUrl]);
    } else {
        throw new Exception('Failed to move uploaded file.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $e->getMessage()]);
}
?>