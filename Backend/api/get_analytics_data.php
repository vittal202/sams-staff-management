<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../auth/auth_check.php';
checkAccess();

try {
    // 1. Total Counts
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalEmployees = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM departments");
    $totalDepts = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM roles");
    $totalRoles = $stmt->fetchColumn();

    // 2. Department Distribution
    $stmt = $pdo->query("
        SELECT d.name, COUNT(u.id) as count 
        FROM departments d 
        LEFT JOIN users u ON d.id = u.department_id 
        GROUP BY d.id 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $deptDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Role Distribution
    $stmt = $pdo->query("
        SELECT r.role_name, COUNT(u.id) as count 
        FROM roles r 
        LEFT JOIN users u ON r.id = u.role_id 
        GROUP BY r.id
    ");
    $roleDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Growth Trend (Simulated for visuals, using created_at if possible)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count 
        FROM users 
        GROUP BY month 
        ORDER BY created_at ASC 
        LIMIT 6
    ");
    $growthTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'summary' => [
            'total_employees' => $totalEmployees,
            'total_departments' => $totalDepts,
            'total_roles' => $totalRoles
        ],
        'dept_distribution' => $deptDistribution,
        'role_distribution' => $roleDistribution,
        'growth_trend' => $growthTrend
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>