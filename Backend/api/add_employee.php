<?php
// Backend/api/add_employee.php
require_once "../../Backend/auth/auth_check.php";
require_once "../../Backend/config/db.php";
checkAccess(); // Ensure user is logged in

// Only CEO (role_id = 2) can add employees
if (($_SESSION['role_id'] ?? 0) != 2) {
    header("Location: ../../Frontend/Employees/index.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $jobTitle = $_POST['job_title'] ?? null;
    $departmentId = $_POST['department_id'] ?? null;
    $roleId = $_POST['role_id'] ?? 4; // Default to Employee

    // Use provided password or fallback to default
    $rawPassword = !empty($_POST['password']) ? $_POST['password'] : 'password123';

    if (empty($fullName) || empty($email)) {
        echo json_encode(['success' => false, 'message' => "Name and Email are required."]);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id, department_id, job_title) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fullName, $email, $hashedPassword, $roleId, $departmentId, $jobTitle ?: null]);

        // Redirect back to employees page with success flag
        header("Location: ../../Frontend/Employees/index.php?success=1");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => "Error: Email already exists."]);
            exit;
        }
        echo json_encode(['success' => false, 'message' => "Database error: " . $e->getMessage()]);
        exit;
    }
} else {
    header("Location: ../../Frontend/Employees/index.php");
    exit();
}
?>