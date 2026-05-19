<?php
require_once "../../Backend/auth/auth_check.php";
require_once "../../Backend/config/db.php";
checkAccess(); // Ensure user is logged in

// Pagination Logic
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter Logic
$deptFilter = isset($_GET['dept']) ? $_GET['dept'] : null;
$roleIdFilter = isset($_GET['role_id']) && $_GET['role_id'] !== '' ? (int)$_GET['role_id'] : null;
$jobTitleFilter = isset($_GET['job_title']) && $_GET['job_title'] !== '' ? $_GET['job_title'] : null;

$conditions = [];
$params = [];

if ($deptFilter) {
    $conditions[] = "d.name = :dept_name";
    $params[':dept_name'] = $deptFilter;
}
if ($roleIdFilter) {
    $conditions[] = "u.role_id = :role_id";
    $params[':role_id'] = $roleIdFilter;
}
if ($jobTitleFilter) {
    $conditions[] = "COALESCE(u.job_title, r.role_name) = :job_title";
    $params[':job_title'] = $jobTitleFilter;
}

$whereClause = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

// Fetch total users for pagination info (with filter)
$countQuery = "SELECT COUNT(*) FROM users u LEFT JOIN roles r ON u.role_id = r.id LEFT JOIN departments d ON u.department_id = d.id $whereClause";
$totalStmt = $pdo->prepare($countQuery);
foreach ($params as $key => $val) {
    $totalStmt->bindValue($key, $val);
}
$totalStmt->execute();
$totalUsers = $totalStmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

// Fetch users with their roles and departments
$query = "
    SELECT u.*, COALESCE(u.job_title, r.role_name) AS role_name, d.name as dept_name, d.color_class as dept_color
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    LEFT JOIN departments d ON u.department_id = d.id
    $whereClause
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$employees = $stmt->fetchAll();

// Fetch current logged-in user's data for sidebar
$currentUserId = $_SESSION['raw_user_id'];
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$currentUserId]);
$currentUser = $userStmt->fetch();

