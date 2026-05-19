<?php
session_start();
require_once __DIR__ . '/../classes/User.php';

$error = "";
$success = "";

// Success message after registration
if (isset($_GET["registered"])) {
    $success = "Account created successfully! Please login.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Handle both JSON and form data
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        if (isset($data['json'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Email and password are required'
            ]);
        } else {
            $error = 'Email and password are required';
        }
        exit;
    }

    // ✅ CREATE USER OBJECT
    $userObj = new User();

    // ✅ LOGIN USING JWT FUNCTION
    $jwt = $userObj->login($email, $password);

    if ($jwt === false) {
        $error = $userObj->error;
        
        // If JSON request, return JSON response
        if (isset($data['json']) || !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $error
            ]);
            exit;
        }
    } else {
        // ✅ SAVE JWT IN SECURE HTTP-ONLY COOKIE
        setcookie("JWT_TOKEN", $jwt, time() + JWT_EXPIRY, "/", "", false, true);

        // Decode token to get user data
        $userData = $userObj->validate($jwt);
        
        if ($userData) {
            // ✅ ALSO SAVE SESSION (OPTIONAL BUT GOOD)
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['username'] = $userData['username'];
            $_SESSION['email'] = $userData['email'];
            $_SESSION['jwt_token'] = $jwt;

            // Create session record in database
            $userObj->createSession($userData['id'], session_id());

            // If JSON request, return JSON response
            if (isset($data['json']) || !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'token' => $jwt,
                    'user' => [
                        'id' => $userData['id'],
                        'username' => $userData['username'],
                        'email' => $userData['email']
                    ],
                    'redirect' => '../../Frontend/dashboard/dashboard_simple.php'
                ]);
            } else {
                // Redirect for form-based login
                header("Location: ../../Frontend/dashboard/dashboard_simple.php");
            }
            exit;
        }
    }
}
?>

