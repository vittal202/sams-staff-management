<?php
// Start session
session_start();

/* 1️⃣ Destroy SESSION */
$_SESSION = [];
session_destroy();

/* 2️⃣ Delete JWT COOKIE */
if (isset($_COOKIE["JWT_TOKEN"])) {
    setcookie("JWT_TOKEN", "", time() - 3600, "/");
}

/* 3️⃣ Redirect to login */
header("Location: ../../Frontend/login.php?message=logged_out");
exit;
