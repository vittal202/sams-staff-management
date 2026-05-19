<?php
require_once __DIR__ . "/../classes/User.php";

$_USER = new User();

$result = $_USER->register($_POST["username"], $_POST["email"], $_POST["password"]);

if ($result !== false) {
    echo json_encode([
        "status" => true,
        "message" => "Registration successful!"
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => $_USER->error
    ]);
}
