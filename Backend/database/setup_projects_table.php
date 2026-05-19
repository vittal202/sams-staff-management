<?php
// Backend/database/setup_projects_table.php
require_once __DIR__ . "/../config/db.php";

try {
    echo "Creating 'projects' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `projects` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `progress` INT DEFAULT 0,
        `status` ENUM('Planning', 'In Progress', 'On Hold', 'Completed') DEFAULT 'Planning',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'projects' created successfully.\n";

    // Check if table is empty before seeding
    $stmt = $pdo->query("SELECT COUNT(*) FROM projects");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        echo "Seeding initial projects...\n";
        $seedSql = "INSERT INTO projects (name, description, progress, status) VALUES 
            ('Project Alpha', 'Initial research and development phase for core modules.', 45, 'In Progress'),
            ('Beta Initiative', 'Expansion of the analytics dashboard features.', 20, 'Planning'),
            ('Security Audit 2026', 'Comprehensive review of authentication and encryption protocols.', 100, 'Completed');";
        $pdo->exec($seedSql);
        echo "Seed data inserted successfully.\n";
    } else {
        echo "Table 'projects' already contains data. Skipping seed.\n";
    }

    echo "\nSetup complete.\n";

} catch (PDOException $e) {
    die("Error setting up projects table: " . $e->getMessage());
}
?>