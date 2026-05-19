<?php
// index.php - Authentication Entry Point

require_once 'Backend/config/db.php'; 
require_once 'Backend/classes/User.php';
require_once 'Backend/config/encryption.php';

session_start();

// Initialize User object with the PDO instance from db.php
$userObj = new User($pdo); 

// 1. Check Session
if (isset($_SESSION['user_id'])) {
    header("Location: Frontend/dashboard/index.php");
    exit();
}

// 2. Check Cookie
if (isset($_COOKIE['remember_me'])) {
    $userId = $userObj->validateToken($_COOKIE['remember_me']);
    
    if ($userId) {
        $user = $userObj->findById($userId);
        if ($user) {
            // Restore session
            $_SESSION['user_id'] = encrypt($user['id']);
            $_SESSION['raw_user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Restore other session vars if available
            if (isset($user['role_id'])) $_SESSION['role_id'] = $user['role_id'];
            if (isset($user['full_name'])) $_SESSION['full_name'] = $user['full_name'];
            
            header("Location: Frontend/dashboard/index.php");
            exit();
        }
    }
}

// 3. Default to Registration
header("Location: Frontend/register.php");
exit();
?>
