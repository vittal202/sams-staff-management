<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>JWT Login - Corporate Portal</title>
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
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
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
        <a href="Support/index.php"
            class="flex min-w-[84px] cursor-pointer items-center justify-center rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold transition-all hover:bg-primary/90">
            <span>Help</span>
        </a>
    </header>
    <main class="flex-1 flex flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-[440px]">
            <div
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl form-card p-8 md:p-10">
                <div class="mb-8 text-left">
                    <h1 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight mb-2">Welcome back
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-normal">Enter your credentials to
                        access
                        your secure workspace.</p>
                </div>
                
                <!-- Error/Success Messages -->
                <div id="message-container" class="hidden mb-4"></div>

                <form id="loginForm" class="space-y-4">
                    <div class="flex flex-col">
                        <label class="flex flex-col w-full">
                            <p class="text-slate-900 dark:text-slate-200 text-sm font-semibold pb-2">Email Address
                            </p>
                            <input id="emailInput"
                                class="form-input flex w-full rounded-lg text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary h-12 px-4 text-base transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500"
                                placeholder="name@company.com" required="" type="email" name="email" />
                        </label>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex justify-between items-center pb-2">
                            <p class="text-slate-900 dark:text-slate-200 text-sm font-semibold">Password</p>
                            <a class="text-primary text-xs font-semibold hover:underline"
                                href="forgot-password.php">Forgot
                                password?</a>
                        </div>
                        <label class="flex w-full items-stretch relative">
                            <input id="passwordInput"
                                class="form-input flex w-full rounded-lg text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary h-12 px-4 text-base transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500 pr-12"
                                placeholder="Enter your password" required="" type="password" name="password" />
                            <div id="togglePassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer flex items-center">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </div>
                        </label>
                    </div>
                    <div class="pt-4 flex flex-col gap-3">
                        <button id="loginButton"
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
                <div class="text-center mt-8">
                    <p class="text-slate-500 dark:text-slate-400 text-[10px] leading-relaxed">
                        By signing in, you agree to our
                        <a class="text-slate-900 dark:text-slate-200 font-medium hover:underline" href="#">Terms</a>
                        and
                        <a class="text-slate-900 dark:text-slate-200 font-medium hover:underline" href="#">Security
                            Policy</a>.
                    </p>
                </div>
            </div>
            <div
                class="mt-8 flex items-center justify-center gap-4 text-slate-400 dark:text-slate-500 text-xs font-medium">
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">lock</span>
                    <span>256-bit AES Encryption</span>
                </div>
                <div class="size-1 bg-slate-300 dark:bg-slate-700 rounded-full"></div>
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">verified_user</span>
                    <span>GDPR Compliant</span>
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

        // Handle login form submission
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const loginButton = document.getElementById('loginButton');
            const originalButtonText = loginButton.innerHTML;
            
            // Disable button and show loading state
            loginButton.disabled = true;
            loginButton.innerHTML = `
                <div class="size-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                <span class="ml-2">Signing in...</span>
            `;

            const email = document.getElementById('emailInput').value;
            const password = document.getElementById('passwordInput').value;

            try {
                const response = await fetch('../Backend/auth/login_jwt.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    
                    // Store JWT token in localStorage (optional)
                    if (data.token) {
                        localStorage.setItem('jwt_token', data.token);
                    }

                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = data.redirect || 'dashboard/dashboard_simple.php';
                    }, 1000);
                } else {
                    showMessage(data.message || 'Login failed', 'error');
                    loginButton.disabled = false;
                    loginButton.innerHTML = originalButtonText;
                }
            } catch (error) {
                console.error('Login error:', error);
                showMessage('An error occurred during login', 'error');
                loginButton.disabled = false;
                loginButton.innerHTML = originalButtonText;
            }
        });

        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const bgColor = type === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800';
            const textColor = type === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
            const icon = type === 'success' ? 'check_circle' : 'error';

            container.innerHTML = `
                <div class="p-4 rounded-lg ${bgColor} border flex items-start gap-3 animate-pulse">
                    <span class="material-symbols-outlined ${textColor} text-lg mt-0.5">${icon}</span>
                    <p class="${textColor} text-sm font-semibold">${message}</p>
                </div>
            `;
            container.classList.remove('hidden');

            // Auto-hide after 5 seconds
            setTimeout(() => {
                container.classList.add('hidden');
            }, 5000);
        }
    </script>
</body>

</html>