// Color mapping for departments
$colorMap = [
    'indigo' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border-indigo-100 dark:border-indigo-800',
    'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800',
    'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-100 dark:border-amber-800',
    'rose' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border-rose-100 dark:border-rose-800',
    'orange' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 border-orange-100 dark:border-orange-800',
    'blue' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-100 dark:border-blue-800',
    'cyan' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400 border-cyan-100 dark:border-cyan-800',
    'teal' => 'bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400 border-teal-100 dark:border-teal-800',
    'purple' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border-purple-100 dark:border-purple-800',
    'green' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-100 dark:border-green-800',
    'slate' => 'bg-slate-50 text-slate-700 dark:bg-slate-900/30 dark:text-slate-400 border-slate-100 dark:border-slate-800',
];
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Staff Directory Management Table</title>
    <!-- Theme Initialization Script -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#16439c",
                        "accent-gold": "#d4af37",
                        "background-light": "#f6f6f8",
                        "background-dark": "#111621",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    <?php require_once "../../Backend/auth/fetch_user_preferences.php"; ?>
    <?php if ($compactView): ?>
        <style>
            /* Compact View Overrides */
            .p-8 {
                padding: 1.25rem !important;
            }

            .p-6 {
                padding: 1rem !important;
            }

            .px-8 {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .px-6 {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            .py-4 {
                padding-top: 0.5rem !important;
                padding-bottom: 0.5rem !important;
            }

            .gap-6 {
                gap: 1rem !important;
            }

            .gap-8 {
                gap: 1.25rem !important;
            }

            .mb-8 {
                margin-bottom: 1rem !important;
            }

            aside .py-2\.5 {
                padding-top: 0.4rem !important;
                padding-bottom: 0.4rem !important;
            }

            aside .gap-3 {
                gap: 0.5rem !important;
            }
        </style>
    <?php endif; ?>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-[#0e121b] dark:text-white antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside
            class="w-64 border-r border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex flex-col justify-between p-4">
            <div class="flex flex-col gap-8">
                <div class="flex items-center gap-3 px-2">
                    <div class="bg-primary p-2 rounded-lg text-white">
                        <span class="material-symbols-outlined">account_tree</span>
                    </div>
                    <h1 class="text-[#0e121b] dark:text-white text-lg font-bold tracking-tight">OrgChart Pro</h1>
                </div>
                <nav class="flex flex-col gap-1">
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        href="../dashboard/index.php">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        href="../Projects/index.php">
                        <span class="material-symbols-outlined">assignment</span>
                        <p class="text-sm">Projects</p>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-semibold"
                        href="../Employees/index.php">
                        <span class="material-symbols-outlined">group</span>
                        <p class="text-sm">Staff</p>
                    </a>

                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        href="../Settings/index.php">
                        <span class="material-symbols-outlined">settings</span>
                        <p class="text-sm">Settings</p>
                    </a>
                </nav>
            </div>
            <!-- User Profile & Logout -->
            <div class="mt-2">
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700/60 bg-gradient-to-br from-white to-slate-50 dark:from-slate-800/80 dark:to-slate-900/80 shadow-sm">
                    <!-- Top accent bar -->
                    <div class="h-0.5 w-full bg-gradient-to-r from-primary via-blue-400 to-primary/30"></div>
                    <div class="p-3">
                        <!-- User Info Row -->
                        <div class="flex items-center gap-3 mb-3">
                            <!-- Avatar with online dot -->
                            <div class="relative shrink-0">
                                <div class="size-9 rounded-full bg-cover bg-center border-2 border-primary/30 shadow"
                                    style='background-image: url("<?php echo !empty($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['full_name'] ?? 'User') . '&background=16439c&color=fff'; ?>");'>
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 size-2.5 bg-emerald-400 border-2 border-white dark:border-slate-800 rounded-full"></span>
                            </div>
                            <!-- Name & Role -->
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold truncate text-slate-800 dark:text-white leading-tight">
                                    <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
                                </p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate font-medium">
                                    <?php echo htmlspecialchars($_SESSION['role_name'] ?? 'SAMS Corporate System'); ?>
                                </p>
                            </div>
                        </div>
                        <!-- Sign Out Button -->
                        <button onclick="showLogoutModal()"
                            id="logoutBtn"
                            class="group flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-500 dark:text-rose-400 text-xs font-bold border border-rose-100 dark:border-rose-500/20 hover:bg-rose-500 hover:text-white dark:hover:bg-rose-500 dark:hover:text-white hover:border-rose-500 hover:shadow-lg hover:shadow-rose-500/20 transition-all duration-200 active:scale-95">
                            <span class="material-symbols-outlined text-base transition-transform duration-200 group-hover:-translate-x-0.5">logout</span>
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </aside>
        <main class="flex-1 flex flex-col overflow-hidden">
            <header
                class="h-16 border-b border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex items-center justify-between px-8">
                <div class="flex-1 max-w-md">
                    <div class="relative group">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input id="employeeSearchInput"
                            class="w-full bg-[#f0f2f5] dark:bg-slate-800 border-none rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary/50 transition-all"
                            placeholder="Search employees, roles, or ID..." type="text" />
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <?php if ($_SESSION['role_id'] == 2): ?>
                    <button id="addEmployeeBtn"
                        class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 hover:bg-blue-800 transition-colors">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Add Member
                    </button>
                    <?php endif; ?>
                    <div class="h-6 w-[1px] bg-slate-200 dark:bg-gray-700 mx-2"></div>
                    <div class="relative">
                        <button id="notifBtn"
                            class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors relative">
                            <span class="material-symbols-outlined">notifications</span>
                            <span id="notifBadge"
                                class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-800"></span>
                        </button>

                        <!-- Notification Dropdown -->
                        <div id="notifPanel"
                            class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-[#e8ebf3] dark:border-gray-800 rounded-2xl shadow-2xl z-[100] overflow-hidden scale-95 opacity-0 transition-all duration-200 origin-top-right">
                            <div
                                class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                                <h4 class="font-bold text-sm tracking-tight">Notifications</h4>
                                <span
                                    class="text-[10px] font-bold text-primary uppercase bg-primary/10 px-2 py-0.5 rounded">Matrix
                                    Feed</span>
                            </div>
                            <div id="notifList"
                                class="max-h-[400px] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                                <div class="p-8 text-center text-slate-400">
                                    <div
                                        class="animate-spin size-5 border-2 border-slate-200 border-t-primary rounded-full mx-auto mb-2">
                                    </div>
                                    <p class="text-xs">Synchronizing...</p>
                                </div>
                            </div>
                            <div class="p-3 border-t border-slate-100 dark:border-slate-800 text-center">
                                <a href="../Notifications/index.php"
                                    class="text-xs font-bold text-primary hover:underline">View All
                                    Intelligence</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="flex-1 overflow-auto bg-white dark:bg-slate-950 p-8">
                <div class="max-w-7xl mx-auto">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">Staff
                                Directory</h2>
                            <p class="text-sm text-slate-500 mt-1">Manage all organizational members and their access
                                levels.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <?php
                            $roleFilter = isset($_GET['role_id']) && $_GET['role_id'] !== '' ? (int)$_GET['role_id'] : null;
                            $roles = $pdo->query("SELECT id, role_name FROM roles ORDER BY id")->fetchAll();
                            $allDepts = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
                            ?>
                            <!-- Department Filter -->
                            <select
                                onchange="
                                    var url='index.php?dept='+encodeURIComponent(this.value);
                                    <?php if ($roleFilter): ?>url+='&role_id=<?php echo $roleFilter; ?>';<?php endif; ?>
                                    window.location.href=url;"
                                class="bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-lg text-sm text-slate-600 focus:ring-primary px-2 py-1.5">
                                <option value="">All Departments</option>
                                <?php foreach ($allDepts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>"
                                        <?php echo ($deptFilter === $d['name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <!-- Role / Job Title Filter -->
                            <?php
                            $jobTitleFilter = isset($_GET['job_title']) ? $_GET['job_title'] : '';
                            $jobTitles = $pdo->query("
                                SELECT DISTINCT COALESCE(u.job_title, r.role_name) AS title
                                FROM users u
                                LEFT JOIN roles r ON u.role_id = r.id
                                WHERE COALESCE(u.job_title, r.role_name) IS NOT NULL
                                ORDER BY title ASC
                            ")->fetchAll(PDO::FETCH_COLUMN);
                            ?>
                            <select
                                onchange="
                                    var url='index.php?job_title='+encodeURIComponent(this.value);
                                    <?php if ($deptFilter): ?>url+='&dept='+encodeURIComponent('<?php echo addslashes($deptFilter); ?>');
                                    <?php endif; ?>
                                    window.location.href=url;"
                                class="bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-lg text-sm text-slate-600 focus:ring-primary px-2 py-1.5">
                                <option value="">All Roles</option>
                                <?php foreach ($jobTitles as $title): ?>
                                    <option value="<?php echo htmlspecialchars($title); ?>"
                                        <?php echo ($jobTitleFilter === $title) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button onclick="window.location.href='index.php'"
                                class="p-2 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-500 hover:bg-slate-50 transition-colors"
                                title="Clear Filters">
                                <span class="material-symbols-outlined text-xl">filter_list_off</span>
                            </button>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-slate-900 border border-[#e8ebf3] dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-slate-50 dark:bg-slate-800/50 border-b border-[#e8ebf3] dark:border-gray-800">
                                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Employee Name</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Role</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Department</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Access Level</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e8ebf3] dark:divide-gray-800">
                                <?php foreach ($employees as $emp): ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3 cursor-pointer group"
                                                onclick="showProfile(<?php echo $emp['id']; ?>)">
                                                <div
                                                    class="size-9 rounded-full bg-slate-200 group-hover:bg-primary/10 flex items-center justify-center text-slate-500 group-hover:text-primary text-xs font-bold transition-colors">
                                                    <?php echo substr($emp['full_name'], 0, 2); ?>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-primary transition-colors">
                                                        <?php echo htmlspecialchars($emp['full_name']); ?>
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        <?php echo htmlspecialchars($emp['email']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                            <?php echo htmlspecialchars($emp['role_name'] ?? 'Employee'); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-medium border <?php echo $colorMap[$emp['dept_color']] ?? $colorMap['slate']; ?>">
                                                <?php echo htmlspecialchars($emp['dept_name'] ?? 'Unassigned'); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                            <?php echo ($emp['role_id'] == 2) ? 'Owner' : (($emp['role_id'] == 3) ? 'Admin' : 'Editor'); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                                <span class="size-2 rounded-full bg-green-500"></span>
                                                Active
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="relative inline-block text-left">
                                                <button onclick="toggleEmpDropdown(event, <?php echo $emp['id']; ?>)"
                                                    class="text-slate-400 hover:text-primary transition-colors p-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800">
                                                    <span class="material-symbols-outlined">more_horiz</span>
                                                </button>
                                                <!-- Dropdown Menu -->
                                                <div id="emp-dropdown-<?php echo $emp['id']; ?>"
                                                    class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-[#e8ebf3] dark:border-gray-800 rounded-xl shadow-xl z-50 overflow-hidden transform origin-top-right transition-all">
                                                    <div class="py-1">
                                                        <button onclick="showProfile(<?php echo $emp['id']; ?>)"
                                                            class="w-full text-left px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center gap-3">
                                                            <span
                                                                class="material-symbols-outlined text-base">visibility</span>
                                                            View Profile
                                                        </button>
                                                        <div class="h-[1px] bg-slate-100 dark:bg-slate-800 mx-3"></div>
                                                        <button
                                                            onclick="openDeleteEmpModal(<?php echo $emp['id']; ?>, '<?php echo addslashes($emp['full_name']); ?>')"
                                                            class="w-full text-left px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors flex items-center gap-3">
                                                            <span class="material-symbols-outlined text-base">delete</span>
                                                            Delete Employee
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div
                            class="px-6 py-4 border-t border-[#e8ebf3] dark:border-gray-800 flex items-center justify-between">
                            <p class="text-sm text-slate-500">Showing <span
                                    class="font-medium text-slate-900 dark:text-white"><?php echo $offset + 1; ?></span>
                                to <span
                                    class="font-medium text-slate-900 dark:text-white"><?php echo min($offset + $limit, $totalUsers); ?></span>
                                of <span
                                    class="font-medium text-slate-900 dark:text-white"><?php echo $totalUsers; ?></span>
                                employees</p>
                            <div class="flex items-center gap-2">
                                <?php
                                $queryParams = $_GET;
                                unset($queryParams['page']);
                                $queryString = http_build_query($queryParams);
                                $baseLink = '?' . ($queryString ? $queryString . '&' : '') . 'page=';
                                ?>
                                <a href="<?php echo $baseLink . max(1, $page - 1); ?>"
                                    class="p-2 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-400 hover:bg-slate-50 transition-colors <?php echo ($page <= 1) ? 'pointer-events-none opacity-50' : ''; ?>">
                                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                                </a>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($totalPages > 5 && ($i > 3 && $i < $totalPages)): ?>
                                        <?php if ($i == 4): ?><span class="text-slate-400 mx-1">...</span><?php endif; ?>
                                        <?php continue; ?>
                                    <?php endif; ?>
                                    <a href="<?php echo $baseLink . $i; ?>"
                                        class="px-4 py-1.5 border <?php echo ($page == $i) ? 'border-primary bg-primary text-white' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50'; ?> rounded-lg text-sm font-medium"><?php echo $i; ?></a>
                                <?php endfor; ?>
                                <a href="<?php echo $baseLink . min($totalPages, $page + 1); ?>"
                                    class="p-2 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-400 hover:bg-slate-50 transition-colors <?php echo ($page >= $totalPages) ? 'pointer-events-none opacity-50' : ''; ?>">
                                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer
                class="h-12 border-t border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex items-center justify-between px-8 text-xs text-slate-500 font-medium">
                <div class="flex items-center gap-6">
                    <span class="flex items-center gap-2">
                        Total: <span class="text-slate-900 dark:text-white font-bold"><?php echo $totalUsers; ?>
                            Employees</span>
                    </span>
                    <span class="flex items-center gap-2">
                        Active: <span class="text-green-600 font-bold"><?php echo $totalUsers; ?></span>
                    </span>
                    <span class="flex items-center gap-2">
                        Pending Invites: <span class="text-amber-600 font-bold">0</span>
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <button class="flex items-center gap-1 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-base">download</span>
                        Export CSV
                    </button>
                    <div class="h-4 w-[1px] bg-slate-200 dark:bg-gray-700"></div>
                    <button class="flex items-center gap-1 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-base">print</span>
                        Print Directory
                    </button>
                </div>
            </footer>
        </main>
    </div>

    <!-- Add Employee Modal -->
    <?php if ($_SESSION['role_id'] == 2): ?>
    <div id="employeeModal"
        class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md p-8 shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Add New Employee</h3>
                <button id="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form class="space-y-4" action="../../Backend/api/add_employee.php" method="POST">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Full
                        Name</label>
                    <input type="text" name="full_name" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 outline-none"
                        placeholder="e.g. John Doe">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Email
                        Address</label>
                    <input type="email" name="email" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 outline-none"
                        placeholder="john@company.com">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Job Title</label>
                    <input type="text" name="job_title"
                        class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 outline-none"
                        placeholder="e.g. Lead Software Engineer">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Department</label>
                        <select name="department_id"
                            class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 outline-none">
                            <?php
                            $depts = $pdo->query("SELECT id, name FROM departments")->fetchAll();
                            foreach ($depts as $d)
                                echo "<option value='{$d['id']}'>{$d['name']}</option>";
                            ?>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Password</label>
                        <input type="password" name="password" required
                            class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 outline-none"
                            placeholder="Set password">
                    </div>
                </div>
                <div class="pt-4 flex gap-3">
                    <button type="button" id="cancelBtn"
                        class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-blue-800 transition-colors shadow-lg shadow-primary/25">Add
                        Member</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Delete Employee Confirmation Modal -->
    <div id="deleteEmpModal"
        class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md p-8 shadow-2xl transform scale-95 transition-transform duration-300 border border-slate-100 dark:border-slate-800 text-center">
            <div
                class="size-16 rounded-full bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center text-rose-500 mx-auto mb-6">
                <span class="material-symbols-outlined text-4xl">warning</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Remove Employee?</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-8">
                Are you sure you want to remove <span id="deleteEmpName"
                    class="font-bold text-slate-900 dark:text-white"></span> from the system? This will also reassign
                their subordinates to their manager.
            </p>
            <div class="flex gap-3">
                <button onclick="closeDeleteEmpModal()"
                    class="flex-1 py-3 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                <button id="confirmDeleteEmpBtn"
                    class="flex-1 py-3 bg-rose-500 text-white rounded-lg text-sm font-bold hover:bg-rose-600 transition-colors shadow-lg shadow-rose-500/25">Delete</button>
            </div>
        </div>
    </div>

    <!-- Employee Profile Modal -->
    <div id="profileModal"
        class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="h-32 bg-primary relative">
                <button onclick="closeProfile()" class="absolute top-4 right-4 text-white/70 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="px-8 pb-8 -mt-12 relative flex flex-col items-center">
                <div id="profImage"
                    class="size-24 rounded-full border-4 border-white dark:border-slate-900 bg-cover bg-center bg-slate-200 mb-4 shadow-lg">
                </div>
                <h3 id="profName" class="text-xl font-bold text-slate-900 dark:text-white">Loading...</h3>
                <p id="profRole" class="text-sm font-medium text-primary uppercase tracking-wider mb-6">Position</p>

                <div class="w-full space-y-4">
                    <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <span class="material-symbols-outlined text-slate-400">mail</span>
                        <div>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Email Address</p>
                            <p id="profEmail" class="text-sm text-slate-700 dark:text-slate-300">email@company.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <span class="material-symbols-outlined text-slate-400 font-variation-fill">domain</span>
                        <div>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Department</p>
                            <div class="flex items-center gap-2">
                                <span id="profDeptIcon" class="material-symbols-outlined text-sm">corporate_fare</span>
                                <p id="profDept" class="text-sm text-slate-700 dark:text-slate-300">Unassigned</p>
                            </div>
                        </div>
                    </div>

                    <!-- Handled Projects -->
                    <div>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-2">Handled Projects</p>
                        <div id="profProjects" class="space-y-2 max-h-44 overflow-y-auto pr-1">
                            <p class="text-xs text-slate-400">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex gap-3 w-full">
                    <button
                        class="flex-1 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-blue-800 transition-colors shadow-lg shadow-primary/25">Send
                        Message</button>
                    <button onclick="closeProfile()"
                        class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const profileModal = document.getElementById('profileModal');
        const profileContent = profileModal.querySelector('div');

        async function showProfile(id) {
            document.getElementById('profName').innerText = 'Loading...';
            profileModal.classList.remove('hidden');
            setTimeout(() => {
                profileModal.classList.replace('opacity-0', 'opacity-100');
                profileContent.classList.replace('scale-95', 'scale-100');
            }, 10);

            try {
                const response = await fetch(`../../Backend/api/get_employee_details.php?id=${id}`);
                const data = await response.json();
                if (data.error) throw new Error(data.error);

                document.getElementById('profName').innerText = data.full_name;
                document.getElementById('profRole').innerText = data.role_name;
                document.getElementById('profEmail').innerText = data.email;
                document.getElementById('profDept').innerText = data.dept_name || 'Unassigned';
                document.getElementById('profDeptIcon').innerText = data.icon || 'corporate_fare';

                // Use custom portraits for specific IDs, fallback to UI Avatars
                const customPortraits = {
                    2: '../assets/images/alexandra_sterling.png',
                    3: '../assets/images/marcus_vane.png',
                    85: '../assets/images/james_aris.png',
                    86: '../assets/images/sarah_loft.png',
                    87: '../assets/images/robert_chen.png',
                    89: '../assets/images/elena_rossi.png'
                };

                const imageUrl = customPortraits[id] || `https://ui-avatars.com/api/?name=${encodeURIComponent(data.full_name)}&background=random&size=128`;
                document.getElementById('profImage').style.backgroundImage = `url('${imageUrl}')`;

                // Load Handled Projects
                const profProjects = document.getElementById('profProjects');
                profProjects.innerHTML = '<p class="text-xs text-slate-400">Loading...</p>';
                try {
                    const projRes = await fetch(`../../Backend/api/get_employee_projects.php?id=${id}`);
                    const projects = await projRes.json();
                    if (!projects.length) {
                        profProjects.innerHTML = '<p class="text-xs text-slate-400 italic">No projects assigned.</p>';
                    } else {
                        const statusColors = {
                            'In Progress': 'bg-blue-100 text-blue-700',
                            'Planning':    'bg-amber-100 text-amber-700',
                            'Completed':   'bg-emerald-100 text-emerald-700',
                            'On Hold':     'bg-slate-100 text-slate-600',
                        };
                        const barColors = {
                            'In Progress': 'bg-primary',
                            'Planning':    'bg-amber-500',
                            'Completed':   'bg-emerald-500',
                            'On Hold':     'bg-slate-400',
                        };
                        profProjects.innerHTML = projects.map(p => `
                            <div class="flex items-center gap-3 p-2 bg-slate-50 dark:bg-slate-800 rounded-xl">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">${p.name}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="flex-1 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full ${barColors[p.status] || 'bg-slate-400'}" style="width:${p.progress}%"></div>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-400">${p.progress}%</span>
                                    </div>
                                </div>
                                <span class="px-1.5 py-0.5 text-[9px] font-bold rounded ${statusColors[p.status] || 'bg-slate-100 text-slate-600'} shrink-0">${p.status}</span>
                            </div>
                        `).join('');
                    }
                } catch(e) {
                    profProjects.innerHTML = '<p class="text-xs text-red-400">Failed to load projects.</p>';
                }
            } catch (err) {
                document.getElementById('profName').innerText = 'Error loading profile';
            }
        }

        function closeProfile() {
            profileModal.classList.replace('opacity-100', 'opacity-0');
            profileContent.classList.replace('scale-100', 'scale-95');
            setTimeout(() => { profileModal.classList.add('hidden'); }, 300);
        }

        profileModal.addEventListener('click', (e) => { if (e.target === profileModal) closeProfile(); });

        // Add Employee Modal logic — CEO only
        const modal = document.getElementById('employeeModal');
        if (modal) {
            const content = modal.querySelector('div');
            const btn = document.getElementById('addEmployeeBtn');
            const closeBtn = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const deptFilterValue = "<?php echo $deptFilter ?? ''; ?>";

            function openModal() {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.replace('opacity-0', 'opacity-100');
                    content.classList.replace('scale-95', 'scale-100');
                }, 10);

                if (deptFilterValue) {
                    const selectInfo = document.querySelector('select[name="department_id"]');
                    if (selectInfo) {
                        for (let i = 0; i < selectInfo.options.length; i++) {
                            if (selectInfo.options[i].text === deptFilterValue) {
                                selectInfo.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }
            }

            function closeModal() {
                modal.classList.replace('opacity-100', 'opacity-0');
                content.classList.replace('scale-100', 'scale-95');
                setTimeout(() => { modal.classList.add('hidden'); }, 300);
            }

            if (btn) btn.addEventListener('click', openModal);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
        }

        // Dropdown Logic
        function toggleEmpDropdown(event, id) {
            event.stopPropagation();
            const dropdown = document.getElementById(`emp-dropdown-${id}`);
            const isHidden = dropdown.classList.contains('hidden');

            // Close all other employee dropdowns
            document.querySelectorAll('[id^="emp-dropdown-"]').forEach(d => {
                if (d.id !== `emp-dropdown-${id}`) d.classList.add('hidden');
            });

            if (isHidden) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }

        // Close dropdowns on body click
        document.addEventListener('click', () => {
            document.querySelectorAll('[id^="emp-dropdown-"]').forEach(d => d.classList.add('hidden'));
        });

        // Delete Employee Logic
        let empToDeleteId = null;
        const deleteEmpModal = document.getElementById('deleteEmpModal');
        const deleteEmpContent = deleteEmpModal.querySelector('div');
        const confirmDeleteEmpBtn = document.getElementById('confirmDeleteEmpBtn');

        function openDeleteEmpModal(id, name) {
            empToDeleteId = id;
            document.getElementById('deleteEmpName').innerText = name;
            deleteEmpModal.classList.remove('hidden');
            setTimeout(() => {
                deleteEmpModal.classList.replace('opacity-0', 'opacity-100');
                deleteEmpContent.classList.replace('scale-95', 'scale-100');
            }, 10);
        }

        function closeDeleteEmpModal() {
            deleteEmpModal.classList.replace('opacity-100', 'opacity-0');
            deleteEmpContent.classList.replace('scale-100', 'scale-95');
            setTimeout(() => { deleteEmpModal.classList.add('hidden'); }, 300);
        }

        confirmDeleteEmpBtn.addEventListener('click', async () => {
            if (!empToDeleteId) return;

            const originalText = confirmDeleteEmpBtn.innerText;
            confirmDeleteEmpBtn.innerText = 'Deleting...';
            confirmDeleteEmpBtn.disabled = true;

            try {
                const response = await fetch('../../Backend/api/delete_employee_node.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ employee_id: empToDeleteId })
                });
                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to delete employee');
                    closeDeleteEmpModal();
                }
            } catch (err) {
                alert('Connection error');
                closeDeleteEmpModal();
            } finally {
                confirmDeleteEmpBtn.innerText = originalText;
                confirmDeleteEmpBtn.disabled = false;
            }
        });

        // Notification System
        const notifBtn = document.getElementById('notifBtn');
        const notifPanel = document.getElementById('notifPanel');
        const notifList = document.getElementById('notifList');
        const notifBadge = document.getElementById('notifBadge');
        let notifsLoaded = false;

        notifBtn.onclick = (e) => {
            e.stopPropagation();
            const isHidden = notifPanel.classList.contains('hidden');

            if (isHidden) {
                notifPanel.classList.remove('hidden');
                setTimeout(() => {
                    notifPanel.classList.replace('scale-95', 'scale-100');
                    notifPanel.classList.replace('opacity-0', 'opacity-100');
                }, 10);
                if (!notifsLoaded) fetchNotifications();
            } else {
                closeNotifs();
            }
        };

        function closeNotifs() {
            notifPanel.classList.replace('scale-100', 'scale-95');
            notifPanel.classList.replace('opacity-100', 'opacity-0');
            setTimeout(() => {
                notifPanel.classList.add('hidden');
            }, 200);
        }

        document.addEventListener('click', (e) => {
            if (!notifPanel.contains(e.target) && e.target !== notifBtn) {
                closeNotifs();
            }
        });

        async function fetchNotifications() {
            try {
                const response = await fetch('../../Backend/api/get_notifications.php');
                const data = await response.json();

                notifList.innerHTML = '';
                if (data.length === 0) {
                    notifList.innerHTML = '<p class="p-8 text-center text-slate-400 text-xs">No active signals detected.</p>';
                    return;
                }

                data.forEach(n => {
                    const item = document.createElement('div');
                    item.className = `p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer group ${!n.read ? 'bg-primary/5' : ''}`;

                    const icons = {
                        update: 'sync',
                        success: 'check_circle',
                        warning: 'warning',
                        security: 'shield'
                    };

                    item.innerHTML = `
                        <div class="flex gap-4 text-left">
                            <div class="size-8 rounded-lg flex items-center justify-center shrink-0 ${!n.read ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'}">
                                <span class="material-symbols-outlined text-[18px]">${icons[n.type] || 'notifications'}</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-1 gap-2">
                                    <h5 class="text-xs font-bold leading-tight group-hover:text-primary transition-colors">${n.title}</h5>
                                    <span class="text-[9px] font-medium text-slate-400 uppercase shrink-0">${n.time}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">${n.message}</p>
                            </div>
                        </div>
                    `;
                    notifList.appendChild(item);
                });

                notifsLoaded = true;
                notifBadge.classList.add('hidden'); // Clear badge when opened
            } catch (err) {
                notifList.innerHTML = '<p class="p-8 text-center text-rose-500 text-xs font-bold">Signal Interference: API Unreachable</p>';
            }
        }

        // --- Live Employee Search (all employees, not just current page) ---
        const searchInput = document.getElementById('employeeSearchInput');
        const tbody = document.querySelector('tbody');

        // Cache original server-rendered rows so we can restore on clear
        const originalRows = tbody.innerHTML;

        const colorMap = {
            indigo:  'bg-indigo-50 text-indigo-700 border-indigo-100',
            emerald: 'bg-emerald-50 text-emerald-700 border-emerald-100',
            amber:   'bg-amber-50 text-amber-700 border-amber-100',
            rose:    'bg-rose-50 text-rose-700 border-rose-100',
            orange:  'bg-orange-50 text-orange-700 border-orange-100',
            blue:    'bg-blue-50 text-blue-700 border-blue-100',
            cyan:    'bg-cyan-50 text-cyan-700 border-cyan-100',
            teal:    'bg-teal-50 text-teal-700 border-teal-100',
            purple:  'bg-purple-50 text-purple-700 border-purple-100',
            green:   'bg-green-50 text-green-700 border-green-100',
            slate:   'bg-slate-50 text-slate-700 border-slate-100',
        };

        function renderSearchRows(employees) {
            if (!employees.length) {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">No employees found.</td></tr>`;
                return;
            }
            tbody.innerHTML = employees.map(emp => {
                const initials = emp.full_name.substring(0, 2);
                const deptClass = colorMap[emp.dept_color] || colorMap['slate'];
                const accessLevel = emp.role_id == 2 ? 'Owner' : (emp.role_id == 3 ? 'Admin' : 'Editor');
                return `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3 cursor-pointer group" onclick="showProfile(${emp.id})">
                            <div class="size-9 rounded-full bg-slate-200 group-hover:bg-primary/10 flex items-center justify-center text-slate-500 group-hover:text-primary text-xs font-bold transition-colors">
                                ${initials}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-primary transition-colors">${emp.full_name}</p>
                                <p class="text-xs text-slate-500">${emp.email}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${emp.role_name || 'Employee'}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium border ${deptClass}">
                            ${emp.dept_name || 'Unassigned'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${accessLevel}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                            <span class="size-2 rounded-full bg-green-500"></span>Active
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="relative inline-block text-left">
                            <button onclick="toggleEmpDropdown(event, ${emp.id})"
                                class="text-slate-400 hover:text-primary transition-colors p-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800">
                                <span class="material-symbols-outlined">more_horiz</span>
                            </button>
                            <div id="emp-dropdown-${emp.id}"
                                class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-[#e8ebf3] dark:border-gray-800 rounded-xl shadow-xl z-50 overflow-hidden">
                                <div class="py-1">
                                    <button onclick="showProfile(${emp.id})"
                                        class="w-full text-left px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center gap-3">
                                        <span class="material-symbols-outlined text-base">visibility</span>View Profile
                                    </button>
                                    <div class="h-[1px] bg-slate-100 dark:bg-slate-800 mx-3"></div>
                                    <button onclick="openDeleteEmpModal(${emp.id}, '${emp.full_name.replace(/'/g, "\\'")}')"
                                        class="w-full text-left px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors flex items-center gap-3">
                                        <span class="material-symbols-outlined text-base">delete</span>Delete Employee
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        }

        let searchTimer;
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                const q = this.value.trim();

                if (!q) {
                    tbody.innerHTML = originalRows;
                    return;
                }

                searchTimer = setTimeout(async () => {
                    try {
                        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center"><div class="animate-spin size-5 border-2 border-slate-200 border-t-primary rounded-full mx-auto"></div></td></tr>`;
                        const res = await fetch(`../../Backend/api/search_employees.php?q=${encodeURIComponent(q)}`);
                        const employees = await res.json();
                        renderSearchRows(employees);
                    } catch (e) {
                        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-rose-500 text-sm">Search failed. Please try again.</td></tr>`;
                    }
                }, 300);
            });
        }
    </script>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 z-[999] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideLogoutModal()"></div>
        <!-- Modal Card -->
        <div id="logoutModalCard"
            class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-8 flex flex-col items-center text-center transform scale-95 opacity-0 transition-all duration-200">
            <!-- Icon -->
            <div class="size-14 rounded-2xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-rose-500 text-3xl" style="font-variation-settings:'FILL' 0">logout</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Logout</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-8">Are you sure you want to logout?</p>
            <div class="flex gap-3 w-full">
                <button onclick="hideLogoutModal()"
                    class="flex-1 py-2.5 rounded-full border border-slate-200 dark:border-slate-700 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Cancel
                </button>
                <a href="../../Backend/auth/logout.php"
                    class="flex-1 py-2.5 rounded-full bg-rose-500 text-white text-sm font-bold hover:bg-rose-600 transition-colors shadow-lg shadow-rose-500/25 text-center">
                    Confirm
                </a>
            </div>
        </div>
    </div>
    <script>
        function showLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const card  = document.getElementById('logoutModalCard');
            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                card.classList.replace('scale-95', 'scale-100');
                card.classList.replace('opacity-0', 'opacity-100');
            });
        }
        function hideLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const card  = document.getElementById('logoutModalCard');
            card.classList.replace('scale-100', 'scale-95');
            card.classList.replace('opacity-100', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 200);
        }
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideLogoutModal(); });
    </script>
</body>

</html>