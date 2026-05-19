<?php
// Backend/api/add_node.php - Add a new employee node
require_once __DIR__ . "/../auth/auth_check.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/notify.php";
checkAccess();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['full_name']) || empty($input['email']) || empty($input['manager_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields (Name, Email, and Manager are required)']);
    exit;
}

$fullName = $input['full_name'];
$email = $input['email'];
$jobTitle = $input['role_name'] ?? 'Employee';
$managerId = intval($input['manager_id']);

try {
    // 1. Get manager's department to inherit it (optional logic)
    $deptStmt = $pdo->prepare("SELECT department_id FROM users WHERE id = ?");
    $deptStmt->execute([$managerId]);
    $deptId = $deptStmt->fetchColumn() ?: null;

    // 2. Insert new user
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password, job_title, manager_id, department_id, role_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    // Default role_id = 4 (Employee)
    $stmt->execute([$fullName, $email, $hashedPassword, $jobTitle, $managerId, $deptId, 4]);

    $newId = $pdo->lastInsertId();

    // Notification: new employee added
    insertNotification($pdo, 'org_update',
        'New Employee Added',
        "{$fullName} has joined the organization as {$jobTitle}."
    );

    echo json_encode([
        'success' => true,
        'message' => 'Node added successfully',
        'id'      => $newId
    ]);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'error' => 'Email already exists.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>