<?php
// Backend/api/delete_employee_node.php - Delete an employee and reassign subordinates
require_once __DIR__ . "/../auth/auth_check.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/notify.php";
checkAccess(); // Ensure user is logged in

header('Content-Type: application/json');

// ── Role-Based Access Control ──────────────────────────────────────────────
// Only CEO (role_id=2) or root node (manager_id IS NULL) can delete nodes
$callerStmt = $pdo->prepare("SELECT role_id, manager_id FROM users WHERE id = ?");
$callerStmt->execute([$_SESSION['raw_user_id'] ?? 0]);
$caller = $callerStmt->fetch(PDO::FETCH_ASSOC);
if (!$caller || (intval($caller['role_id']) !== 2 && $caller['manager_id'] !== null)) {
    echo json_encode(['success' => false, 'error' => 'Access denied. Only the CEO can delete nodes.']);
    exit;
}
// ──────────────────────────────────────────────────────────────────────────

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input && !empty($_POST)) {
    $input = $_POST;
}

if (!isset($input['employee_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameter: employee_id']);
    exit;
}

$employeeId = intval($input['employee_id']);

try {
    $pdo->beginTransaction();

    // 1. Fetch employee details to check for special roles and get their manager
    $stmt = $pdo->prepare("
        SELECT u.id, u.manager_id, u.full_name, COALESCE(u.job_title, r.role_name) as role_name 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        throw new Exception("Employee not found.");
    }

    $roleName = strtolower($employee['role_name'] ?? '');

    // 2. Prevent deletion of CEO or Founder (Special Protection)
    // Also check if they are a root node (manager_id is NULL)
    if (strpos($roleName, 'ceo') !== false || strpos($roleName, 'founder') !== false || $employee['manager_id'] === null) {
        // Count how many root nodes exist
        $rootCountStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE manager_id IS NULL");
        $rootCount = $rootCountStmt->fetchColumn();

        if ($rootCount <= 1) {
            throw new Exception("Cannot delete the main Organization Root (" . $employee['role_name'] . "). Please assign a successor or promote another manager first.");
        }
    }

    // 3. Reassign Subordinates
    // Move all subordinates of the deleted employee to the deleted employee's manager
    $newManagerId = $employee['manager_id']; // This could be NULL if we're deleting a root that isn't the only root

    $updateSubordinatesStmt = $pdo->prepare("UPDATE users SET manager_id = ? WHERE manager_id = ?");
    $updateSubordinatesStmt->execute([$newManagerId, $employeeId]);
    $reassignedCount = $updateSubordinatesStmt->rowCount();

    // 4. Delete the employee
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->execute([$employeeId]);

    $pdo->commit();

    // Notification: employee deleted
    $deletedName = $employee['full_name'] ?? 'An employee';
    insertNotification($pdo, 'org_update',
        'Employee Removed',
        "{$deletedName} has been removed from the organization. {$reassignedCount} subordinate(s) reassigned."
    );

    echo json_encode([
        'success'          => true,
        'message'          => 'Employee deleted successfully.',
        'reassigned_count' => $reassignedCount,
        'new_parent_id'    => $newManagerId
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>