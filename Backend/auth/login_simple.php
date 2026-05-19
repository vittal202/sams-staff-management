<?php
session_start();
require_once "../config/db_simple.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Query users table (username-based schema)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Secure password check using bcrypt verification
    if ($user && password_verify($password, $user['password'])) {

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        // Redirect to dashboard on success
        header("Location: ../../Frontend/dashboard/index.php");
        exit();

    } else {
        // Redirect with error flag for invalid credentials
        header("Location: ../../Frontend/login.php?error=invalid_credentials");
        exit();
    }
}
?>
