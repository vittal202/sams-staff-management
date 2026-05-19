<?php
require_once "../../Backend/auth/auth_check.php";
checkAccess();
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Matrix Intelligence - OrgChart Pro</title>
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
                        "background-light": "#f6f6f8",
                        "background-dark": "#111621",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    }
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

<body class="bg-background-light dark:bg-background-dark font-display text-[#0e121b] dark:text-white">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Navigation (Same as Dashboard) -->
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

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <header
                class="h-16 border-b border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex items-center justify-between px-8">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold">Matrix Intelligence</h2>
                    <span class="px-2 py-0.5 bg-primary/10 text-primary text-[10px] font-black rounded uppercase">Live
                        Feed</span>
                </div>
                <div class="flex items-center gap-4">
                    <button onclick="markAllRead()"
                        class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-primary hover:text-white rounded-lg text-sm font-bold transition-all">
                        <span class="material-symbols-outlined text-sm">done_all</span>
                        Mark All Read
                    </button>
                    <a href="../dashboard/index.php" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg">
                        <span class="material-symbols-outlined">close</span>
                    </a>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-8 bg-slate-50 dark:bg-slate-950">
                <div class="max-w-4xl mx-auto space-y-6">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-2xl font-bold tracking-tight">System Notifications</h3>
                            <p class="text-slate-500 text-sm">Monitor all organizational changes and system alerts.</p>
                        </div>
                        <div
                            class="flex bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-1 shadow-sm">
                            <button onclick="setFilter('all')" id="filter-all"
                                class="px-4 py-1.5 text-xs font-bold bg-primary text-white rounded-lg transition-all">All</button>
                            <button onclick="setFilter('unread')" id="filter-unread"
                                class="px-4 py-1.5 text-xs font-bold text-slate-500 hover:text-primary rounded-lg transition-all">Unread</button>
                            <button onclick="setFilter('archived')" id="filter-archived"
                                class="px-4 py-1.5 text-xs font-bold text-slate-500 hover:text-primary rounded-lg transition-all">Archived</button>
                        </div>
                    </div>

                    <div id="fullNotifList" class="space-y-4">
                        <!-- Loading State -->
                        <div class="py-20 text-center">
                            <div
                                class="animate-spin inline-block size-8 border-4 border-slate-200 border-t-primary rounded-full mb-4">
                            </div>
                            <p class="text-slate-500 font-medium">Synchronizing with Matrix Directory...</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let allNotifications = [];
        let archivedIds = new Set();
        let currentFilter = 'all';

        async function loadAllNotifications() {
            try {
                const response = await fetch('../../Backend/api/get_notifications.php');
                allNotifications = await response.json();
                renderNotifications();
            } catch (err) {
                document.getElementById('fullNotifList').innerHTML = '<div class="p-8 text-center text-rose-500 font-bold">Failed to synchronize with Matrix API.</div>';
            }
        }

        function setFilter(filter) {
            currentFilter = filter;
            // Update UI buttons
            ['all', 'unread', 'archived'].forEach(f => {
                const btn = document.getElementById(`filter-${f}`);
                if (f === filter) {
                    btn.classList.add('bg-primary', 'text-white');
                    btn.classList.remove('text-slate-500');
                } else {
                    btn.classList.remove('bg-primary', 'text-white');
                    btn.classList.add('text-slate-500');
                }
            });
            renderNotifications();
        }

        function toggleRead(id) {
            const notif = allNotifications.find(n => n.id === id);
            if (notif) {
                notif.read = !notif.read;
                renderNotifications();
            }
        }

        function archiveNotif(id) {
            archivedIds.add(id);
            renderNotifications();
        }

        function markAllRead() {
            allNotifications.forEach(n => n.read = true);
            renderNotifications();
        }

        function renderNotifications() {
            const list = document.getElementById('fullNotifList');
            list.innerHTML = '';

            let filtered = allNotifications;

            if (currentFilter === 'archived') {
                filtered = allNotifications.filter(n => archivedIds.has(n.id));
            } else {
                // For 'all' and 'unread', we exclude archived ones
                filtered = allNotifications.filter(n => !archivedIds.has(n.id));
                if (currentFilter === 'unread') {
                    filtered = filtered.filter(n => !n.read);
                }
            }

            if (filtered.length === 0) {
                list.innerHTML = `
                    <div class="text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        <span class="material-symbols-outlined text-4xl text-slate-200 mb-4 font-variation-light">inbox_customize</span>
                        <p class="text-slate-400 font-medium">No intelligence reports in this sector.</p>
                    </div>`;
                return;
            }

            filtered.forEach(n => {
                const row = document.createElement('div');
                row.className = `bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex gap-6 items-start transition-all hover:shadow-md group ${!n.read ? 'border-l-4 border-l-primary' : ''}`;

                const icons = {
                    update: { icon: 'sync', color: 'bg-blue-50 text-blue-600' },
                    success: { icon: 'check_circle', color: 'bg-emerald-50 text-emerald-600' },
                    warning: { icon: 'warning', color: 'bg-amber-50 text-amber-600' },
                    security: { icon: 'shield', color: 'bg-purple-50 text-purple-600' }
                };
                const type = icons[n.type] || { icon: 'notifications', color: 'bg-slate-50 text-slate-600' };

                row.innerHTML = `
                        <div class="size-12 ${type.color} rounded-2xl flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl">${type.icon}</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-lg font-bold group-hover:text-primary transition-colors">${n.title}</h4>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">${n.time}</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-4">${n.message}</p>
                            <div class="flex gap-4">
                                <button onclick="toggleRead(${n.id})" class="text-xs font-bold text-primary hover:underline">${n.read ? 'Mark as Unread' : 'Mark as Read'}</button>
                                ${!archivedIds.has(n.id) ? `<button onclick="archiveNotif(${n.id})" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors">Archive</button>` : ''}
                            </div>
                        </div>
                    `;
                list.appendChild(row);
            });
        }

        document.addEventListener('DOMContentLoaded', loadAllNotifications);
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