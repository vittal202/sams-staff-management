<?php
require_once __DIR__ . '/protect.php';

function checkAccess($allowedRoles = [])
{
    if (!empty($allowedRoles) && (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], $allowedRoles))) {
        echo "Access Denied";
        exit();
    }
}
