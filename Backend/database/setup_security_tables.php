<?php
try {
    require_once __DIR__ . '/../config/db.php';
    echo "Connected to database: " . $db . "\n";

    // 1. Ensure 'users' table has 'username' column
    echo "Checking 'users' table for 'username' column...\n";
    
    // Check if table users exists first to be safe, though it should.
    $tableExists = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;
    
    if (!$tableExists) {
        // If users table is missing, we might be in trouble as other parts depend on it.
        // But let's create it with the provided schema combined with what we know is needed roughly?
        // Actually, better to just warn or create basic one.
        echo "WARNING: 'users' table does not exist! Creating it with basic schema from request...\n";
        // Note: This might lack columns used in login.php like role_id. 
        // But the user request specifically asked to add this code.
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        echo "Created 'users' table.\n";
    }

    // Now check for username column
    $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'username'");
    if (!$stmt->fetch()) {
        echo "Adding 'username' column to 'users' table...\n";
        // Check if id exists to place after it, otherwise just add it
        $idCheck = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'id'");
        if ($idCheck->fetch()) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `username` VARCHAR(255) NOT NULL AFTER `id`");
        } else {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `username` VARCHAR(255) NOT NULL");
        }
        
        // Add unique index if not exists (hard to check index verify simply, so using try-catch or just add)
        try {
             $pdo->exec("ALTER TABLE `users` ADD UNIQUE KEY `username` (`username`)");
        } catch (Exception $e) {
            echo "Index for username might already exist or failed: " . $e->getMessage() . "\n";
        }
        
        echo "Column 'username' added successfully!\n";
    } else {
        echo "Column 'username' already exists.\n";
    }

    // 2. Create 'tokens' table
    echo "Creating 'tokens' table if not exists...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `tokens` (
        `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
        `user_id` BIGINT(20) NOT NULL,
        `token` VARCHAR(255) NOT NULL,
        `expiry` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "'tokens' table checked/created.\n";

    echo "Database setup complete!\n";

} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
