<?php
// Test database connection
require_once __DIR__ . '/../config/db_simple.php';

try {
    echo "<h1>Database Setup Verification</h1>";
    echo "<pre>";
    echo "Testing connection to sams_rbac_backend database...\n\n";

    // Check if database exists
    $result = $pdo->query("SELECT DATABASE()");
    $dbName = $result->fetchColumn();
    echo "✓ Connected to database: $dbName\n\n";

    // Check all tables
    $tables = ['users', 'tokens', 'email_otps', 'user_sessions'];
    $allTablesExist = true;

    echo "Checking tables:\n";
    echo str_repeat("-", 50) . "\n";

    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' does NOT exist\n";
            $allTablesExist = false;
        }
    }

    if ($allTablesExist) {
        // Show users table structure
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "USERS TABLE STRUCTURE:\n";
        echo str_repeat("=", 50) . "\n";
        $result = $pdo->query("DESCRIBE users");
        while ($row = $result->fetch()) {
            echo sprintf("%-15s %-20s %-10s\n", $row['Field'], $row['Type'], $row['Key']);
        }

        // Show users count
        echo "\n" . str_repeat("-", 50) . "\n";
        $result = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $result->fetchColumn();
        echo "Total users: $count\n";

        if ($count > 0) {
            echo "\nSample users:\n";
            echo str_repeat("-", 50) . "\n";
            $result = $pdo->query("SELECT id, username, email FROM users LIMIT 5");
            while ($row = $result->fetch()) {
                echo sprintf(
                    "ID: %d | Username: %s | Email: %s\n",
                    $row['id'],
                    $row['username'],
                    $row['email']
                );
            }
        }

        // Show tokens count
        echo "\n" . str_repeat("=", 50) . "\n";
        $result = $pdo->query("SELECT COUNT(*) as count FROM tokens");
        $tokenCount = $result->fetchColumn();
        echo "Active tokens: $tokenCount\n";

        // Show OTPs count
        $result = $pdo->query("SELECT COUNT(*) as count FROM email_otps");
        $otpCount = $result->fetchColumn();
        echo "Email OTPs: $otpCount\n";

        // Show user sessions count
        $result = $pdo->query("SELECT COUNT(*) as count FROM user_sessions");
        $sessionCount = $result->fetchColumn();
        echo "Active user sessions: $sessionCount\n";

        echo "\n" . str_repeat("=", 50) . "\n";
        echo "✓ Database setup is complete!\n\n";
        echo "You can now test login with:\n";
        echo "  Email: test@example.com\n";
        echo "  Password: 123456\n";

    } else {
        echo "\n✗ Some tables are missing!\n";
        echo "\nPlease run the SQL schema file in phpMyAdmin:\n";
        echo "Backend/database/users_schema.sql\n";
    }

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";

    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "The database 'sams_rbac_backend' does not exist yet.\n\n";
        echo "SETUP INSTRUCTIONS:\n";
        echo str_repeat("=", 50) . "\n";
        echo "1. Open phpMyAdmin: http://localhost/phpmyadmin\n";
        echo "2. Click 'Import' in the top menu\n";
        echo "3. Choose file: Backend/database/users_schema.sql\n";
        echo "4. Click 'Go' button\n";
        echo "5. Run this script again to verify\n";
    }
}
?>