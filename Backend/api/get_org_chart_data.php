<?php
// Backend/api/get_org_chart_data.php - CEO as single root (always enforced)
require_once __DIR__ . "/../auth/auth_check.php";
require_once __DIR__ . "/../config/db.php";
checkAccess();

header('Content-Type: application/json');

try {
    // Auto-heal: ensure CEO (role_id=2) always has manager_id = NULL in DB
    $pdo->exec("UPDATE users SET manager_id = NULL WHERE role_id = 2");

    // 1. Fetch all users — include role_id so we can find CEO reliably
    $query = "SELECT u.id, u.full_name, u.email, u.manager_id, u.role_id,
                     COALESCE(u.job_title, r.role_name) as role_name,
                     d.name as dept_name, d.icon, d.color_class
              FROM users u
              LEFT JOIN roles r ON u.role_id = r.id
              LEFT JOIN departments d ON u.department_id = d.id";
    $stmt = $pdo->query($query);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Build employee map
    //    Force CEO (role_id=2) manager_id to NULL so they are ALWAYS root
    $employeeMap = [];
    foreach ($employees as $employee) {
        $employee['children'] = [];
        if (intval($employee['role_id']) === 2) {
            $employee['manager_id'] = null; // override in memory too
        }
        $employeeMap[$employee['id']] = $employee;
    }

    // 3. Build tree
    $allRoots = [];
    foreach ($employeeMap as &$employee) {
        $mid = $employee['manager_id'];
        if ($mid && isset($employeeMap[$mid]) && $mid != $employee['id']) {
            $employeeMap[$mid]['children'][] = &$employee;
        } else {
            $allRoots[] = &$employee;
        }
    }

    // 4. Find CEO (role_id=2) as master root — guaranteed to be in $allRoots now
    $masterRoot = null;
    foreach ($allRoots as &$r) {
        if (intval($r['role_id']) === 2) {
            $masterRoot = &$r;
            break;
        }
    }
    // Fallback: first root
    if (!$masterRoot && !empty($allRoots)) {
        $masterRoot = &$allRoots[0];
    }

    // 5. Any other orphan roots become direct children of CEO
    if ($masterRoot) {
        $rootId = $masterRoot['id'];
        foreach ($allRoots as &$r) {
            if ($r['id'] != $rootId) {
                $alreadyChild = false;
                foreach ($masterRoot['children'] as $child) {
                    if ($child['id'] == $r['id']) { $alreadyChild = true; break; }
                }
                if (!$alreadyChild) {
                    $masterRoot['children'][] = $r;
                }
            }
        }
        echo json_encode($masterRoot);
    } else {
        echo json_encode(['error' => 'No employee data found']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>