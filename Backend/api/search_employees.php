<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../auth/auth_check.php';
checkAccess();

$q = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';

try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, u.role_id,
               COALESCE(u.job_title, r.role_name) AS role_name,
               d.name AS dept_name, d.color_class AS dept_color
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.full_name LIKE :q
           OR u.email    LIKE :q2
           OR r.role_name LIKE :q3
           OR u.job_title LIKE :q5
           OR d.name      LIKE :q4
        ORDER BY u.full_name ASC
        LIMIT 100
    ");
    $stmt->execute([':q' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like, ':q5' => $like]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
