<?php
// Backend/api/reset_org_data.php
require_once __DIR__ . "/../config/db.php";

echo "<h1>Resetting Org Chart Data...</h1>";

try {
    // 1. Disable Foreign Key Checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 2. Truncate Users Table
    $pdo->exec("TRUNCATE TABLE users");
    echo "<li>Users table truncated.</li>";

    // 3. Reset Auto Increment and Ensure job_title column exists
    $pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1");
    try {
        // Attempt to add the column, ignore if it exists
        $pdo->exec("ALTER TABLE users ADD COLUMN job_title VARCHAR(100) AFTER email");
    } catch (Exception $e) { /* Column likely exists */
    }

    // 4. Insert CEO (ID: 1)
    $password = password_hash('password123', PASSWORD_DEFAULT);
    // Updated Insert Statement to include job_title
    $stmt = $pdo->prepare("INSERT INTO users (id, full_name, email, job_title, password, role_id, manager_id, department_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    // CEO
    $stmt->execute([1, 'Erica Romaguera', 'ceo@sams.com', 'CEO', $password, 2, null, 1]);
    echo "<li>CEO Inserted (ID: 1).</li>";

    // 5. Insert 2 Managers (IDs: 2-3)
    $managers = [
        [2, 'Russell Ross', 'russell@sams.com', 'Staff Director', 3, 1, 2],
        [3, 'David Sellner', 'david@sams.com', 'Volunteer Director', 3, 1, 2],
    ];

    foreach ($managers as $mgr) {
        $stmt->execute([$mgr[0], $mgr[1], $mgr[2], $mgr[3], $password, $mgr[4], $mgr[5], $mgr[6]]);
        echo "<li>Manager Inserted: {$mgr[1]}.</li>";
    }

    // 6. Insert 30 Employees (IDs: 6-36)
    // List of requested titles
    $titles = [
        'Financer',
        'Marketer',
        'Sales Manager',
        'HR Specialist',
        'IT Consultant',
        'Developer',
        'Designer',
        'Copywriter',
        'Analyst',
        'Coordinator',
        'Executive Assistant'
    ];

    $employees = [
        [6, 'Matteo Gobeaux', 'matteo@sams.com', 4, 3, 3],
        [7, 'Bernadine Godsell', 'bernadine@sams.com', 4, 2, 2],
        [8, 'Birgitta Rosoni', 'birgitta@sams.com', 4, 2, 2],
        [9, 'Caleigh Jerde', 'caleigh@sams.com', 4, 3, 2],
        [10, 'Bartholemy Durgan', 'bartholemy@sams.com', 4, 3, 3],
        [11, 'Laney Christmas', 'laney@sams.com', 4, 3, 2],
        [12, 'Bernelle Cubley', 'bernelle@sams.com', 4, 3, 3],
        [13, 'Kendra Loud', 'kendra@sams.com', 4, 2, 2],
        [14, 'Jonis Thring', 'jonis@sams.com', 4, 2, 2],
        [15, 'Holmes Dever', 'holmes@sams.com', 4, 2, 2],
        [16, 'Sarah Connor', 'sarah.c@sams.com', 4, 2, 2],
        [17, 'John Wick', 'john.w@sams.com', 4, 2, 2],
        [18, 'Ellen Ripley', 'ellen.r@sams.com', 4, 2, 2],
        [19, 'Marty McFly', 'marty.m@sams.com', 4, 3, 2],
        [20, 'Doc Brown', 'doc.b@sams.com', 4, 3, 2],
        [21, 'Luke Skywalker', 'luke.s@sams.com', 4, 3, 2],
        [22, 'Han Solo', 'han.s@sams.com', 4, 3, 2],
        [23, 'Leia Organa', 'leia.o@sams.com', 4, 3, 3],
        [24, 'Tony Stark', 'tony.s@sams.com', 4, 3, 3],
        [25, 'Steve Rogers', 'steve.r@sams.com', 4, 3, 3]
    ];

    foreach ($employees as $i => $emp) {
        // Assign a random title from the list, cycling through if needed
        $title = $titles[$i % count($titles)];

        $stmt->execute([$emp[0], $emp[1], $emp[2], $title, $password, $emp[3], $emp[4], $emp[5]]);
        echo "<li>Employee Inserted: {$emp[1]} - {$title}.</li>";
    }

    // Re-enable Foreign Key Checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "<h3 style='color: green;'>✓ Database Reset Successfully!</h3>";
    echo "<p>1 CEO, 2 Managers, 20 Employees created (Total 23) with Job Titles.</p>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
}
?>