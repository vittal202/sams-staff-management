<?php
session_start();
require_once "../Backend/config/db.php";
require_once "../Backend/classes/User.php";
require_once "../Backend/config/encryption.php";

$userObj = new User($pdo);

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username)) {
        $error = "Username is required.";
    } elseif (empty($email)) {
        $error = "Email address is required.";
    } elseif (empty($password)) {
        $error = "Password is required.";
    } elseif ($userObj->findByUsername($username)) {
        $error = "Username already taken.";
    } elseif ($userObj->findByEmail($email)) {
        $error = "Email already registered.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W\_]).{10,}$/', $password)) {
        $error = "Password must be at least 10 characters and include uppercase, lowercase, numbers, and special characters.";
    } else {
        if ($userObj->create($username, $email, $password)) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $error = $userObj->error ?: "Something went wrong.";
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Register - Corporate Portal</title>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
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
        body { font-family: 'Inter', sans-serif; }
        .form-card { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03); }
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
        <a href="login.php"
            class="flex min-w-[84px] cursor-pointer items-center justify-center rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold transition-all hover:bg-primary/90">
            <span>Sign In</span>
        </a>
    </header>

    <main class="flex-1 flex flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-[440px]">
            <div
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl form-card p-8 md:p-10">
                <div class="mb-8 text-left">
                    <h1 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight mb-2">Create Account
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-normal">Join our secure platform today.
                    </p>
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
                        class="p-4 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 flex items-start gap-3 mb-4">
                        <span class="material-symbols-outlined text-rose-500 text-lg mt-0.5">error</span>
                        <div>
                            <p class="text-rose-600 dark:text-rose-400 text-sm font-bold">Registration Error</p>
                            <p class="text-rose-500 dark:text-rose-500/80 text-xs"><?php echo htmlspecialchars($error); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div class="flex flex-col">
                        <label class="flex flex-col w-full">
                            <p class="text-slate-900 dark:text-slate-200 text-sm font-semibold pb-2">Username</p>
                            <input
                                class="form-input flex w-full rounded-lg text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary h-12 px-4 text-base transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500"
                                placeholder="johndoe" required type="text" name="username"
                                value="<?php echo htmlspecialchars($username ?? ''); ?>" />
                        </label>
                    </div>

                    <div class="flex flex-col">
                        <label class="flex flex-col w-full">
                            <p class="text-slate-900 dark:text-slate-200 text-sm font-semibold pb-2">Email Address</p>
                            <input id="emailInput"
                                class="form-input flex w-full rounded-lg text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary h-12 px-4 text-base transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500"
                                placeholder="name@company.com" required type="email" name="email"
                                value="<?php echo htmlspecialchars($email ?? ''); ?>" />
                        </label>
                    </div>



                    <div class="flex flex-col">
                        <label class="flex flex-col w-full relative">
                            <p class="text-slate-900 dark:text-slate-200 text-sm font-semibold pb-2">Password</p>
                            <div class="relative">
                                <input id="passwordInput"
                                    class="form-input flex w-full rounded-lg text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary h-12 px-4 text-base transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500 pr-12"
                                    placeholder="Minimum 10 chars, Mixed" required type="password" name="password"
                                    minlength="10" />
                                <div id="togglePassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer flex items-center">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </div>
                            </div>
                        </label>

                        <!-- Password Strength Indicator -->
                        <div id="passwordStrengthSection" class="mt-2 space-y-2">
                            <div class="flex items-center justify-between">
                                <span id="strengthLabel" class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Strength: <span>None</span></span>
                                <span id="strengthPercent" class="text-[10px] font-mono text-slate-400">0%</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div id="strengthBar" class="h-full w-0 transition-all duration-500 ease-out bg-slate-300"></div>
                            </div>
                            
                            <!-- Requirements Checklist -->
                            <div class="grid grid-cols-2 gap-x-2 gap-y-1 mt-3">
                                <div id="req-length" class="flex items-center gap-1.5 text-slate-400 transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">circle</span>
                                    <span class="text-[10px]">10+ Characters</span>
                                </div>
                                <div id="req-upper" class="flex items-center gap-1.5 text-slate-400 transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">circle</span>
                                    <span class="text-[10px]">Uppercase</span>
                                </div>
                                <div id="req-lower" class="flex items-center gap-1.5 text-slate-400 transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">circle</span>
                                    <span class="text-[10px]">Lowercase</span>
                                </div>
                                <div id="req-number" class="flex items-center gap-1.5 text-slate-400 transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">circle</span>
                                    <span class="text-[10px]">Number</span>
                                </div>
                                <div id="req-special" class="flex items-center gap-1.5 text-slate-400 transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">circle</span>
                                    <span class="text-[10px]">Special Char</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex flex-col gap-3">
                        <button id="btnCreateAccount"
                            class="w-full h-12 flex items-center justify-center rounded-lg bg-primary hover:bg-primary/95 text-white font-bold text-base transition-all shadow-sm active:scale-[0.98]"
                            type="submit">
                            Create Account
                        </button>
                    </div>
                </form>

                <script>
                    // Password Visibility Toggle
                    const passwordInput = document.getElementById('passwordInput');
                    const togglePassword = document.getElementById('togglePassword');
                    const icon = togglePassword.querySelector('span');

                    togglePassword.addEventListener('click', () => {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);
                        icon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
                    });

                    // Password Strength Logic
                    const strengthBar = document.getElementById('strengthBar');
                    const strengthLabel = document.getElementById('strengthLabel').querySelector('span');
                    const strengthPercent = document.getElementById('strengthPercent');

                    const requirements = {
                        length: { element: document.getElementById('req-length'), regex: /.{10,}/ },
                        upper: { element: document.getElementById('req-upper'), regex: /[A-Z]/ },
                        lower: { element: document.getElementById('req-lower'), regex: /[a-z]/ },
                        number: { element: document.getElementById('req-number'), regex: /[0-9]/ },
                        special: { element: document.getElementById('req-special'), regex: /[^A-Za-z0-9]/ }
                    };

                    passwordInput.addEventListener('input', () => {
                        const val = passwordInput.value;
                        let passed = 0;
                        const total = Object.keys(requirements).length;

                        for (const key in requirements) {
                            const req = requirements[key];
                            const isPassed = req.regex.test(val);
                            const icon = req.element.querySelector('span:first-child');

                            if (isPassed) {
                                passed++;
                                req.element.classList.remove('text-slate-400', 'text-rose-500');
                                req.element.classList.add('text-emerald-500');
                                icon.textContent = 'check_circle';
                            } else {
                                req.element.classList.remove('text-emerald-500');
                                if (val.length > 0) {
                                    req.element.classList.add('text-rose-500');
                                    icon.textContent = 'cancel';
                                } else {
                                    req.element.classList.add('text-slate-400');
                                    req.element.classList.remove('text-rose-500');
                                    icon.textContent = 'circle';
                                }
                            }
                        }

                        const percentage = (passed / total) * 100;
                        strengthBar.style.width = `${percentage}%`;
                        strengthPercent.textContent = `${Math.round(percentage)}%`;

                        if (percentage === 0) {
                            strengthBar.className = 'h-full w-0 transition-all duration-500 bg-slate-300';
                            strengthLabel.textContent = 'None';
                            strengthLabel.className = 'text-slate-400';
                        } else if (percentage <= 40) {
                            strengthBar.className = 'h-full transition-all duration-500 bg-rose-500';
                            strengthLabel.textContent = 'Weak';
                            strengthLabel.className = 'text-rose-500';
                        } else if (percentage <= 80) {
                            strengthBar.className = 'h-full transition-all duration-500 bg-amber-500';
                            strengthLabel.textContent = 'Medium';
                            strengthLabel.className = 'text-amber-500';
                        } else {
                            strengthBar.className = 'h-full transition-all duration-500 bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]';
                            strengthLabel.textContent = 'Strong';
                            strengthLabel.className = 'text-emerald-500';
                        }
                    });
                </script>

                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white dark:bg-slate-900 px-2 text-slate-400 font-medium tracking-tight">Secure
                            Registration</span>
                    </div>
                </div>

                <div class="text-center mt-6">
                    <p class="text-slate-500 dark:text-slate-400 text-xs font-medium">
                        Already have an account?
                        <a href="login.php" class="text-primary font-bold hover:underline">Sign in here</a>
                    </p>
                </div>
            </div>

            <div
                class="mt-8 flex items-center justify-center gap-4 text-slate-400 dark:text-slate-500 text-xs font-medium">
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">lock</span>
                    <span>Bcrypt Encryption</span>
                </div>
                <div class="size-1 bg-slate-300 dark:bg-slate-700 rounded-full"></div>
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">verified_user</span>
                    <span>Secure Storage</span>
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