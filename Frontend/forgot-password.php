<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Forgot Password - OrgChart Pro</title>
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
                    }
                },
            },
        }
    </script>
</head>

<body class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col font-display">
    <header
        class="flex items-center justify-between border-b border-solid border-slate-200 dark:border-slate-800 px-6 py-4 bg-white dark:bg-slate-900">
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
            <h2 class="text-lg font-bold">Corporate Portal</h2>
        </div>
        <a href="login.php" class="text-sm font-bold text-primary hover:underline">Sign In</a>
    </header>

    <main class="flex-1 flex flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-[440px]">
            <div id="formContainer"
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl p-8 md:p-10 transition-all duration-500">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold tracking-tight mb-2">Reset Password</h1>
                    <p class="text-slate-500 text-sm">Enter your corporate email to receive recovery instructions.</p>
                </div>

                <form id="forgotForm" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Corporate Email</label>
                        <input type="email" name="email" required
                            class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 h-12 px-4 text-base focus:ring-primary placeholder:text-slate-400"
                            placeholder="name@company.com" />
                    </div>

                    <button type="submit"
                        class="w-full h-12 bg-primary text-white font-bold rounded-lg hover:bg-opacity-95 transition-all shadow-md active:scale-[0.98] flex items-center justify-center gap-3">
                        <span id="btnLabel">Send Instructions</span>
                        <div id="loader"
                            class="hidden animate-spin size-5 border-2 border-white/20 border-t-white rounded-full">
                        </div>
                    </button>
                </form>

                <div class="mt-8 text-center border-t border-slate-100 dark:border-slate-800 pt-6">
                    <a href="login.php"
                        class="inline-flex items-center gap-2 text-slate-500 hover:text-primary text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Back to Login
                    </a>
                </div>
            </div>

            <!-- Success State (Hidden initially) -->
            <div id="successState"
                class="hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl p-10 text-center scale-95 opacity-0 transition-all duration-500">
                <div
                    class="size-20 bg-emerald-50 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-500 mx-auto mb-6">
                    <span class="material-symbols-outlined text-[40px]">task_alt</span>
                </div>
                <h2 class="text-2xl font-bold mb-2">Check your email</h2>
                <p class="text-slate-500 text-sm mb-8 leading-relaxed">We've sent recovery instructions to your
                    corporate inbox. Please follow the link to reset your account credentials.</p>
                <a href="login.php"
                    class="inline-block w-full py-3 bg-slate-900 dark:bg-slate-800 text-white font-bold rounded-lg hover:bg-opacity-90 transition-all">
                    Return to Sign In
                </a>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('forgotForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const loader = document.getElementById('loader');
            const label = document.getElementById('btnLabel');
            const formContainer = document.getElementById('formContainer');
            const successState = document.getElementById('successState');

            btn.disabled = true;
            loader.classList.remove('hidden');
            label.innerText = 'Transmitting...';

            // Simulate Email Transmission
            setTimeout(() => {
                formContainer.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    formContainer.classList.add('hidden');
                    successState.classList.remove('hidden');
                    setTimeout(() => {
                        successState.classList.remove('scale-95', 'opacity-0');
                    }, 50);
                }, 400);
            }, 1800);
        };
    </script>
</body>

</html>