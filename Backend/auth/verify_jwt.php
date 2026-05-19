<?php
// (A) JWT NOT SET!
if (!isset($_POST["jwt"])) {
    exit("NO");
}

// (B) VERIFY JWT
require_once __DIR__ . "/../classes/User.php";
$_USER = new User();
$user = $_USER->validate($_POST["jwt"]);

if ($user === false) {
    exit("NO");
}

// (C) PROCEED AS USUAL
echo "YES";
