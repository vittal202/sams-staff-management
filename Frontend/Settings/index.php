<?php
require_once "../../Backend/auth/auth_check.php";
require_once "../../Backend/config/db.php";
checkAccess(); // Ensure user is logged in

$userId = $_SESSION['raw_user_id'];
// Explicitly select columns to ensure we get what we expect
$stmt = $pdo->prepare("SELECT id, full_name, email, language, timezone, theme, compact_view, email_notifications, push_notifications, two_fa_enabled, avatar_url FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // If user not found, force logout or handle error
    error_log("CRITICAL: User ID $userId not found in database settings page.");
    header("Location: ../../Frontend/login.php");
    exit();
}

// Debug: Log the language value
error_log("DEBUG: User ID: " . $userId);
error_log("DEBUG: Language from DB: " . ($user['language'] ?? 'NULL'));

// Ensure default settings if columns are missing or values are null
$user['language'] = $user['language'] ?? 'en';
$user['timezone'] = $user['timezone'] ?? 'UTC';
$user['theme'] = $user['theme'] ?? 'light';
$user['compact_view'] = $user['compact_view'] ?? 0;
$user['email_notifications'] = $user['email_notifications'] ?? 1;
$user['push_notifications'] = $user['push_notifications'] ?? 0;

// Debug: Log after defaults applied
error_log("DEBUG: Language after defaults: " . $user['language']);
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Platform &amp; Profile Settings - OrgChart Pro</title>
    <!-- Theme Initialization Script -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script src="../shared/toast.js"></script>
    <?php if ($user['compact_view']): ?>
        <style>
            /* Compact View Overrides - reducing whitespace for data density */
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

            /* Compact Sidebar Item Tweaks */
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
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        href="../Employees/index.php">
                        <span class="material-symbols-outlined">group</span>
                        <p class="text-sm">Staff</p>
                    </a>

                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-semibold"
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
                                <div id="sidebarProfileImage" class="size-9 rounded-full bg-cover bg-center border-2 border-primary/30 shadow"
                                    style='background-image: url("<?php echo !empty($user['avatar_url']) ? $user['avatar_url'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? 'User') . '&background=16439c&color=fff'; ?>");'>
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 size-2.5 bg-emerald-400 border-2 border-white dark:border-slate-800 rounded-full"></span>
                            </div>
                            <!-- Name & Role -->
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold truncate text-slate-800 dark:text-white leading-tight">
                                    <?php echo htmlspecialchars($user['full_name'] ?? ($_SESSION['role_name'] ?? 'User')); ?>
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
                        <input
                            class="w-full bg-[#f0f2f5] dark:bg-slate-800 border-none rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary/50 transition-all"
                            placeholder="Search settings..." type="text" />
                    </div>
                </div>
                <div class="flex items-center gap-4">
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
                    <div class="h-6 w-[1px] bg-slate-200 dark:bg-gray-700 mx-2"></div>
                    <a href="../Support/index.php"
                        class="flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400">
                        <span class="material-symbols-outlined">help_outline</span>
                        Support
                    </a>
                </div>
            </header>
            <div class="flex-1 overflow-auto bg-[#f8fafc] dark:bg-slate-950 p-8">
                <div class="max-w-4xl mx-auto">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">Settings</h2>
                        <p class="text-sm text-slate-500 mt-1">Manage your account and platform preferences.</p>
                    </div>
                    <div class="flex flex-col gap-6">
                        <section
                            class="bg-white dark:bg-slate-900 border border-[#e8ebf3] dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                            <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-800">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Profile Settings</h3>
                                <p class="text-xs text-slate-500">Update your personal information and profile picture.
                                </p>
                            </div>
                            <div class="p-6 flex flex-col gap-6">
                                <div class="flex items-center gap-6">
                                    <div class="relative">
                                        <!-- Dynamic Profile Picture -->
                                        <div id="profileImagePreview"
                                            class="size-20 rounded-full bg-cover bg-center border-2 border-slate-200"
                                            style='background-image: url("<?php echo !empty($user['avatar_url']) ? $user['avatar_url'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? 'User') . '&background=16439c&color=fff'; ?>");'>
                                        </div>

                                        <!-- Hidden File Input -->
                                        <input type="file" id="avatarUpload" accept="image/png, image/jpeg, image/gif"
                                            class="hidden" style="display:none;" />

                                        <button onclick="document.getElementById('avatarUpload').click()"
                                            class="absolute bottom-0 right-0 size-7 bg-primary text-white rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900 hover:bg-blue-800 transition-colors">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </button>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Profile Picture
                                        </h4>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400">Full
                                            Name</label>
                                        <input id="fullNameInput"
                                            class="bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-gray-700 rounded-lg text-sm px-4 py-2 focus:ring-primary focus:border-primary"
                                            type="text"
                                            value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" />
                                    </div>
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400">Email
                                            Address</label>
                                        <input id="emailInput"
                                            class="bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-gray-700 rounded-lg text-sm px-4 py-2 focus:ring-primary focus:border-primary"
                                            type="email"
                                            value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" />
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section
                            class="bg-white dark:bg-slate-900 border border-[#e8ebf3] dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                            <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-800">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Security</h3>
                                <p class="text-xs text-slate-500">Secure your account with multi-factor authentication.
                                </p>
                            </div>
                            <div class="p-6 flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-slate-400">lock_reset</span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Change
                                                Password</p>
                                            <p class="text-xs text-slate-500">Update your account password regularly for
                                                better security.</p>
                                        </div>
                                    </div>
                                    <button id="updatePasswordBtn"
                                        class="px-4 py-2 border border-slate-200 dark:border-gray-700 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                        Update
                                    </button>
                                </div>
                                <div class="h-px bg-slate-100 dark:bg-gray-800"></div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-slate-400">verified_user</span>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                                    Two-Factor Authentication</p>
                                                <?php if (!empty($user['two_fa_enabled'])): ?>
                                                    <span
                                                        class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded uppercase tracking-wider">Enabled</span>
                                                <?php else: ?>
                                                    <span
                                                        class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded uppercase tracking-wider">Disabled</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-xs text-slate-500">Adds an extra layer of security to your
                                                account login.</p>
                                        </div>
                                    </div>
                                    <?php if (!empty($user['two_fa_enabled'])): ?>
                                    <button onclick="disable2FA()"
                                        class="px-4 py-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-sm font-semibold rounded-lg transition-colors">
                                        Deactivate
                                    </button>
                                    <?php else: ?>
                                    <button onclick="activate2FA()"
                                        class="px-4 py-2 text-primary hover:bg-blue-50 dark:hover:bg-primary/10 text-sm font-semibold rounded-lg transition-colors">
                                        Activate
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </section>
                        <section
                            class="bg-white dark:bg-slate-900 border border-[#e8ebf3] dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                            <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-800">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Appearance</h3>
                                <p class="text-xs text-slate-500">Customize how the platform looks and feels.</p>
                            </div>
                            <div class="p-6 flex flex-col gap-4">
                                <div
                                    class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-slate-400">dark_mode</span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Dark Mode
                                            </p>
                                            <p class="text-xs text-slate-500">Switch between light and dark themes.</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input id="darkModeToggle" class="sr-only peer" type="checkbox" <?php echo $user['theme'] === 'dark' ? 'checked' : ''; ?> />
                                        <div
                                            class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary">
                                        </div>
                                    </label>
                                </div>
                                <div
                                    class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-slate-400">density_medium</span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Compact View
                                            </p>
                                            <p class="text-xs text-slate-500">Reduce padding for a more data-dense
                                                interface.</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input id="compactViewToggle" class="sr-only peer" type="checkbox" <?php echo $user['compact_view'] ? 'checked' : ''; ?> />
                                        <div
                                            class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
            <footer
                class="h-16 border-t border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex items-center justify-end px-8 gap-4">
                <button id="discardBtn"
                    class="px-6 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors">
                    Discard
                </button>
                <button id="saveSettingsBtn"
                    class="px-8 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-blue-800 shadow-md shadow-primary/20 transition-all">
                    Save Changes
                </button>
            </footer>
        </main>
    </div>

    <!-- PIN Setup Modal (for enabling 2FA) -->
    <div id="pinSetupModal"
        class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-sm p-8 shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Set Your Security PIN</h3>
                <button onclick="closePinModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <p class="text-xs text-slate-500 mb-6">Choose a 6-digit PIN. You'll enter this every time you log in.</p>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">New PIN</label>
                    <input type="password" id="pinInput" maxlength="6" inputmode="numeric" pattern="[0-9]*"
                        placeholder="••••••"
                        class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm text-center tracking-[0.5em] font-bold focus:ring-2 focus:ring-primary/50 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Confirm PIN</label>
                    <input type="password" id="pinConfirm" maxlength="6" inputmode="numeric" pattern="[0-9]*"
                        placeholder="••••••"
                        class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm text-center tracking-[0.5em] font-bold focus:ring-2 focus:ring-primary/50 outline-none">
                </div>
                <p id="pinError" class="text-xs text-rose-500 hidden"></p>
                <div class="flex gap-3 pt-2">
                    <button onclick="closePinModal()"
                        class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                    <button onclick="submitPin()"
                        class="flex-1 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-blue-800 transition-colors shadow-lg shadow-primary/25">Enable 2FA</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Password Modal -->
    <div id="passwordModal"
        class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md p-8 shadow-2xl transform scale-95 transition-transform duration-300">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Update Password</h3>
                <button id="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="passwordUpdateForm" class="space-y-4">
                <input type="hidden" name="action" value="update_password">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Current
                        Password</label>
                    <input type="password" name="current_password" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">New
                        Password</label>
                    <input type="password" name="new_password" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Confirm New
                        Password</label>
                    <input type="password" name="confirm_password" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 outline-none">
                </div>
                <div class="pt-4 flex gap-3">
                    <button type="button" id="cancelBtn"
                        class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-blue-800 transition-colors shadow-lg shadow-primary/25">Update
                        Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Container (Managed by toast.js) -->

    <!-- 2FA Deactivate Confirmation Modal -->
    <div id="disable2FAModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[200] flex items-center justify-center hidden opacity-0 transition-all duration-300">
        <div id="disable2FAContent"
            class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-sm p-8 shadow-2xl transform scale-90 transition-all duration-300 border border-slate-200 dark:border-slate-800 mx-4">
            <!-- Icon -->
            <div class="flex flex-col items-center text-center">
                <div class="size-20 rounded-2xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center mb-5 relative">
                    <span class="material-symbols-outlined text-rose-500 text-4xl" style="font-variation-settings:'FILL' 1">shield_lock</span>
                    <span class="absolute -top-1 -right-1 size-6 bg-amber-400 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-sm" style="font-size:14px">warning</span>
                    </span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Disable 2FA Protection?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-2">
                    You will no longer need a <span class="font-bold text-slate-700 dark:text-slate-200">6-digit PIN</span> to log in.
                </p>
                <div class="w-full bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl px-4 py-3 flex items-start gap-2 mb-6 text-left">
                    <span class="material-symbols-outlined text-amber-500 text-base mt-0.5 shrink-0">info</span>
                    <p class="text-xs text-amber-700 dark:text-amber-300 font-medium">
                        This reduces your account security. Anyone with your password could access your account.
                    </p>
                </div>
            </div>
            <!-- Buttons -->
            <div class="flex flex-col gap-3">
                <button id="confirmDisable2FABtn"
                    class="w-full py-3.5 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl text-sm font-bold transition-all active:scale-[0.98] shadow-lg shadow-rose-500/25 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">lock_open</span>
                    Yes, Disable 2FA
                </button>
            </div>
        </div>
    </div>

    <script>
        // toast logic handled by toast.js

        // ── 2FA PIN Management ────────────────────────────────────────────────
        const pinSetupModal    = document.getElementById('pinSetupModal');
        const pinSetupContent  = pinSetupModal.querySelector('div');

        function activate2FA() {
            document.getElementById('pinInput').value   = '';
            document.getElementById('pinConfirm').value = '';
            document.getElementById('pinError').classList.add('hidden');
            pinSetupModal.classList.remove('hidden');
            setTimeout(() => {
                pinSetupModal.classList.replace('opacity-0', 'opacity-100');
                pinSetupContent.classList.replace('scale-95', 'scale-100');
                document.getElementById('pinInput').focus();
            }, 10);
        }

        function closePinModal() {
            pinSetupModal.classList.replace('opacity-100', 'opacity-0');
            pinSetupContent.classList.replace('scale-100', 'scale-95');
            setTimeout(() => pinSetupModal.classList.add('hidden'), 300);
        }

        async function submitPin() {
            const pin        = document.getElementById('pinInput').value.trim();
            const confirmPin = document.getElementById('pinConfirm').value.trim();
            const errEl      = document.getElementById('pinError');

            if (!/^\d{6}$/.test(pin)) {
                errEl.textContent = 'PIN must be exactly 6 digits.';
                errEl.classList.remove('hidden');
                return;
            }
            if (pin !== confirmPin) {
                errEl.textContent = 'PINs do not match.';
                errEl.classList.remove('hidden');
                return;
            }

            errEl.classList.add('hidden');

            try {
                const res  = await fetch('../../Backend/api/update_security.php', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify({ action: 'set_2fa_pin', pin, confirm_pin: confirmPin })
                });
                const data = await res.json();

                if (data.success) {
                    closePinModal();
                    showToast('2FA enabled! PIN set successfully.', 'success');
                    setTimeout(() => window.location.reload(), 1300);
                } else {
                    errEl.textContent = data.message || 'Failed to set PIN.';
                    errEl.classList.remove('hidden');
                }
            } catch (err) {
                errEl.textContent = 'Connection error. Please try again.';
                errEl.classList.remove('hidden');
            }
        }

        // ── 2FA Disable Confirmation Modal ─────────────────────────────────
        const disable2FAModal   = document.getElementById('disable2FAModal');
        const disable2FAContent = document.getElementById('disable2FAContent');

        function disable2FA() {
            disable2FAModal.classList.remove('hidden');
            setTimeout(() => {
                disable2FAModal.classList.replace('opacity-0', 'opacity-100');
                disable2FAContent.classList.replace('scale-90', 'scale-100');
            }, 10);
        }

        function closeDisable2FAModal() {
            disable2FAModal.classList.replace('opacity-100', 'opacity-0');
            disable2FAContent.classList.replace('scale-100', 'scale-90');
            setTimeout(() => disable2FAModal.classList.add('hidden'), 300);
        }

        async function doDisable2FA() {
            const btn = document.getElementById('confirmDisable2FABtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin material-symbols-outlined text-base">progress_activity</span> Disabling...';
            try {
                const res  = await fetch('../../Backend/api/update_security.php', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify({ action: 'toggle_2fa', status: 0 })
                });
                const data = await res.json();
                if (data.success) {
                    closeDisable2FAModal();
                    showToast('2FA has been disabled.', 'info');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showToast(data.message || 'Failed to disable 2FA.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<span class="material-symbols-outlined text-base">lock_open</span> Yes, Disable 2FA';
                }
            } catch (err) {
                showToast('Connection error. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined text-base">lock_open</span> Yes, Disable 2FA';
            }
        }

        document.getElementById('confirmDisable2FABtn').addEventListener('click', doDisable2FA);
        disable2FAModal.addEventListener('click', (e) => { if (e.target === disable2FAModal) closeDisable2FAModal(); });
        // ──────────────────────────────────────────────────────────────────────

        // Close PIN modal on backdrop click
        pinSetupModal.addEventListener('click', (e) => { if (e.target === pinSetupModal) closePinModal(); });
        // ─────────────────────────────────────────────────────────────────────


        const modal = document.getElementById('passwordModal');
        const content = modal.querySelector('div');
        const btn = document.getElementById('updatePasswordBtn');
        const closeBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');

        function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.replace('opacity-0', 'opacity-100');
                content.classList.replace('scale-95', 'scale-100');
            }, 10);
        }

        function close() {
            modal.classList.replace('opacity-100', 'opacity-0');
            content.classList.replace('scale-100', 'scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        btn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        modal.addEventListener('click', (e) => { if (e.target === modal) close(); });

        modal.addEventListener('click', (e) => { if (e.target === modal) close(); });

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

        // Dark Mode Logic
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;

        if (localStorage.getItem('theme') === 'dark' || html.classList.contains('dark')) {
            darkModeToggle.checked = true;
            html.classList.add('dark');
            html.classList.remove('light');
        }

        darkModeToggle.addEventListener('change', () => {
            if (darkModeToggle.checked) {
                html.classList.add('dark');
                html.classList.remove('light');
                localStorage.setItem('theme', 'dark');
            } else {
                html.classList.remove('dark');
                html.classList.add('light');
                localStorage.setItem('theme', 'light');
            }
        });


        // ===== SAVE CHANGES LOGIC =====
        document.addEventListener('DOMContentLoaded', function () {
            console.log('🚀 Settings page loaded');

            // Get all elements
            const saveBtn = document.getElementById('saveSettingsBtn');
            const discardBtn = document.getElementById('discardBtn');
            const fullNameInput = document.getElementById('fullNameInput');
            const emailInput = document.getElementById('emailInput');
            const compactViewToggle = document.getElementById('compactViewToggle');
            const darkModeToggle = document.getElementById('darkModeToggle');

            if (discardBtn) {
                discardBtn.addEventListener('click', () => {
                    window.location.reload();
                });
            }

            // Log what we found
            console.log('✅ Save Button:', saveBtn ? 'Found' : '❌ NOT FOUND');
            console.log('✅ Full Name:', fullNameInput ? 'Found' : '❌ NOT FOUND');
            console.log('✅ Email:', emailInput ? 'Found' : '❌ NOT FOUND');
            console.log('✅ Compact View:', compactViewToggle ? 'Found' : '❌ NOT FOUND');
            console.log('✅ Dark Mode:', darkModeToggle ? 'Found' : '❌ NOT FOUND');
            console.log('✅ Email Notifications:', document.getElementById('emailNotificationsToggle') ? 'Found' : '❌ NOT FOUND');
            console.log('✅ Push Notifications:', document.getElementById('pushNotificationsToggle') ? 'Found' : '❌ NOT FOUND');

            if (!saveBtn) {
                console.error('❌ CRITICAL: Save button not found!');
                alert('Error: Save button not found. Please refresh the page.');
                return;
            }

            // Helper function for quick sync (Real-time updates)
            async function syncSetting(key, value) {
                console.log(`🔄 [Sync] ${key} = ${value}`);
                try {
                    const data = {
                        action: 'update_preferences',
                        [key]: value
                    };
                    const response = await fetch('../../Backend/api/update_settings.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    if (result.success) {
                        console.log(`✅ [Sync] Successfully persisted ${key}`);
                        showToast(`Setting persisted: ${key.replace('_', ' ')}`, 'info');
                    } else {
                        console.warn(`⚠️ [Sync] Server returned error: ${result.message}`);
                    }
                } catch (error) {
                    console.error('❌ [Sync] Exception:', error);
                }
            }

            // Attach real-time sync to appearance toggles
            if (darkModeToggle) {
                darkModeToggle.addEventListener('change', () => {
                    syncSetting('theme', darkModeToggle.checked ? 'dark' : 'light');
                });
            }
            if (compactViewToggle) {
                compactViewToggle.addEventListener('change', () => {
                    syncSetting('compact_view', compactViewToggle.checked ? 1 : 0);
                });
            }

            // Attach click handler for manual save
            saveBtn.addEventListener('click', async function () {
                console.log('🔵 [Manual Save] Button clicked');

                try {
                    // Disable button
                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Saving...';

                    // Safely get notification states
                    const emailNotifEl = document.getElementById('emailNotificationsToggle');
                    const pushNotifEl = document.getElementById('pushNotificationsToggle');

                    // Explicitly get input elements
                    const fullNameInput = document.getElementById('fullNameInput');
                    const emailInput = document.getElementById('emailInput');
                    const darkModeToggle = document.getElementById('darkModeToggle'); // Ensure this ID matches your HTML
                    const compactViewToggle = document.getElementById('compactViewToggle'); // Ensure this ID matches your HTML

                    console.log('🔵 [Manual Save] Toggles found:', {
                        email: !!emailNotifEl,
                        push: !!pushNotifEl,
                        name: !!fullNameInput,
                        emailInput: !!emailInput
                    });

                    // Collect data
                    const data = {
                        action: 'update_preferences',
                        full_name: fullNameInput ? fullNameInput.value : '',
                        email: emailInput ? emailInput.value : '',
                        theme: darkModeToggle && darkModeToggle.checked ? 'dark' : 'light',
                        compact_view: compactViewToggle && compactViewToggle.checked ? 1 : 0,
                        email_notifications: emailNotifEl ? (emailNotifEl.checked ? 1 : 0) : 1,
                        push_notifications: pushNotifEl ? (pushNotifEl.checked ? 1 : 0) : 0
                    };

                    console.log('📤 [Manual Save] Sending data:', data);

                    // Send request
                    console.log('📤 [Manual Save] Starting fetch to ../../Backend/api/update_settings.php');
                    const response = await fetch('../../Backend/api/update_settings.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    console.log('📥 [Manual Save] Response status:', response.status);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('📥 [Manual Save] Response data:', result);

                    if (result.success) {
                        console.log('✅ [Manual Save] Success! Redirecting...');
                        showToast('Settings saved. Redirecting to dashboard...', 'success');
                        setTimeout(() => {
                            window.location.href = '../dashboard/index.php';
                        }, 1500);
                    } else {
                        console.error('❌ [Manual Save] Server failed:', result.message);
                        showToast(result.message || 'Failed to save settings', 'error');
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Save Changes';
                    }

                } catch (error) {
                    console.error('❌ [Manual Save] Exception occurred:', error);
                    // Use a fallback alert in case toast system is failing
                    if (window.showToast) {
                        showToast('An unexpected error occurred: ' + error.message, 'error');
                    } else {
                        alert('Error: ' + error.message);
                    }
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Changes';
                }
            });

            console.log('✅ Save handler attached successfully');

            // Profile Picture Upload Handler
            const avatarUpload = document.getElementById('avatarUpload');
            if (avatarUpload) {
                avatarUpload.addEventListener('change', async function (e) {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        const formData = new FormData();
                        formData.append('profile_picture', file);

                        // Optimistic Preview
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            document.getElementById('profileImagePreview').style.backgroundImage = `url('${e.target.result}')`;
                        }
                        reader.readAsDataURL(file);

                        try {
                            const response = await fetch('../../Backend/api/upload_profile_picture.php', {
                                method: 'POST',
                                body: formData
                            });

                            const result = await response.json();

                            if (result.success) {
                                console.log('✅ Profile picture uploaded:', result.url);
                                const newUrl = `url('${result.url}')`;
                                document.getElementById('profileImagePreview').style.backgroundImage = newUrl;

                                const sidebarImg = document.getElementById('sidebarProfileImage');
                                if (sidebarImg) {
                                    sidebarImg.style.backgroundImage = newUrl;
                                }
                                showToast('Profile picture updated successfully.', 'success');
                            } else {
                                showToast('Upload failed: ' + result.message, 'error');
                            }
                        } catch (error) {
                            console.error('❌ Upload error:', error);
                            showToast('Error uploading profile picture.', 'error');
                        }
                    }
                });
            }
            // Password Update Handler
            const passwordForm = document.getElementById('passwordUpdateForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.textContent;
                    
                    try {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Updating...';
                        
                        const formData = new FormData(this);
                        const response = await fetch('../../Backend/api/update_security.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showToast(result.message, 'success');
                            this.reset();
                            close(); // Close the modal
                        } else {
                            showToast(result.message, 'error');
                        }
                    } catch (error) {
                        console.error('❌ Password update error:', error);
                        showToast('An unexpected error occurred.', 'error');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalBtnText;
                    }
                });
            }
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