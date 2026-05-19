<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/User.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Fallback to POST data if JSON is not provided
    if (!$data) {
        $data = $_POST;
    }

    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username, email, and password are required'
        ]);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }

    // Validate password strength (minimum 6 characters)
    if (strlen($password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters long'
        ]);
        exit;
    }

    $user = new User();
    $result = $user->register($username, $email, $password);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! You can now login.',
            'redirect' => '../../Frontend/login.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $user->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
