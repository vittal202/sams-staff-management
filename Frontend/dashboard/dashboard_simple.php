<?php
require_once "../../Backend/auth/auth_guard.php";
// $user array is now available from auth_guard.php
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Dashboard - Welcome</title>

    <!-- Theme Initialization Script -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
                    },
                },
            },
        }
    </script>
</head>

<body class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col font-display">
    <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-slate-200 dark:border-slate-800 px-6 py-4 bg-white dark:bg-slate-900">
        <div class="flex items-center gap-3 text-slate-900 dark:text-white">
            <div class="w-8 h-8 text-primary">
                <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" d="M24 18.4228L42 11.475V34.3663C42 34.7796 41.7457 35.1504 41.3601 35.2992L24 42V18.4228Z" fill="currentColor" fill-rule="evenodd"></path>
                    <path clip-rule="evenodd" d="M24 8.18819L33.4123 11.574L24 15.2071L14.5877 11.574L24 8.18819ZM9 15.8487L21 20.4805V37.6263L9 32.9945V15.8487ZM27 37.6263V20.4805L39 15.8487V32.9945L27 37.6263ZM25.354 2.29885C24.4788 1.98402 23.5212 1.98402 22.646 2.29885L4.98454 8.65208C3.7939 9.08038 3 10.2097 3 11.475V34.3663C3 36.0196 4.01719 37.5026 5.55962 38.098L22.9197 44.7987C23.6149 45.0671 24.3851 45.0671 25.0803 44.7987L42.4404 38.098C43.9828 37.5026 45 36.0196 45 34.3663V11.475C45 10.2097 44.2061 9.08038 43.0155 8.65208L25.354 2.29885Z" fill="currentColor" fill-rule="evenodd"></path>
                </svg>
            </div>
            <h2 class="text-lg font-bold leading-tight tracking-tight">Dashboard</h2>
        </div>
        <a href="../../Backend/auth/logout_jwt.php" class="flex min-w-[84px] cursor-pointer items-center justify-center rounded-lg h-10 px-4 bg-rose-500 text-white text-sm font-bold transition-all hover:bg-rose-600">
            <span>Logout</span>
        </a>
    </header>

    <main class="flex-1 flex flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-4xl">
            <!-- Welcome Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-8 md:p-10 mb-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="size-16 rounded-full bg-primary text-white flex items-center justify-center text-2xl font-bold">
                        <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                    </div>
                    <div>
                        <h1 class="text-slate-900 dark:text-white text-3xl font-bold tracking-tight">
                            Welcome, <?php echo htmlspecialchars($user['username']); ?>!
                        </h1>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- User Info Card -->
                    <div class="p-4 bg-blue-50 dark:bg-blue-950/30 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-blue-500">person</span>
                            <h3 class="font-semibold text-slate-900 dark:text-white">User ID</h3>
                        </div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">#<?php echo $user['id']; ?></p>
                    </div>

                    <!-- Session Status Card -->
                    <div class="p-4 bg-green-50 dark:bg-green-950/30 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-green-500">verified_user</span>
                            <h3 class="font-semibold text-slate-900 dark:text-white">Status</h3>
                        </div>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">Authenticated</p>
                    </div>

                    <!-- JWT Token Card -->
                    <div class="p-4 bg-purple-50 dark:bg-purple-950/30 rounded-lg border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-purple-500">key</span>
                            <h3 class="font-semibold text-slate-900 dark:text-white">JWT Token</h3>
                        </div>
                        <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Active</p>
                    </div>
                </div>
            </div>

            <!-- Session Details -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-8 md:p-10">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Session Information</h2>
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-600 dark:text-slate-400">Username:</span>
                        <span class="font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-600 dark:text-slate-400">Email:</span>
                        <span class="font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-slate-600 dark:text-slate-400">Session ID:</span>
                        <span class="font-mono text-sm text-slate-900 dark:text-white"><?php echo session_id(); ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-600 dark:text-slate-400">JWT Token:</span>
                        <span class="font-mono text-xs text-slate-600 dark:text-slate-400 truncate max-w-md">
                            <?php echo isset($_SESSION['jwt_token']) ? substr($_SESSION['jwt_token'], 0, 30) . '...' : 'Not available'; ?>
                        </span>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-blue-500 mt-0.5">info</span>
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Protected Dashboard</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                This page is protected by JWT authentication. Your token is validated on every page load to ensure secure access.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="px-6 py-6 border-t border-slate-200 dark:border-slate-800 text-center">
        <p class="text-slate-400 dark:text-slate-500 text-sm">
            © 2024 Corporate Systems Inc. All rights reserved.
        </p>
    </footer>
</body>

</html>
