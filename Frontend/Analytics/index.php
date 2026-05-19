<?php
require_once '../../Backend/auth/auth_check.php';
checkAccess(); // Allow all logged in users for now
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Analytics & Reports - OrgChart Pro</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#16439c",
                        "background-light": "#f8fafc",
                        "background-dark": "#0e121b",
                        "accent-gold": "#d4af37"
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-[#0e121b] dark:text-white">
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
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        href="../Employees/index.php">
                        <span class="material-symbols-outlined">group</span>
                        <p class="text-sm">Staff</p>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-semibold"
                        href="../Analytics/index.php">
                        <span class="material-symbols-outlined">analytics</span>
                        <p class="text-sm">Analytics</p>
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

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <header
                class="h-16 border-b border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex items-center justify-between px-8">
                <h2 class="text-lg font-bold">Analytics & Insights</h2>
                <div class="flex items-center gap-4">
                    <button
                        class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition-colors">
                        <span class="material-symbols-outlined text-sm">download</span>
                        Export Report
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

            <div class="flex-1 overflow-auto p-8 space-y-8">
                <!-- Stat Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-[#e8ebf3] dark:border-gray-800 shadow-sm">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Workforce</p>
                        <h3 id="statEmployees" class="text-3xl font-bold mt-2">0</h3>
                        <div class="flex items-center gap-1 text-emerald-500 text-xs mt-2 font-bold">
                            <span class="material-symbols-outlined text-xs">trending_up</span>
                            <span>+4% vs last month</span>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-[#e8ebf3] dark:border-gray-800 shadow-sm">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Security Roles</p>
                        <h3 id="statRoles" class="text-3xl font-bold mt-2">0</h3>
                        <div class="flex items-center gap-1 text-primary text-xs mt-2 font-bold">
                            <span class="material-symbols-outlined text-xs">verified</span>
                            <span>RBAC Active</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Dept Distribution -->
                    <div
                        class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-[#e8ebf3] dark:border-gray-800 shadow-sm">
                        <h4 class="text-sm font-bold mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">pie_chart</span>
                            Departmental Headcount
                        </h4>
                        <div class="h-64 relative">
                            <canvas id="deptChart"></canvas>
                        </div>
                    </div>
                    <!-- Role Distribution -->
                    <div
                        class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-[#e8ebf3] dark:border-gray-800 shadow-sm">
                        <h4 class="text-sm font-bold mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">bar_chart</span>
                            Role Distribution
                        </h4>
                        <div class="h-64 relative">
                            <canvas id="roleChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Growth Trend -->
                <div
                    class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-[#e8ebf3] dark:border-gray-800 shadow-sm">
                    <h4 class="text-sm font-bold mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">show_chart</span>
                        Organizational Growth Trend
                    </h4>
                    <div class="h-72 relative">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        async function loadAnalytics() {
            try {
                const response = await fetch('../../Backend/api/get_analytics_data.php');
                const data = await response.json();

                if (data.error) throw new Error(data.error);

                // Update Stats
                document.getElementById('statEmployees').innerText = data.summary.total_employees;
                document.getElementById('statDepts').innerText = data.summary.total_departments;
                document.getElementById('statRoles').innerText = data.summary.total_roles;

                // Dept Chart
                new Chart(document.getElementById('deptChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.dept_distribution.map(d => d.name),
                        datasets: [{
                            data: data.dept_distribution.map(d => d.count),
                            backgroundColor: ['#16439c', '#d4af37', '#e2e8f0', '#94a3b8', '#475569'],
                            borderWidth: 0
                        }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                });

                // Role Chart
                new Chart(document.getElementById('roleChart'), {
                    type: 'bar',
                    data: {
                        labels: data.role_distribution.map(r => r.role_name),
                        datasets: [{
                            label: 'Headcount',
                            data: data.role_distribution.map(r => r.count),
                            backgroundColor: '#16439c',
                            borderRadius: 8
                        }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });

                // Growth Chart
                new Chart(document.getElementById('growthChart'), {
                    type: 'line',
                    data: {
                        labels: data.growth_trend.map(g => g.month),
                        datasets: [{
                            label: 'New Hires',
                            data: data.growth_trend.map(g => g.count),
                            borderColor: '#16439c',
                            backgroundColor: 'rgba(22, 67, 156, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#16439c',
                            pointBorderWidth: 2
                        }]
                    },
                    options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
                });

            } catch (err) {
                console.error("Analytics Load Error:", err);
            }
        }

        document.addEventListener('DOMContentLoaded', loadAnalytics);
        document.addEventListener('DOMContentLoaded', loadAnalytics);

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