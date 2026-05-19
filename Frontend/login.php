<?php
session_start();
require_once "../Backend/config/db.php";
require_once "../Backend/classes/User.php";
require_once "../Backend/config/encryption.php";

$error = "";
$success = "";

// Initialize User object
$userObj = new User($pdo);

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
    exit();
}

// Success message after registration
// Success message after registration
if (isset($_GET["registered"])) {
    $success = "Account created successfully! Please login.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } else {
        $user = $userObj->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {

            // ── 2FA CHECK ─────────────────────────────────────────────────
            if (!empty($user['two_fa_enabled']) && !empty($user['two_fa_pin'])) {
                // Store temp session — full login not complete until PIN verified
                session_regenerate_id(true);
                $_SESSION['2fa_user_id'] = $user['id'];
                unset($_SESSION['user_id']); // ensure no partial auth
                header("Location: auth/two_fa.php");
                exit();
            }
            // ──────────────────────────────────────────────────────────────

            // --- SECURE SESSION MANAGEMENT ---
            // 1. Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            $newSessionId = session_id();

            // 2. Store session in database for Global Logout tracking
            $userObj->addSession($user['id'], $newSessionId);

            // 3. Store user data in SESSION
            $_SESSION['user_id'] = encrypt($user['id']);
            $_SESSION['raw_user_id'] = $user['id']; // Storing raw ID for faster lookups
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_name'] = $user['role_name'] ?? 'Employee';
            $_SESSION['avatar_url'] = $user['avatar_url'];
            if (isset($user['role_id']))
                $_SESSION['role_id'] = $user['role_id'];

            if (isset($_POST['remember'])) {
                // 4. Generate and store secure COOKIE for persistent login
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days

                $userObj->storeToken($user['id'], $token, $expiry);

                setcookie(
                    'remember_me',
                    $token,
                    [
                        'expires' => time() + (30 * 24 * 60 * 60),
                        'path' => '/',
                        'domain' => '',
                        'secure' => isset($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );
            }

            header("Location: dashboard/index.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Login - Corporate Portal</title>
    <!-- Theme Initialization Script -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
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
    <style type="text/tailwindcss">
        body {
            font-family: 'Inter', sans-serif;
        }
        .form-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col font-display">
    <header
        class="flex items-center justify-between whitespace-nowrap border-b border-solid border-slate-200 dark:border-slate-800 px-6 py-4 bg-white dark:bg-slate-900">
        <div class="flex items-center gap-3 text-slate-900 dark:text-white">
            <div class="w-8 h-8 text-primary">
                <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd"
                        d="M24 18.4228L42 11.475V34.3663C42 34.7796 41.7457 35.1504 41.3601 35.2992L24 42V18.4228Z"
                        fill="currentColor" fill-rule="evenodd"></path>
                    <path clip-rule="evenodd"
                        d="M24 8.18819L33.4123 11.574L24 15.2071L14.5877 11.574L24 8.18819ZM9 15.8487L21 20.4805V37.6263L9 32.9945V15.8487ZM27 37.6263V20.4805L39 15.8487V32.9945L27 37.6263ZM25.354 2.29885C24.4788 1.98402 23.5212 1.98402 22.646 2.29885L4.98454 8.65208C3.7939 9.08038 3 10.2097 3 11.475V34.3663C3 36.0196 4.01719 37.5026 5.55962 38.098L22.9197 44.7987C23.6149 45.0671 24.3851 45.0671 25.0803 44.7987L42.4404 38.098C43.9828 37.5026 45 36.0196 45 34.3663V11.475C45 10.2097 44.2061 9.08038 43.0155 8.65208L25.354 2.29885Z"
                        fill="currentColor" fill-rule="evenodd"></path>
                </svg>
            </div>
            <h2 class="text-lg font-bold leading-tight tracking-tight">Corporate Portal</h2>
        </div>
    </header>

    <main class="flex-1 flex flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-[440px]">
            <div
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl form-card p-8 md:p-10">
                <div class="mb-8 text-left">
                    <h1 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight mb-2">Welcome back</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-normal">Enter your credentials to access
                        your secure workspace.</p>
                </div>

                <?php if ($success): ?>
                    <div
                        class="p-4 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 flex items-start gap-3 mb-4">
                        <span class="material-symbols-outlined text-emerald-500 text-lg mt-0.5">check_circle</span>
                        <p class="text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
                            <?php echo htmlspecialchars($success); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div
                        class="p-4 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 flex items-start gap-3 mb-4 animate-pulse">
                        <span class="material-symbols-outlined text-rose-500 text-lg mt-0.5">error</span>
                        <div>
                            <p class="text-rose-600 dark:text-rose-400 text-sm font-bold">Authentication Failed</p>
                            <p class="text-rose-500 dark:text-rose-500/80 text-xs"><?php echo htmlspecialchars($error); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div class="flex flex-col">
                        <label class="flex flex-col w-full">
                            <p class="text-slate-900 dark:text-slate-200 text-sm font-semibold pb-2">Email Address</p>
                            <input
                                class="form-input flex w-full rounded-lg text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary h-12 px-4 text-base transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500"
                                placeholder="name@company.com" required type="email" name="email"
                                value="<?php echo htmlspecialchars($email ?? ''); ?>" />
                        </label>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center pb-2">
                            <p class="text-slate-900 dark:text-slate-200 text-sm font-semibold">Password</p>
                            <a class="text-primary text-xs font-semibold hover:underline"
                                href="forgot-password.php">Forgot password?</a>
                        </div>
                        <label class="flex w-full items-stretch relative">
                            <input id="passwordInput"
                                class="form-input flex w-full rounded-lg text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary h-12 px-4 text-base transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500 pr-12"
                                placeholder="Enter your password" required type="password" name="password" />
                            <div id="togglePassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer flex items-center">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between py-2">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="remember"
                                class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer">
                            <span
                                class="text-sm text-slate-600 dark:text-slate-400 font-medium group-hover:text-slate-900 dark:group-hover:text-slate-200 transition-colors">Remember
                                me</span>
                        </label>
                    </div>

                    <div class="pt-2 flex flex-col gap-3">
                        <button
                            class="w-full h-12 flex items-center justify-center rounded-lg bg-primary text-white font-bold text-base hover:bg-primary/95 transition-all shadow-sm active:scale-[0.98]"
                            type="submit">
                            Sign In
                        </button>
                    </div>
                </form>

                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white dark:bg-slate-900 px-2 text-slate-400 font-medium tracking-tight">Security
                            Verified</span>
                    </div>
                </div>

                <div class="text-center mt-6">
                    <p class="text-slate-500 dark:text-slate-400 text-xs font-medium">
                        Don't have an account?
                        <a href="register.php" class="text-primary font-bold hover:underline">Create an account</a>
                    </p>
                </div>
            </div>

            <div
                class="mt-8 flex items-center justify-center gap-4 text-slate-400 dark:text-slate-500 text-xs font-medium">
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">lock</span>
                    <span>JWT Authentication</span>
                </div>
                <div class="size-1 bg-slate-300 dark:bg-slate-700 rounded-full"></div>
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">verified_user</span>
                    <span>Secure Cookies</span>
                </div>
            </div>
        </div>
    </main>

    <footer class="px-6 py-6 border-t border-slate-200 dark:border-slate-800 text-center">
        <p class="text-slate-400 dark:text-slate-500 text-sm">
            © 2024 Corporate Systems Inc. All rights reserved.
        </p>
    </footer>

    <script>
        const passwordInput = document.getElementById('passwordInput');
        const togglePassword = document.getElementById('togglePassword');
        const icon = togglePassword.querySelector('span');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            icon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
        });
    </script>
</body>

</html>