<?php
// init_db.php - One-click database setup
require_once __DIR__ . '/../config/db_simple.php';

echo "<h1>Initializing SAMS Database...</h1>";
echo "<pre>";

$files = [
    'init_schema.sql',          // The file we just made (Schema + Base Data)
    'add_15_employees.sql',     // The user's requested file
    'add_username_column.sql',  // Fix missing username
    'add_preference_columns.sql', // Fix missing preference columns
    'assign_job_titles.sql',    // Fix titles
    'add_manager_id.sql',       // Fix hierarchy
    'fix_auth_tables.sql'       // Final auth tables
];

// Helper to run SQL file
function runSqlFile($pdo, $filename)
{
    if (!file_exists(__DIR__ . '/' . $filename)) {
        echo "⚠️ Skipping $filename (File not found)\n";
        return;
    }

    echo "▶ Running $filename... ";
    try {
        $sql = file_get_contents(__DIR__ . '/' . $filename);

        // Remove comments to avoid issues with some parsers, 
        // but PDO usually handles multiple queries if we use correct settings.
        // We'll try running the whole block.
        $pdo->exec($sql);
        echo "✅ DONE\n";
    } catch (PDOException $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
}

try {
    // 1. Create Database if not exists (using root connection without DB)
    $pdoRoot = new PDO("mysql:host=$host", $user, $pass);
    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    echo "✅ Database `$db` checked/created.\n";

    // 2. Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // 3. Disable Foreign Key Checks temporarily to avoid ordering headaches
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 4. Run Files
    foreach ($files as $file) {
        runSqlFile($pdo, $file);
    }

    // 5. Re-enable Foreign Key Checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n🎉 Database Setup Complete!\n";
    echo "You can now login with:\n";
    echo "Email: michael.chen@sams.com\n";
    echo "Password: password\n"; // Based on add_15_employees.sql hash

} catch (PDOException $e) {
    echo "💀 CRITICAL ERROR: " . $e->getMessage();
}

echo "</pre>";
?>