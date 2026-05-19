<?php
header('Content-Type: application/json');
require_once "../auth/auth_check.php";
require_once "../config/db.php";
checkAccess();

try {
    // Fetch last 50 real notifications from DB, newest first
    $stmt = $pdo->query(
        "SELECT id, type, title, message, is_read,
                CASE
                    WHEN created_at >= NOW() - INTERVAL 1 HOUR  THEN CONCAT(TIMESTAMPDIFF(MINUTE, created_at, NOW()), 'm ago')
                    WHEN created_at >= NOW() - INTERVAL 24 HOUR THEN CONCAT(TIMESTAMPDIFF(HOUR,   created_at, NOW()), 'h ago')
                    ELSE CONCAT(DATEDIFF(NOW(), created_at), 'd ago')
                END AS time_ago
         FROM notifications
         ORDER BY created_at DESC
         LIMIT 50"
    );
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $notifications = [];
    foreach ($rows as $row) {
        $notifications[] = [
            'id'      => intval($row['id']),
            'title'   => $row['title'],
            'message' => $row['message'],
            'type'    => $row['type'],
            'time'    => $row['time_ago'],
            'read'    => (bool) $row['is_read'],
        ];
    }

    echo json_encode($notifications);

} catch (Throwable $e) {
    // Fallback: return empty array rather than crash
    echo json_encode([]);
}
?>