<?php
session_start();
header('Content-Type: application/json');

unset($_SESSION['email_verified']);

echo json_encode(["success" => true, "message" => "Verification status reset."]);
?>