<?php
session_start();
require_once __DIR__ . '/../classes/User.php';

/* ================================
   STEP 1️⃣ CHECK SESSION
================================ */
if (!isset($_SESSION["logged_in"])) {
    header("Location: ../../Frontend/login.php");
    exit;
}

/* ================================
   STEP 2️⃣ CHECK JWT COOKIE
================================ */
if (!isset($_COOKIE["JWT_TOKEN"])) {
    session_destroy();
    header("Location: ../../Frontend/login.php");
    exit;
}

/* ================================
   STEP 3️⃣ VERIFY JWT TOKEN
================================ */
$_USER = new User();
$user = $_USER->validate($_COOKIE["JWT_TOKEN"]);

if ($user === false) {
    // Token invalid or expired
    setcookie("JWT_TOKEN", "", time() - 3600, "/");
    session_destroy();
    header("Location: ../../Frontend/login.php?error=session_expired");
    exit;
}

/* ================================
   STEP 4️⃣ USER IS AUTHENTICATED
   Dashboard can access $user array
================================ */
?>
