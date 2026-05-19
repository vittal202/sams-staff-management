<?php
try {
    require_once "../../Backend/auth/auth_check.php";
    require_once "../../Backend/config/db.php";
    checkAccess(); // Ensure user is logged in

    header('Content-Type: application/json');

    $userId = $_SESSION['raw_user_id'];
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Matrix Error: Null Data Stream.']);
        exit();
    }

    $action = $data['action'] ?? '';

    if ($action === 'update_preferences') {
        // Build dynamic update query
        $updateFields = [];
        $updateValues = [];

        // Personal Info
        if (array_key_exists('full_name', $data)) {
            $updateFields[] = "full_name = ?";
            $updateValues[] = $data['full_name'];
        }
        if (array_key_exists('email', $data)) {
            $email = $data['email'];
            // Check if email is being changed and if it's already taken
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already in use by another account.']);
                exit();
            }
            $updateFields[] = "email = ?";
            $updateValues[] = $email;
        }

        // Appearance & Preferences
        $fields = ['language', 'timezone', 'theme', 'compact_view', 'email_notifications', 'push_notifications'];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updateFields[] = "`$field` = ?";
                $updateValues[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            echo json_encode(['success' => true, 'message' => 'No changes detected.']);
            exit();
        }

        $updateValues[] = $userId;
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateValues);

        echo json_encode(['success' => true, 'message' => 'Settings Synchronized.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown protocol.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Fault: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
}