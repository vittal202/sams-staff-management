<?php
require_once '../../Backend/auth/auth_check.php';
require_once '../../Backend/config/db.php';
checkAccess();

$userId = $_SESSION['raw_user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Projects - OrgChart Pro</title>
    <!-- Theme Initialization Script -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#16439c",
                        "background-light": "#f8fafc",
                        "background-dark": "#0e121b",
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <script src="../shared/toast.js"></script>
    <style>
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
        }
        /* --- Compact View CSS --- */
        body.compact aside                             { padding: 0.5rem !important; }
        body.compact nav a, body.compact nav > a       { padding: 0.3rem 0.6rem !important; font-size: 0.8rem !important; }
        body.compact header                            { height: 3rem !important; padding-left: 1.25rem !important; padding-right: 1.25rem !important; }
        body.compact .flex-1.overflow-auto             { padding: 0.75rem !important; }
        body.compact #projectsGrid                     { gap: 0.5rem !important; }
        body.compact #projectsGrid > div               { padding: 0.875rem !important; border-radius: 0.75rem !important; }
        body.compact #projectsGrid h4                  { font-size: 0.9rem !important; margin-bottom: 0.25rem !important; }
        body.compact #projectsGrid p                   { font-size: 0.75rem !important; margin-bottom: 0.5rem !important; }
        body.compact #projectsGrid .space-y-4          { gap: 0.5rem !important; }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-[#0e121b] dark:text-white<?php echo !empty($user['compact_view']) ? ' compact' : ''; ?>">
    <div class="flex h-screen overflow-hidden">
        <!-- Side Navigation Bar -->
        <aside
            class="w-64 border-r border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex flex-col justify-between p-4">
            <div class="flex flex-col gap-8">
                <div class="flex items-center gap-3 px-2">
                    <div class="bg-primary p-2 rounded-lg text-white">
                        <span class="material-symbols-outlined">account_tree</span>
                    </div>
                    <h1 class="text-[#0e121b] dark:text-white text-lg font-bold tracking-tight">OrgChart Pro</h1>
                </div>
                <nav class="flex flex-col gap-2">
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        href="../dashboard/index.php">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm">Dashboard</p>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-semibold"
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
                                <p class="text-xs font-bold truncate text-slate-800 dark:text-white leading-tight"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></p>
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

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <header
                class="h-16 border-b border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex items-center justify-between px-8">
                <div class="flex items-center gap-4">
                    <button onclick="openModal()"
                        class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition-colors shadow-sm active:scale-95">
                        <span class="material-symbols-outlined text-sm">add</span>
                        New Project
                    </button>
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
                                    class="text-xs font-bold text-primary hover:underline">View All Intelligence</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-auto p-8">
                <div id="projectsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Loading State -->
                    <div class="col-span-full flex justify-center py-12">
                        <div class="animate-spin size-8 border-4 border-slate-200 border-t-primary rounded-full"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- New Project Modal -->
    <div id="projectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop">
        <div
            class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-gray-800">
            <div
                class="px-6 py-4 border-b border-slate-100 dark:border-gray-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="font-bold">Create New Project</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="projectForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Project Name</label>
                    <input type="text" name="name" required
                        class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-4 focus:ring-primary/10 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-4 focus:ring-primary/10 text-sm"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Initial Progress %</label>
                        <input type="number" name="progress" min="0" max="100" value="0"
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-4 focus:ring-primary/10 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Status</label>
                        <select name="status"
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-4 focus:ring-primary/10 text-sm capitalize">
                            <option>Planning</option>
                            <option>In Progress</option>
                            <option>On Hold</option>
                            <option>Completed</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Handled By</label>
                    <div class="relative">
                        <select name="handled_by" id="createHandlerSelect"
                            class="w-full px-4 py-2 pr-10 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-4 focus:ring-primary/10 text-sm appearance-none">
                            <option value="">— Unassigned —</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-sm">person</span>
                    </div>
                </div>
                <div class="pt-4">
                    <button type="submit"
                        class="w-full py-3 bg-primary text-white font-bold rounded-xl hover:bg-blue-800 transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                        <span id="submitLabel">Initiate Project</span>
                        <div id="submitLoader"
                            class="hidden animate-spin size-4 border-2 border-white/30 border-t-white rounded-full">
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Project Details Modal -->
    <div id="detailsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop">
        <div
            class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-gray-800">
            <div class="p-8 space-y-6">
                <div class="flex justify-between items-start">
                    <div id="detailsIconContainer" class="size-14 rounded-2xl flex items-center justify-center">
                        <span id="detailsIcon" class="material-symbols-outlined text-2xl"></span>
                    </div>
                    <button onclick="closeDetailsModal()"
                        class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h3 id="detailsName" class="text-2xl font-bold"></h3>
                        <span id="detailsStatusLabel"
                            class="px-2 py-0.5 text-[10px] font-bold rounded uppercase"></span>
                    </div>
                    <p id="detailsDescription" class="text-slate-500 leading-relaxed"></p>
                </div>

                <!-- Handled By -->
                <div class="flex items-center gap-3 pt-2">
                    <span class="material-symbols-outlined text-slate-400 text-lg">person</span>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Handled By</p>
                        <p id="detailsHandler" class="text-sm font-semibold text-slate-700 dark:text-slate-200"></p>
                    </div>
                </div>

                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-gray-800">
                    <div class="flex justify-between items-end">
                        <span class="text-sm font-bold text-slate-400 uppercase tracking-wider">Current Progress</span>
                        <div class="flex items-center gap-2">
                            <span id="detailsProgressText" class="text-2xl font-black text-primary"></span>
                        </div>
                    </div>
                    <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div id="detailsProgressBar" class="h-full bg-primary transition-all duration-1000"></div>
                    </div>
                    <!-- Progress Adjustment Slider -->
                    <div class="pt-2 space-y-3">
                        <input type="range" id="progressSlider" min="0" max="100"
                            class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-primary">
                        <button id="btnUpdateProgress"
                            class="w-full py-2.5 bg-primary/10 text-primary text-sm font-bold rounded-xl hover:bg-primary hover:text-white transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">sync</span>
                            Update Progress
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4">
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Created On</p>
                        <p id="detailsDate" class="font-semibold text-sm"></p>
                    </div>
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Project ID</p>
                        <p id="detailsId" class="font-semibold text-sm"></p>
                    </div>
                </div>

                <!-- Delete Project Button in Details -->
                <div class="pt-4 border-t border-slate-100 dark:border-gray-800">
                    <button id="btnDeleteDetail"
                        class="w-full py-2.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-bold rounded-xl transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">delete</span>
                        Delete Project
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal"
        class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md p-8 shadow-2xl transform scale-95 transition-transform duration-300 border border-slate-200 dark:border-gray-800">
            <div class="flex flex-col items-center text-center mb-6">
                <div
                    class="size-12 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-500 mb-4">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete Project?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Are you sure you want to delete this project? <br>
                    This action cannot be undone.
                </p>
            </div>

            <div class="flex gap-3">
                <button type="button" id="cancelDeleteBtn"
                    class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                <button type="button" id="confirmDeleteBtn"
                    class="flex-1 py-2.5 bg-red-500 text-white rounded-lg text-sm font-bold hover:bg-red-600 transition-colors shadow-lg shadow-red-500/25">Delete</button>
            </div>
        </div>
    </div>

    <script>
        const grid = document.getElementById('projectsGrid');
        const modal = document.getElementById('projectModal');
        const detailsModal = document.getElementById('detailsModal');
        const form = document.getElementById('projectForm');

        let allProjects = [];

        async function openModal() {
            modal.classList.remove('hidden');
            await loadEmployees();
            const sel = document.getElementById('createHandlerSelect');
            sel.innerHTML = '<option value="">— Unassigned —</option>';
            employeesList.forEach(emp => {
                const opt = document.createElement('option');
                opt.value = emp.id;
                opt.textContent = emp.full_name + (emp.role_name ? ` (${emp.role_name})` : '');
                sel.appendChild(opt);
            });
        }

        function closeModal() {
            modal.classList.add('hidden');
            form.reset();
        }

        // Employees list cache
        let employeesList = [];

        async function loadEmployees() {
            if (employeesList.length > 0) return; // already loaded
            try {
                const res = await fetch('../../Backend/api/get_employees_list.php');
                employeesList = await res.json();
            } catch (e) {
                employeesList = [];
            }
        }

        function populateHandlerSelect(currentHandledBy) {
            // no-op: handler select now only in create modal
        }

        async function openDetailsModal(id) {
            const p = allProjects.find(item => item.id == id);
            if (!p) return;

            const sc = getStatusColor(p.status);

            document.getElementById('detailsIconContainer').className = `size-14 ${sc.bg} rounded-2xl flex items-center justify-center ${sc.text}`;
            document.getElementById('detailsIcon').innerText = getStatusIcon(p.status);
            document.getElementById('detailsName').innerText = p.name;
            document.getElementById('detailsDescription').innerText = p.description || 'No detailed description available for this corporate initiative.';
            document.getElementById('detailsStatusLabel').className = `px-2 py-0.5 ${sc.labelBg} ${sc.labelBtn} text-[10px] font-bold rounded uppercase`;
            document.getElementById('detailsStatusLabel').innerText = p.status;
            document.getElementById('detailsProgressText').innerText = `${p.progress}%`;
            document.getElementById('detailsProgressBar').style.width = `${p.progress}%`;
            document.getElementById('detailsProgressBar').className = `h-full ${sc.bar} transition-all duration-1000`;
            document.getElementById('detailsDate').innerText = new Date(p.created_at).toLocaleDateString();
            document.getElementById('detailsId').innerText = `#PRJ-${p.id.toString().padStart(4, '0')}`;
            document.getElementById('detailsHandler').innerText = p.handler_name || '— Unassigned —';

            // Set Slider
            const slider = document.getElementById('progressSlider');
            slider.value = p.progress;

            detailsModal.classList.remove('hidden');
        }

        // Real-time slider feedback
        document.getElementById('progressSlider').oninput = function () {
            const val = this.value;
            document.getElementById('detailsProgressText').innerText = `${val}%`;
            document.getElementById('detailsProgressBar').style.width = `${val}%`;
        };

        // Update Progress Button Logic
        document.getElementById('btnUpdateProgress').onclick = async function () {
            const btn = this;
            const projectId = document.getElementById('detailsId').innerText.replace('#PRJ-', '').replace(/^0+/, '');
            const progress = document.getElementById('progressSlider').value;

            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin material-symbols-outlined text-sm">progress_activity</span> Updating...';

            try {
                const response = await fetch('../../Backend/api/update_project_progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        project_id: projectId,
                        progress: progress
                    })
                });

                const result = await response.json();
                if (result.success) {
                    await fetchProjects(); // Refresh the grid
                    showToast('Project progress updated successfully.', 'success');
                } else {
                    showToast(result.message, 'error');
                }
            } catch (err) {
                showToast("Network error occurred. Please check your connection.", 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined text-sm">sync</span> Update Progress';
            }
        };

        function closeDetailsModal() {
            detailsModal.classList.add('hidden');
        }


        async function fetchProjects() {
            try {
                const response = await fetch('../../Backend/api/get_projects.php');
                const projects = await response.json();
                allProjects = projects;
                grid.innerHTML = '';

                if (projects.length === 0) {
                    grid.innerHTML = '<div class="col-span-full text-center text-slate-500 py-12">No active projects found.</div>';
                    return;
                }

                projects.forEach(p => {
                    const statusColor = getStatusColor(p.status);
                    const icon = getStatusIcon(p.status);

                    const card = document.createElement('div');
                    card.className = "bg-white dark:bg-slate-900 border border-[#e8ebf3] dark:border-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all cursor-pointer hover:-translate-y-1 active:scale-95 group";
                    card.onclick = () => openDetailsModal(p.id);

                    card.innerHTML = `
                        <div class="flex items-start justify-between mb-4">
                            <div class="size-12 ${statusColor.bg} rounded-xl flex items-center justify-center ${statusColor.text} group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined">${icon}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="event.stopPropagation(); openDeleteModal(${p.id})" 
                                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                                <span class="px-2 py-1 ${statusColor.labelBg} ${statusColor.labelBtn} text-[10px] font-bold rounded uppercase">${p.status}</span>
                            </div>
                        </div>
                        <h4 class="font-bold text-lg mb-1 truncate">${p.name}</h4>
                        <p class="text-sm text-slate-500 mb-6 line-clamp-2">${p.description || 'No description provided.'}</p>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 font-medium">Progress</span>
                                <span class="text-primary font-bold">${p.progress}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full ${statusColor.bar}" style="width: ${p.progress}%"></div>
                            </div>
                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center -space-x-2">
                                    <div class="size-8 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500">SY</div>
                                    <div class="size-8 rounded-full border-2 border-white bg-blue-100 text-blue-600 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-xs">add</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Click for details</span>
                            </div>
                        </div>
                    `;
                    grid.appendChild(card);
                });
            } catch (err) {
                grid.innerHTML = '<div class="col-span-full text-red-500 text-center">Failed to load projects.</div>';
            }
        }

        function getStatusColor(status) {
            switch (status) {
                case 'In Progress': return { bg: 'bg-blue-50', text: 'text-primary', labelBg: 'bg-blue-100', labelBtn: 'text-blue-700', bar: 'bg-primary' };
                case 'Planning': return { bg: 'bg-amber-50', text: 'text-amber-600', labelBg: 'bg-amber-100', labelBtn: 'text-amber-700', bar: 'bg-amber-500' };
                case 'Completed': return { bg: 'bg-emerald-50', text: 'text-emerald-600', labelBg: 'bg-emerald-100', labelBtn: 'text-emerald-700', bar: 'bg-emerald-500' };
                default: return { bg: 'bg-slate-50', text: 'text-slate-600', labelBg: 'bg-slate-100', labelBtn: 'text-slate-700', bar: 'bg-slate-400' };
            }
        }

        function getStatusIcon(status) {
            switch (status) {
                case 'In Progress': return 'terminal';
                case 'Planning': return 'campaign';
                case 'Completed': return 'check_circle';
                default: return 'pause';
            }
        }

        form.onsubmit = async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button');
            const loader = document.getElementById('submitLoader');
            const label = document.getElementById('submitLabel');

            btn.disabled = true;
            loader.classList.remove('hidden');
            label.innerText = 'Syncing...';

            try {
                const formData = new FormData(form);
                const response = await fetch('../../Backend/api/add_project.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.success) {
                    closeModal();
                    fetchProjects();
                } else {
                    alert(res.message);
                }
            } catch (err) {
                alert("Critical Error: Unable to reach backend server.");
            } finally {
                btn.disabled = false;
                loader.classList.add('hidden');
                label.innerText = 'Initiate Project';
            }
        };

        document.addEventListener('DOMContentLoaded', fetchProjects);

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
                    item.className = `p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer group ${!n.read ? 'bg-primary/5' : ''} text-left`;

                    const icons = {
                        update: 'sync',
                        success: 'check_circle',
                        warning: 'warning',
                        security: 'shield'
                    };

                    item.innerHTML = `
                        <div class="flex gap-4">
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

        // Deletion Logic
        let projectToDelete = null;
        const deleteModal = document.getElementById('deleteModal');
        const deleteModalContent = deleteModal.querySelector('div');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

        function openDeleteModal(id) {
            projectToDelete = id;
            deleteModal.classList.remove('hidden');
            setTimeout(() => {
                deleteModal.classList.replace('opacity-0', 'opacity-100');
                deleteModalContent.classList.replace('scale-95', 'scale-100');
            }, 10);
        }

        function closeDeleteModal() {
            deleteModal.classList.replace('opacity-100', 'opacity-0');
            deleteModalContent.classList.replace('scale-100', 'scale-95');
            setTimeout(() => {
                deleteModal.classList.add('hidden');
                projectToDelete = null;
            }, 300);
        }

        cancelDeleteBtn.onclick = closeDeleteModal;
        deleteModal.onclick = (e) => { if (e.target === deleteModal) closeDeleteModal(); };

        document.getElementById('btnDeleteDetail').onclick = () => {
            const currentId = document.getElementById('detailsId').innerText.replace('#PRJ-', '').replace(/^0+/, '');
            closeDetailsModal();
            openDeleteModal(currentId);
        };

        confirmDeleteBtn.onclick = async () => {
            if (!projectToDelete) return;

            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<span class="animate-spin size-4 border-2 border-white/30 border-t-white rounded-full"></span> Deleting...';

            try {
                const response = await fetch('../../Backend/api/delete_project.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: projectToDelete })
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Project deleted successfully.', 'success');
                    fetchProjects();
                    closeDeleteModal();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (err) {
                showToast('Failed to delete project.', 'error');
            } finally {
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.innerHTML = 'Delete';
            }
        };
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