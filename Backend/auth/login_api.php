<?php
require_once __DIR__ . "/../classes/User.php";

$_USER = new User();

$token = $_USER->login($_POST["email"], $_POST["password"]);

if ($token !== false) {
    setcookie("JWT_TOKEN", $token, time() + JWT_EXPIRY, "/", "", false, true);
    echo json_encode(["status" => true]);
} else {
    echo json_encode([
        "status" => false,
        "message" => $_USER->error
    ]);
}
