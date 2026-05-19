<?php
/**
 * Backend/helpers/notify.php
 * Helper to insert a notification into the DB notifications table.
 *
 * Usage:
 *   require_once __DIR__ . '/notify.php';
 *   insertNotification($pdo, 'org_update', 'Employee Added', 'John Doe joined as Software Engineer.');
 *
 * Types: org_update | job_change | ceo_change | delete | info
 */
function insertNotification(PDO $pdo, string $type, string $title, string $message): void
{
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO notifications (type, title, message, created_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$type, $title, $message]);
    } catch (Throwable $e) {
        // Notifications are non-critical — never let them crash the main operation
        error_log('[notify.php] Failed to insert notification: ' . $e->getMessage());
    }
}
?>
