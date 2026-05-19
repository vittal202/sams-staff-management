<?php
require_once "../../Backend/auth/auth_check.php";
require_once "../../Backend/config/db.php";
checkAccess(); // Ensure user is logged in

$userId = $_SESSION['raw_user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$userRoleId   = intval($user['role_id'] ?? 0);
$userRoleName = strtolower($user['role_name'] ?? '');
$isOrgRoot    = empty($user['manager_id']); // root node has no manager

// CEO can modify: either role_id=2, role_name contains 'ceo', or is root node
$canModify = ($userRoleId === 2)
          || (str_contains($userRoleName, 'ceo'))
          || $isOrgRoot;

?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Organization Chart</title>

    <!-- Theme Initialization Script -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
                },
            },
        }
    </script>

    <?php require_once "../../Backend/auth/fetch_user_preferences.php"; ?>

    <style>
        /* --- Genealogy Tree CSS --- */
        .tree {
            white-space: nowrap;
            overflow: auto;
            padding: 50px;
            min-height: 500px;
            display: inline-flex;
            min-width: 100%;
            justify-content: center;
        }

        .tree ul {
            padding-top: 20px;
            position: relative;
            transition: all 0.5s;
            display: flex;
            justify-content: center;
        }

        .tree li {
            float: left;
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 20px 5px 0 5px;
            transition: all 0.5s;
        }

        /* Connecting Lines */
        .tree li::before,
        .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 2px solid #94a3b8;
            /* Slate-400 */
            width: 50%;
            height: 20px;
        }

        .tree li::after {
            right: auto;
            left: 50%;
            border-left: 2px solid #94a3b8;
        }

        .tree li:only-child::after,
        .tree li:only-child::before {
            display: none;
        }

        .tree li:only-child {
            padding-top: 0;
        }

        .tree li:first-child::before,
        .tree li:last-child::after {
            border: 0 none;
        }

        .tree li:last-child::before {
            border-right: 2px solid #94a3b8;
            border-radius: 0 5px 0 0;
        }

        .tree li:first-child::after {
            border-radius: 5px 0 0 0;
        }

        .tree ul ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 2px solid #94a3b8;
            width: 0;
            height: 20px;
        }

        .dark .tree li::before,
        .dark .tree li::after,
        .dark .tree ul ul::before,
        .dark .tree li:last-child::before,
        .dark .tree li:first-child::after {
            border-color: #475569;
        }


        /* Employee Card Styling - Blue Theme */
        .org-card {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #e0f2fe;
            /* Light Blue 100 */
            border: 1px solid #7dd3fc;
            /* Sky 300 */
            border-radius: 8px;
            /* Slightly sharper rounded corners */
            padding: 16px 12px;
            width: 160px;
            /* Specific width */
            text-decoration: none;
            color: #1e293b;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            position: relative;
            z-index: 50;
            /* Increased z-index */
        }

        .dark .org-card {
            background: #0f172a;
            border-color: #1e293b;
            color: #f8fafc;
        }

        .org-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-color: #38bdf8;
            /* Sky 400 */
            z-index: 100;
        }

        /* Drag & Drop Feedback */
        .org-card.dragging {
            opacity: 0.5;
            border: 2px dashed #0284c7;
        }

        .org-card.drag-over {
            background-color: #dbeafe;
            /* Blue 100 */
            border-color: #2563eb;
            transform: scale(1.05);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
        }

        /* Profile Image */
        .card-img {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 8px;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            pointer-events: none;
            /* Ensure drag triggers on parent */
        }

        /* Typography */
        .card-name {
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 2px;
            line-height: 1.2;
            color: #0f172a;
            /* Slate 900 */
            pointer-events: none;
            /* Ensure drag triggers on parent */
        }

        .dark .card-name {
            color: #f1f5f9;
        }

        .card-role {
            font-size: 0.7rem;
            color: #475569;
            /* Slate 600 */
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            pointer-events: none;
            /* Ensure drag triggers on parent */
        }

        .dark .card-role {
            color: #94a3b8;
        }

        /* Level Specifics (Optional overrides) */
        /* Level Specific Colors */
        .level-executive {
            background: #1e3a8a !important;
            /* Blue-900 */
            color: white !important;
            border: 2px solid #d4af37 !important;
            /* Gold border for CEO/Founder */
        }

        .level-director {
            background: #1d4ed8 !important;
            /* Blue-700 */
            color: white !important;
            border-color: #1d4ed8 !important;
        }

        .level-manager {
            background: #3b82f6 !important;
            /* Blue-500 */
            color: white !important;
            border-color: #3b82f6 !important;
        }

        .level-specialist,
        .level-employee {
            background: #eff6ff !important;
            /* Blue-50 */
            border-color: #bfdbfe !important;
            /* Blue-200 */
        }

        .level-executive .card-name,
        .level-director .card-name,
        .level-manager .card-name {
            color: white !important;
        }

        .level-executive .card-role,
        .level-director .card-role,
        .level-manager .card-role {
            color: #dbeafe !important;
            /* Light Blue-100 */
        }

        /* Delete Button Styling */
        .delete-btn {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(244, 63, 94, 0.1);
            color: #f43f5e;
            border-radius: 6px;
            opacity: 0;
            transition: all 0.2s ease;
            cursor: pointer;
            z-index: 100;
        }

        .org-card:hover .delete-btn {
            opacity: 1;
        }

        .delete-btn:hover {
            background: #f43f5e;
            color: white;
            transform: scale(1.1);
        }

        .delete-btn .material-symbols-outlined {
            font-size: 16px !important;
        }
        /* --- Compact View CSS --- */
        body.compact aside                             { padding: 0.5rem !important; }
        body.compact nav a, body.compact nav > a       { padding: 0.3rem 0.6rem !important; font-size: 0.8rem !important; }
        body.compact header                            { height: 3rem !important; padding-left: 1.25rem !important; padding-right: 1.25rem !important; }
        body.compact .org-card                         { padding: 6px 5px !important; width: 100px !important; border-radius: 6px !important; }
        body.compact .card-img                         { width: 30px !important; height: 30px !important; margin-bottom: 3px !important; }
        body.compact .card-name                        { font-size: 0.65rem !important; margin-bottom: 1px !important; }
        body.compact .card-role                        { font-size: 0.55rem !important; }
        body.compact .tree li                          { padding-top: 10px !important; padding-left: 3px !important; padding-right: 3px !important; }
        body.compact .tree                             { padding: 24px !important; }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark font-display text-[#0e121b] dark:text-white h-screen flex flex-col overflow-hidden<?php echo !empty($user['compact_view']) ? ' compact' : ''; ?>">

    <div class="flex h-full overflow-hidden">
        <!-- Sidebar (Simplified for spacing) -->
        <aside
            class="w-64 border-r border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex-shrink-0 flex flex-col justify-between p-4">
            <div class="flex flex-col gap-8">
                <div class="flex items-center gap-3 px-2">
                    <div class="bg-primary p-2 rounded-lg text-white">
                        <span class="material-symbols-outlined">account_tree</span>
                    </div>
                    <h1 class="text-[#0e121b] dark:text-white text-lg font-bold tracking-tight">OrgChart Pro</h1>
                </div>
                <nav class="flex flex-col gap-2">
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-semibold"
                        href="index.php">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        href="../Projects/index.php">
                        <span class="material-symbols-outlined">assignment</span>
                        <p class="text-sm">Projects</p>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
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
                                    <?php echo htmlspecialchars($_SESSION['role_name'] ?? 'SAMS Corporate'); ?>
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

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header
                class="h-16 border-b border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex items-center justify-between px-8 flex-shrink-0">
                <div class="font-semibold text-lg flex items-center gap-3">
                    Organization Layout
                    <span id="total-count-badge"
                        class="bg-primary/10 text-primary text-xs px-2 py-1 rounded-full hidden">Total: 0</span>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span id="loading-indicator" class="text-blue-500 hidden items-center gap-2">
                        <span class="animate-spin material-symbols-outlined text-sm">progress_activity</span> Syncing...
                    </span>
                    <button onclick="zoomIn()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                    <button onclick="zoomOut()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">
                        <span class="material-symbols-outlined">remove</span>
                    </button>
                    <button onclick="fitToScreen()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg"
                        title="Reset Zoom">
                        <span class="material-symbols-outlined">center_focus_strong</span>
                    </button>
                </div>
            </header>

            <!-- Chart Area -->
            <div id="chart-container" class="flex-1 overflow-auto bg-slate-50 dark:bg-black/20 cursor-move relative">
                <div class="tree" id="org-tree-root">
                    <!-- Tree will be rendered here -->
                </div>
                <?php if (!$canModify): ?>
                <!-- Read-only banner for non-CEO users -->
                <div class="absolute top-3 right-3 flex items-center gap-2 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-300 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm pointer-events-none">
                    <span class="material-symbols-outlined text-sm">visibility</span>
                    View Only — CEO access required to modify
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Drag Restriction Toast -->
    <div id="drag-toast"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[200] bg-slate-900 dark:bg-slate-700 text-white text-sm font-semibold px-5 py-3 rounded-2xl shadow-2xl
               opacity-0 translate-y-4 transition-all duration-300 pointer-events-none flex items-center gap-2">
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
                    <div id="profProjectsWrap">
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-2 mt-1">Handled Projects</p>
                        <div id="profProjects" class="space-y-2 max-h-40 overflow-y-auto pr-1">
                            <p class="text-xs text-slate-400">Loading projects...</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex gap-3 w-full">
                    <button id="btnSendMessage"
                        class="flex-1 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-blue-800 transition-colors shadow-lg shadow-primary/25">Send
                        Message</button>
                    <button onclick="closeProfile()"
                        class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div id="deleteConfirmModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-all duration-300">
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl transform scale-90 transition-all duration-300 border border-slate-200 dark:border-slate-800">
            <div class="p-8 flex flex-col items-center text-center">
                <div
                    class="size-16 rounded-2xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-500 mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-4xl font-variation-fill">delete_forever</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete Node?</h3>
                <p id="deleteModalMessage" class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-8 px-2">
                    Are you sure you want to remove <span id="deleteModalNodeName"
                        class="font-bold text-slate-900 dark:text-white"></span>? Subordinates will be moved to the
                    manager above.
                </p>

                <div class="flex flex-col gap-3 w-full">
                    <button id="btnConfirmDelete"
                        class="w-full py-3.5 bg-rose-500 text-white rounded-2xl text-sm font-bold hover:bg-rose-600 transition-all shadow-lg shadow-rose-500/25 active:scale-[0.98]">
                        Yes, Delete Node
                    </button>
                    <button onclick="closeDeleteModal()"
                        class="w-full py-3.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-2xl text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all active:scale-[0.98]">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- State ---
        let currentZoom = 1;
        let isDraggingCanvas = false;
        let startX, startY, scrollLeft, scrollTop;
        let draggedEmployeeId = null;

        // --- Role-Based Access (dynamic — updated after each chart load) ---
        let canDrag = <?php echo $canModify ? 'true' : 'false'; ?>;
        const loggedInUserId = <?php echo (int)$userId; ?>;

        // Custom Portraits Map (Preserving from original file)
        const customPortraits = {
            1: 'https://i.pravatar.cc/150?img=68',
            2: '../assets/images/alexandra_sterling.png',
            3: '../assets/images/marcus_vane.png',
            85: '../assets/images/james_aris.png',
            86: '../assets/images/sarah_loft.png',
            87: '../assets/images/robert_chen.png',
            89: '../assets/images/elena_rossi.png',
            93: 'https://i.pravatar.cc/150?img=32'
        };

        // --- Initialization ---
        $(document).ready(function () {
            loadOrgChart();
            initCanvasDrag();
        });

        async function loadOrgChart() {
            try {
                showLoading(true);
                const response = await fetch('../../Backend/api/get_org_chart_data.php');
                const data = await response.json();

                if (data.error) {
                    $('#org-tree-root').html(`<div class="text-rose-500 font-bold p-10">${data.error}</div>`);
                    return;
                }

                // Render the Tree — pass isRoot=true so CEO always gets executive styling
                const treeHtml = '<ul>' + renderNode(data, true) + '</ul>';
                $('#org-tree-root').html(treeHtml);

                // Calculate and Show Total Count
                const totalCount = countNodes(data);
                $('#total-count-badge').text(`Total: ${totalCount}`).removeClass('hidden');

                // ── Dynamic CEO detection ─────────────────────────────────
                // If the logged-in user is now the root node, grant drag access
                if (data && parseInt(data.id) === loggedInUserId) {
                    canDrag = true;
                    // Hide the "View Only" banner if present
                    const viewOnlyBanner = document.querySelector('[class*="amber"]');
                    if (viewOnlyBanner) viewOnlyBanner.style.display = 'none';
                }
                // ─────────────────────────────────────────────────────────

                // Re-bind Drag Events (since we replaced DOM)
                bindDragEvents();

                // Scroll to top-center so CEO card is always visible
                const container = document.getElementById('chart-container');
                container.scrollTop = 0;
                // Center horizontally on the root node
                setTimeout(() => {
                    const tree = document.getElementById('org-tree-root');
                    const treeWidth = tree.scrollWidth;
                    const containerWidth = container.clientWidth;
                    container.scrollLeft = Math.max(0, (treeWidth - containerWidth) / 2);
                }, 50);

            } catch (err) {
                console.error("Error loading chart:", err);
                $('#org-tree-root').html('<div class="text-rose-500 p-10">Failed to load organization chart.</div>');
            } finally {
                showLoading(false);
            }
        }

        // --- Recursive Rendering ---
        // isRoot=true forces CEO styling on the root node, regardless of DB state
        function renderNode(node, isRoot = false) {
            if (!node) return '';

            // CEO detection: root node is ALWAYS the CEO
            const roleId = parseInt(node.role_id) || 0;
            const role   = (node.role_name || '').toLowerCase();
            const isCEO  = isRoot || roleId === 2;

            // Override display title for root node so it always shows correct title
            const displayTitle = isCEO ? 'Chief Executive Officer' : (node.role_name || 'Employee');

            let levelClass;
            if (isCEO)                                        levelClass = 'level-executive';
            else if (roleId === 3 && role.includes('director')) levelClass = 'level-director';
            else if (roleId === 3)                              levelClass = 'level-manager';
            else                                               levelClass = 'level-employee';

            const photoUrl = customPortraits[node.id] || `https://ui-avatars.com/api/?name=${encodeURIComponent(node.full_name)}&background=random&size=128`;

            // HTML Construction
            let html = `
                <li>
                    <div class="org-card ${levelClass} cursor-pointer hover:scale-105 transition-transform"
                         draggable="${canDrag ? 'true' : 'false'}"
                         onclick="showProfile(${node.id}, '${displayTitle.replace(/'/g, "\\'")}')"
                         data-id="${node.id}"
                         data-manager-id="${node.manager_id || ''}">

                        ${canDrag && !isCEO ? `<div class="delete-btn" title="Delete Employee" onclick="event.stopPropagation(); deleteEmployeeNode(${node.id}, '${node.full_name.replace(/'/g, "\\'")}', '${(node.role_name || 'Employee').replace(/'/g, "\\'")}')">
                            <span class="material-symbols-outlined">delete</span>
                        </div>` : ''}

                        <img src="${photoUrl}" class="card-img" alt="${node.full_name}">
                        <div class="card-name">${node.full_name}</div>
                        <div class="card-role">${displayTitle}</div>
                    </div>
            `;

            // Recursive Children
            if (node.children && node.children.length > 0) {
                html += '<ul>';
                // Sort children by name for consistent ordering
                node.children.sort((a, b) => a.full_name.localeCompare(b.full_name));

                node.children.forEach(child => {
                    html += renderNode(child);
                });
                html += '</ul>';
            }

            html += '</li>';
            return html;
        }

        function countNodes(node) {
            if (!node) return 0;
            let count = 1; // Count self
            if (node.children && node.children.length > 0) {
                node.children.forEach(child => {
                    count += countNodes(child);
                });
            }
            return count;
        }


        // --- Drag & Drop Logic ---
        function bindDragEvents() {
            const cards = document.querySelectorAll('.org-card');

            cards.forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragover', handleDragOver);
                card.addEventListener('dragleave', handleDragLeave);
                card.addEventListener('drop', handleDrop);
                card.addEventListener('dragend', handleDragEnd);
            });
        }

        function handleDragStart(e) {
            // Role-Based Restriction: Only CEO can drag
            if (!canDrag) {
                e.preventDefault();
                showToast('⚠️ Only the CEO can modify the organization structure.');
                return false;
            }
            draggedEmployeeId = this.dataset.id;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', draggedEmployeeId);
        }

        function handleDragOver(e) {
            e.preventDefault(); // Necessary to allow dropping
            if (this.classList.contains('dragging')) return;

            const targetId = this.dataset.id;

            // Visual feedback
            if (targetId === draggedEmployeeId) {
                this.classList.add('drag-invalid');
                e.dataTransfer.dropEffect = 'none';
            } else {
                this.classList.add('drag-over');
                // Added: Hint that this is a promotion drop
                this.setAttribute('title', 'Drop here to promote employee');
                e.dataTransfer.dropEffect = 'move';
            }
        }

        function handleDragLeave(e) {
            this.classList.remove('drag-over', 'drag-invalid');
        }

        function handleDragEnd(e) {
            this.classList.remove('dragging');
            document.querySelectorAll('.org-card').forEach(c => c.classList.remove('drag-over', 'drag-invalid'));
        }

        async function handleDrop(e) {
            e.preventDefault();
            this.classList.remove('drag-over', 'drag-invalid');

            const newManagerId = this.dataset.id;
            const employeeId = e.dataTransfer.getData('text/plain');

            console.log(`Dropping ${employeeId} onto ${newManagerId}`);

            if (!employeeId || !newManagerId || employeeId === newManagerId) return;

            // Call AJAX Update
            await updateManager(employeeId, newManagerId);
        }

        async function updateManager(employeeId, newManagerId) {
            try {
                showLoading(true);
                const response = await fetch('../../Backend/api/update_employee_manager.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        new_manager_id: newManagerId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Success - Reload Chart
                    // Note: We reload to ensure the circular check didn't block it and to re-layout the tree
                    await loadOrgChart();
                } else {
                    alert('Error: ' + result.error);
                }

            } catch (err) {
                console.error("Update failed", err);
                alert("Network error occurred.");
            } finally {
                showLoading(false);
            }
        }


        // --- Canvas Controls (Pan & Zoom) ---
        function zoomIn() {
            currentZoom += 0.1;
            applyTransform();
        }

        function zoomOut() {
            if (currentZoom > 0.3) {
                currentZoom -= 0.1;
                applyTransform();
            }
        }

        function fitToScreen() {
            currentZoom = 1;
            applyTransform();
        }

        function applyTransform() {
            const root = document.getElementById('org-tree-root');
            root.style.transform = `scale(${currentZoom})`;
            root.style.transformOrigin = 'top center';
        }

        function initCanvasDrag() {
            const container = document.getElementById('chart-container');

            container.addEventListener('mousedown', (e) => {
                // Ignore if clicking on a card
                if (e.target.closest('.org-card')) return;

                isDraggingCanvas = true;
                container.classList.add('cursor-grabbing');
                startX = e.pageX - container.offsetLeft;
                startY = e.pageY - container.offsetTop;
                scrollLeft = container.scrollLeft;
                scrollTop = container.scrollTop;
            });

            container.addEventListener('mouseleave', () => {
                isDraggingCanvas = false;
                container.classList.remove('cursor-grabbing');
            });

            container.addEventListener('mouseup', () => {
                isDraggingCanvas = false;
                container.classList.remove('cursor-grabbing');
            });

            container.addEventListener('mousemove', (e) => {
                if (!isDraggingCanvas) return;
                e.preventDefault();
                const x = e.pageX - container.offsetLeft;
                const y = e.pageY - container.offsetTop;
                const walkX = (x - startX) * 1.5; // Scroll speed
                const walkY = (y - startY) * 1.5;
                container.scrollLeft = scrollLeft - walkX;
                container.scrollTop = scrollTop - walkY;
            });
        }

        function showLoading(show) {
            const indicator = document.getElementById('loading-indicator');
            if (show) indicator.classList.remove('hidden');
            else indicator.classList.add('hidden');

            if (show) indicator.classList.add('flex');
            else indicator.classList.remove('flex');
        }

        // --- Toast Notification ---
        function showToast(message) {
            const toast = document.getElementById('drag-toast');
            toast.textContent = message;
            toast.classList.remove('opacity-0', 'translate-y-4');
            toast.classList.add('opacity-100', 'translate-y-0');
            clearTimeout(toast._timer);
            toast._timer = setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-4');
            }, 3000);
        }

        // --- Profile Modal Logic ---
        const profileModal = document.getElementById('profileModal');
        const profileContent = profileModal.querySelector('div');

        async function showProfile(id, titleOverride) {
            if (isDraggingCanvas) return;

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
                document.getElementById('profRole').innerText = titleOverride || data.role_name;
                document.getElementById('profEmail').innerText = data.email;
                document.getElementById('profDept').innerText = data.dept_name || 'Unassigned';
                document.getElementById('profDeptIcon').innerText = data.icon || 'corporate_fare';

                const imageUrl = customPortraits[id] || `https://ui-avatars.com/api/?name=${encodeURIComponent(data.full_name)}&background=random&size=128`;
                document.getElementById('profImage').style.backgroundImage = `url('${imageUrl}')`;

                // Update Send Message Button
                document.getElementById('btnSendMessage').onclick = () => {
                    window.location.href = `mailto:${data.email}`;
                };

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
                console.error(err);
                document.getElementById('profName').innerText = 'Error loading details';
            }
        }

        function closeProfile() {
            profileModal.classList.replace('opacity-100', 'opacity-0');
            profileContent.classList.replace('scale-100', 'scale-95');
            setTimeout(() => { profileModal.classList.add('hidden'); }, 300);
        }

        profileModal.addEventListener('click', (e) => { if (e.target === profileModal) closeProfile(); });

        let pendingDeleteId = null;

        function deleteEmployeeNode(id, name, role) {
            pendingDeleteId = id;
            document.getElementById('deleteModalNodeName').innerText = name;

            const modal = document.getElementById('deleteConfirmModal');
            const content = modal.querySelector('div');

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.replace('opacity-0', 'opacity-100');
                content.classList.replace('scale-90', 'scale-100');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteConfirmModal');
            const content = modal.querySelector('div');

            modal.classList.replace('opacity-100', 'opacity-0');
            content.classList.replace('scale-100', 'scale-90');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
            pendingDeleteId = null;
        }

        document.getElementById('btnConfirmDelete').addEventListener('click', async function () {
            if (!pendingDeleteId) return;

            const idToDelete = pendingDeleteId; // Capture the ID before resetting

            try {
                showLoading(true);
                closeDeleteModal();
                const response = await fetch('../../Backend/api/delete_employee_node.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ employee_id: idToDelete })
                });

                const result = await response.json();

                if (result.success) {
                    await loadOrgChart();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (err) {
                console.error("Deletion failed:", err);
                alert("Failed to delete employee.");
            } finally {
                showLoading(false);
            }
        });

        // Close modal on outside click
        document.getElementById('deleteConfirmModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('deleteConfirmModal')) closeDeleteModal();
        });

    </script>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 z-[999] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="hideLogoutModal()"></div>
        <div id="logoutModalCard"
            class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-8 flex flex-col items-center text-center transform scale-95 opacity-0 transition-all duration-200">
            <div class="size-14 rounded-2xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-rose-500 text-3xl">logout</span>
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