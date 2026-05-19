<?php
// Backend/api/update_employee_manager.php - Update employee's manager and department via drag-and-drop
require_once __DIR__ . "/../auth/auth_check.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/notify.php";
checkAccess(); // Ensure user is logged in

header('Content-Type: application/json');

// ── Role-Based Access Control ──────────────────────────────────────────────
// Allow if: CEO (role_id=2) OR user is the root node (manager_id IS NULL)
$callerStmt = $pdo->prepare("SELECT role_id, manager_id FROM users WHERE id = ?");
$callerStmt->execute([$_SESSION['raw_user_id'] ?? 0]);
$caller = $callerStmt->fetch(PDO::FETCH_ASSOC);
$callerRole      = intval($caller['role_id'] ?? 0);
$callerManagerId = $caller['manager_id']; // NULL = root node

if ($callerRole !== 2 && $callerManagerId !== null) {
    echo json_encode([
        'success' => false,
        'error'   => 'Access denied. Only the CEO can modify the organization structure.'
    ]);
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

if (!isset($input['employee_id']) || !isset($input['new_manager_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$employeeId  = intval($input['employee_id']);
// Treat 0 same as null (no manager)
$newManagerId = (empty($input['new_manager_id'])) ? null : intval($input['new_manager_id']);
if ($newManagerId === 0) $newManagerId = null;

try {
    // 1. Validate employee exists (include email for CEO anchor check)
    $stmt = $pdo->prepare("SELECT id, manager_id, full_name, role_id, department_id, email FROM users WHERE id = ?");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        echo json_encode(['success' => false, 'error' => 'Employee not found']);
        exit;
    }

    // 2. Validate new manager exists (if provided)
    if ($newManagerId !== null) {
        $chkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $chkStmt->execute([$newManagerId]);
        if (!$chkStmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Target manager not found']);
            exit;
        }
    }

    // 3. Prevent self-assignment
    if ($newManagerId !== null && $employeeId === $newManagerId) {
        echo json_encode(['success' => false, 'error' => 'An employee cannot be their own manager']);
        exit;
    }

    // 4. Handle circular dependency (if moving to a subordinate)
    if ($newManagerId !== null && isSubordinate($pdo, $newManagerId, $employeeId)) {
        // RE-PARENTING: move the target subordinate up to the dragged employee's current manager
        $oldManagerOfEmployee = $employee['manager_id']; // May be NULL (e.g. CEO)

        // Only re-parent if the old manager is a real user (not NULL) to avoid FK violation
        $reparentStmt = $pdo->prepare("UPDATE users SET manager_id = ? WHERE id = ?");
        $reparentStmt->execute([$oldManagerOfEmployee, $newManagerId]);
        // Note: if $oldManagerOfEmployee is NULL, this sets manager_id=NULL which is valid (root node)
    }

    // 4. Fetch New Manager Details (Department & Role)
    $mgrRoleId = null;
    $managerTitle = '';
    $deptId = null;

    if ($newManagerId !== null) {
        $stmt = $pdo->prepare("SELECT role_id, job_title, department_id FROM users WHERE id = ?");
        $stmt->execute([$newManagerId]);
        $mgrData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($mgrData) {
            $mgrRoleId = intval($mgrData['role_id']);
            $managerTitle = strtolower($mgrData['job_title'] ?? '');
            $deptId = $mgrData['department_id']; // Sync department with new manager
        }
    }

    // 5. Default Department if unassigned
    if ($deptId === null) {
        $deptId = $employee['department_id'] ?: 3; // Keep existing or default to IT (3)
    }

    // 6. Determine New Role ID & Job Title (Promotion Logic)
    // Direct report to CEO (role_id=2) → Manager (3)
    // Everything else → Employee (4)
    // NOTE: role_id=2 (CEO) is NEVER assigned via drag — reserved for ceo@sams.com only
    if ($mgrRoleId === 2) {
        $newRoleId = 3; // Reports to CEO = Manager level
    } else if ($mgrRoleId === 3) {
        $newRoleId = 4; // Reports to Manager = Employee level
    } else {
        $newRoleId = 4; // Default: Employee
    }

    // Base titles by department
    $deptBaseTitles = [
        1 => 'Operations Specialist',
        2 => 'HR Specialist',
        3 => 'Systems Analyst',
        4 => 'Software Engineer',
        5 => 'Financial Analyst',
        6 => 'Marketing Specialist',
        7 => 'Solutions Consultant',
        8 => 'Support Specialist'
    ];
    $baseTitle = $deptBaseTitles[$deptId] ?? 'Professional Staff';
    $newJobTitle = $baseTitle;

    // Promotion Hierarchy — use role_id for reliability, not job_title strings
    if ($mgrRoleId === 2) {
        // Direct report to CEO → Vice President
        $newJobTitle = 'Vice President of ' . explode(' ', $baseTitle)[0];
    } elseif (strpos($managerTitle, 'vice president') !== false) {
        $newJobTitle = 'Director of ' . explode(' ', $baseTitle)[0];
    } elseif (strpos($managerTitle, 'director') !== false) {
        $newJobTitle = 'Senior Manager, ' . explode(' ', $baseTitle)[0];
    } elseif (strpos($managerTitle, 'manager') !== false || $mgrRoleId === 3) {
        $newJobTitle = 'Lead ' . $baseTitle;
    } elseif (strpos($managerTitle, 'lead') !== false) {
        $newJobTitle = 'Senior ' . $baseTitle;
    }

    // Special case for HR
    if ($deptId == 2 && $newJobTitle == $baseTitle) {
        $newJobTitle = 'HR Coordinator';
    }

    // 7. PERFORM THE UPDATE
    $updateStmt = $pdo->prepare("UPDATE users SET manager_id = ?, role_id = ?, job_title = ?, department_id = ? WHERE id = ?");
    $result = $updateStmt->execute([$newManagerId, $newRoleId, $newJobTitle, $deptId, $employeeId]);

    if ($result) {
        $empName = $employee['full_name'];

        // Notification: job title / manager changed
        insertNotification($pdo, 'job_change',
            'Job Change',
            "{$empName}'s role has been updated to {$newJobTitle}."
        );

        // Notification: CEO-level promotion (direct report to CEO)
        if ($mgrRoleId === 2) {
            insertNotification($pdo, 'ceo_change',
                'Organization Update',
                "{$empName} now reports directly to the CEO."
            );
        }

        echo json_encode([
            'success' => true,
            'message' => 'Hierarchy updated successfully',
            'employee' => [
                'id' => $employeeId,
                'name' => $employee['full_name'],
                'new_manager_id' => $newManagerId,
                'new_dept_id' => $deptId,
                'new_job_title' => $newJobTitle
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Check for circular dependency
 */
function isSubordinate($pdo, $potentialSubordinate, $employeeId)
{
    if ($potentialSubordinate === null)
        return false;

    $stmt = $pdo->prepare("SELECT manager_id FROM users WHERE id = ?");
    $stmt->execute([$potentialSubordinate]);
    $managerId = $stmt->fetchColumn();

    if ($managerId == $employeeId)
        return true;
    if ($managerId == null)
        return false;

    return isSubordinate($pdo, $managerId, $employeeId);
}
?>