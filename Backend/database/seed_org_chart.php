<?php
// Backend/database/seed_org_chart.php
// Self-contained script: seeds a full 3-level org chart hierarchy under the existing admin.
require_once __DIR__ . "/../config/db.php";

// The bcrypt hash for the password "password" (cost 10)
$pw = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

try {
    // -------------------------------------------------------
    // 0. Ensure departments 1-8 have the names we rely on
    // -------------------------------------------------------
    $pdo->exec("
        INSERT IGNORE INTO departments (id, name) VALUES
        (1, 'Operations'),
        (2, 'Human Resources'),
        (3, 'Information Technology'),
        (4, 'Engineering'),
        (5, 'Finance'),
        (6, 'Marketing'),
        (7, 'Sales'),
        (8, 'Customer Support');
    ");
    echo "Departments ensured.\n";

    // -------------------------------------------------------
    // 1. Update Michael Scott (id=1) to be CEO
    // -------------------------------------------------------
    $pdo->exec("
        UPDATE users SET
            role_id = 2,
            department_id = 1,
            job_title = 'CEO',
            manager_id = NULL,
            username = COALESCE(NULLIF(username,''), 'michael.scott')
        WHERE id = 1;
    ");
    echo "Michael Scott set as CEO (id=1).\n";

    // -------------------------------------------------------
    // 2. Insert Directors (report to Michael Scott, id=1)
    // -------------------------------------------------------
    $directors = [
        // [username, full_name, email, dept_id, job_title]
        ['sarah.williams', 'Sarah Williams', 'sarah.williams@sams.com', 1, 'Director of Operations'],
        ['david.martinez', 'David Martinez', 'david.martinez@sams.com', 2, 'Director of HR'],
        ['thomas.anderson', 'Thomas Anderson', 'thomas.anderson@sams.com', 3, 'Director of IT'],
        ['christopher.brown', 'Christopher Brown', 'christopher.brown@sams.com', 4, 'Director of Engineering'],
        ['daniel.wilson', 'Daniel Wilson', 'daniel.wilson@sams.com', 5, 'Director of Finance'],
        ['matthew.garcia', 'Matthew Garcia', 'matthew.garcia@sams.com', 6, 'Director of Marketing'],
        ['andrew.miller', 'Andrew Miller', 'andrew.miller@sams.com', 7, 'Director of Sales'],
        ['joshua.harris', 'Joshua Harris', 'joshua.harris@sams.com', 8, 'Director of Customer Support'],
    ];

    $stmtDir = $pdo->prepare("
        INSERT IGNORE INTO users (username, full_name, email, password, role_id, department_id, job_title, manager_id)
        VALUES (?, ?, ?, ?, 3, ?, ?, 1)
    ");
    foreach ($directors as $d) {
        $stmtDir->execute([$d[0], $d[1], $d[2], $pw, $d[3], $d[4]]);
    }
    echo "8 Directors inserted.\n";

    // -------------------------------------------------------
    // 3. Insert Managers (report to their director by email)
    // -------------------------------------------------------
    function getIdByEmail(PDO $pdo, string $email): ?int
    {
        $s = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $s->execute([$email]);
        $row = $s->fetch();
        return $row ? (int) $row['id'] : null;
    }

    $managers = [
        // [username, full_name, email, dept_id, job_title, director_email]
        // Operations
        ['ryan.thompson', 'Ryan Thompson', 'ryan.thompson@sams.com', 1, 'Operations Manager', 'sarah.williams@sams.com'],
        ['lauren.scott', 'Lauren Scott', 'lauren.scott@sams.com', 1, 'Process Manager', 'sarah.williams@sams.com'],
        // HR
        ['brandon.king', 'Brandon King', 'brandon.king@sams.com', 2, 'HR Manager', 'david.martinez@sams.com'],
        ['stephanie.green', 'Stephanie Green', 'stephanie.green@sams.com', 2, 'Talent Manager', 'david.martinez@sams.com'],
        // IT
        ['kevin.baker', 'Kevin Baker', 'kevin.baker@sams.com', 3, 'IT Manager', 'thomas.anderson@sams.com'],
        ['rachel.adams', 'Rachel Adams', 'rachel.adams@sams.com', 3, 'Systems Manager', 'thomas.anderson@sams.com'],
        // Engineering
        ['justin.nelson', 'Justin Nelson', 'justin.nelson@sams.com', 4, 'Engineering Manager', 'christopher.brown@sams.com'],
        ['megan.carter', 'Megan Carter', 'megan.carter@sams.com', 4, 'QA Manager', 'christopher.brown@sams.com'],
        // Finance
        ['tyler.mitchell', 'Tyler Mitchell', 'tyler.mitchell@sams.com', 5, 'Finance Manager', 'daniel.wilson@sams.com'],
        ['samantha.perez', 'Samantha Perez', 'samantha.perez@sams.com', 5, 'Accounting Manager', 'daniel.wilson@sams.com'],
        // Marketing
        ['nathan.roberts', 'Nathan Roberts', 'nathan.roberts@sams.com', 6, 'Marketing Manager', 'matthew.garcia@sams.com'],
        ['victoria.turner', 'Victoria Turner', 'victoria.turner@sams.com', 6, 'Brand Manager', 'matthew.garcia@sams.com'],
        // Sales
        ['eric.phillips', 'Eric Phillips', 'eric.phillips@sams.com', 7, 'Sales Manager', 'andrew.miller@sams.com'],
        ['brittany.campbell', 'Brittany Campbell', 'brittany.campbell@sams.com', 7, 'Account Manager', 'andrew.miller@sams.com'],
        // Customer Support
        ['aaron.parker', 'Aaron Parker', 'aaron.parker@sams.com', 8, 'Support Manager', 'joshua.harris@sams.com'],
        ['kayla.evans', 'Kayla Evans', 'kayla.evans@sams.com', 8, 'CX Manager', 'joshua.harris@sams.com'],
    ];

    $stmtMgr = $pdo->prepare("
        INSERT IGNORE INTO users (username, full_name, email, password, role_id, department_id, job_title, manager_id)
        VALUES (?, ?, ?, ?, 3, ?, ?, ?)
    ");
    foreach ($managers as $m) {
        $dirId = getIdByEmail($pdo, $m[5]);
        $stmtMgr->execute([$m[0], $m[1], $m[2], $pw, $m[3], $m[4], $dirId]);
    }
    echo "16 Managers inserted.\n";

    // -------------------------------------------------------
    // 4. Insert Team Members (report to their manager by email)
    // -------------------------------------------------------
    $members = [
        // Operations
        ['jacob.edwards', 'Jacob Edwards', 'jacob.edwards@sams.com', 1, 'Operations Analyst', 'ryan.thompson@sams.com'],
        ['olivia.collins', 'Olivia Collins', 'olivia.collins@sams.com', 1, 'Coordinator', 'ryan.thompson@sams.com'],
        ['ethan.stewart', 'Ethan Stewart', 'ethan.stewart@sams.com', 1, 'Process Analyst', 'lauren.scott@sams.com'],
        ['sophia.morris', 'Sophia Morris', 'sophia.morris@sams.com', 1, 'Logistics Specialist', 'lauren.scott@sams.com'],
        // HR
        ['mason.rogers', 'Mason Rogers', 'mason.rogers@sams.com', 2, 'HR Specialist', 'brandon.king@sams.com'],
        ['isabella.reed', 'Isabella Reed', 'isabella.reed@sams.com', 2, 'Recruiter', 'brandon.king@sams.com'],
        ['logan.cook', 'Logan Cook', 'logan.cook@sams.com', 2, 'HR Coordinator', 'stephanie.green@sams.com'],
        ['ava.morgan', 'Ava Morgan', 'ava.morgan@sams.com', 2, 'Training Specialist', 'stephanie.green@sams.com'],
        // IT
        ['lucas.bell', 'Lucas Bell', 'lucas.bell@sams.com', 3, 'IT Specialist', 'kevin.baker@sams.com'],
        ['emma.murphy', 'Emma Murphy', 'emma.murphy@sams.com', 3, 'Network Engineer', 'kevin.baker@sams.com'],
        ['aiden.bailey', 'Aiden Bailey', 'aiden.bailey@sams.com', 3, 'Security Analyst', 'rachel.adams@sams.com'],
        ['mia.rivera', 'Mia Rivera', 'mia.rivera@sams.com', 3, 'Systems Admin', 'rachel.adams@sams.com'],
        // Engineering
        ['jackson.cooper', 'Jackson Cooper', 'jackson.cooper@sams.com', 4, 'Software Engineer', 'justin.nelson@sams.com'],
        ['charlotte.richardson', 'Charlotte Richardson', 'charlotte.richardson@sams.com', 4, 'Backend Developer', 'justin.nelson@sams.com'],
        ['liam.cox', 'Liam Cox', 'liam.cox@sams.com', 4, 'QA Engineer', 'megan.carter@sams.com'],
        ['amelia.howard', 'Amelia Howard', 'amelia.howard@sams.com', 4, 'Test Engineer', 'megan.carter@sams.com'],
        // Finance
        ['noah.ward', 'Noah Ward', 'noah.ward@sams.com', 5, 'Financial Analyst', 'tyler.mitchell@sams.com'],
        ['harper.torres', 'Harper Torres', 'harper.torres@sams.com', 5, 'Budget Analyst', 'tyler.mitchell@sams.com'],
        ['carter.peterson', 'Carter Peterson', 'carter.peterson@sams.com', 5, 'Accountant', 'samantha.perez@sams.com'],
        ['ella.gray', 'Ella Gray', 'ella.gray@sams.com', 5, 'Payroll Specialist', 'samantha.perez@sams.com'],
        // Marketing
        ['wyatt.ramirez', 'Wyatt Ramirez', 'wyatt.ramirez@sams.com', 6, 'Marketing Analyst', 'nathan.roberts@sams.com'],
        ['aria.james', 'Aria James', 'aria.james@sams.com', 6, 'Content Writer', 'nathan.roberts@sams.com'],
        ['grayson.watson', 'Grayson Watson', 'grayson.watson@sams.com', 6, 'Social Media Mgr', 'victoria.turner@sams.com'],
        ['scarlett.brooks', 'Scarlett Brooks', 'scarlett.brooks@sams.com', 6, 'Designer', 'victoria.turner@sams.com'],
        // Sales
        ['luke.kelly', 'Luke Kelly', 'luke.kelly@sams.com', 7, 'Sales Executive', 'eric.phillips@sams.com'],
        ['chloe.sanders', 'Chloe Sanders', 'chloe.sanders@sams.com', 7, 'Sales Rep', 'eric.phillips@sams.com'],
        ['owen.price', 'Owen Price', 'owen.price@sams.com', 7, 'Account Executive', 'brittany.campbell@sams.com'],
        ['lily.bennett', 'Lily Bennett', 'lily.bennett@sams.com', 7, 'Sales Rep', 'brittany.campbell@sams.com'],
        // Customer Support
        ['caleb.wood', 'Caleb Wood', 'caleb.wood@sams.com', 8, 'Support Agent', 'aaron.parker@sams.com'],
        ['zoe.barnes', 'Zoe Barnes', 'zoe.barnes@sams.com', 8, 'Support Agent', 'aaron.parker@sams.com'],
        ['elijah.ross', 'Elijah Ross', 'elijah.ross@sams.com', 8, 'Customer Advocate', 'kayla.evans@sams.com'],
        ['grace.henderson', 'Grace Henderson', 'grace.henderson@sams.com', 8, 'CX Specialist', 'kayla.evans@sams.com'],
    ];

    $stmtEmp = $pdo->prepare("
        INSERT IGNORE INTO users (username, full_name, email, password, role_id, department_id, job_title, manager_id)
        VALUES (?, ?, ?, ?, 4, ?, ?, ?)
    ");
    foreach ($members as $e) {
        $mgrId = getIdByEmail($pdo, $e[5]);
        $stmtEmp->execute([$e[0], $e[1], $e[2], $pw, $e[3], $e[4], $mgrId]);
    }
    echo "32 Team members inserted.\n";

    // -------------------------------------------------------
    // 5. Summary
    // -------------------------------------------------------
    $total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "\n=== DONE ===\n";
    echo "Total users in database: $total\n";
    echo "Password for all new accounts: 'password'\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>