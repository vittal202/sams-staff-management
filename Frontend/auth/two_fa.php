<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify Your Identity – OrgChart Pro</title>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { primary: '#16439c' },
                    fontFamily: { display: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>
    <style>
        @keyframes shake {
            0%,100%{transform:translateX(0)}
            20%{transform:translateX(-8px)}
            40%{transform:translateX(8px)}
            60%{transform:translateX(-6px)}
            80%{transform:translateX(6px)}
        }
        .shake { animation: shake 0.45s ease; }
    </style>
</head>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// If no pending 2FA, redirect to login
if (!isset($_SESSION['2fa_user_id'])) {
    header("Location: ../../Frontend/login.php");
    exit();
}
$error = isset($_GET['error']) && $_GET['error'] == '1';
?>

<body class="bg-slate-50 dark:bg-slate-950 font-display min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">

        <!-- Logo -->
        <div class="flex flex-col items-center mb-10">
            <div class="bg-primary p-3 rounded-2xl text-white mb-4 shadow-lg shadow-primary/30">
                <span class="material-symbols-outlined text-3xl">lock</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Verify Your Identity</h1>
            <p class="text-sm text-slate-500 mt-1 text-center">Enter your 6-digit security PIN to continue.</p>
        </div>

        <!-- Error Banner -->
        <?php if ($error): ?>
        <div id="errorBanner" class="mb-6 flex items-center gap-3 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400 rounded-xl px-4 py-3 text-sm font-semibold">
            <span class="material-symbols-outlined text-base">error</span>
            Incorrect PIN. Please try again.
        </div>
        <?php endif; ?>

        <!-- PIN Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl p-8">

            <form method="POST" action="../../Backend/api/verify_2fa_pin.php" id="pinForm">
                <!-- 6 digit boxes -->
                <div class="flex gap-3 justify-center mb-8" id="pinBoxes">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <input
                        type="password"
                        maxlength="1"
                        inputmode="numeric"
                        pattern="[0-9]"
                        class="pin-digit w-12 h-14 text-center text-xl font-bold bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:border-primary dark:focus:border-primary transition-all"
                        autocomplete="off"
                    />
                    <?php endfor; ?>
                </div>

                <!-- Hidden full PIN input -->
                <input type="hidden" name="pin" id="pinHidden" />

                <button type="submit" id="submitBtn"
                    class="w-full py-3 bg-primary text-white font-bold rounded-xl hover:bg-blue-800 transition-all shadow-lg shadow-primary/25 flex items-center justify-center gap-2 disabled:opacity-50">
                    <span class="material-symbols-outlined text-base">verified_user</span>
                    Verify PIN
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="../../Backend/auth/logout.php"
                    class="text-xs text-slate-400 hover:text-rose-500 transition-colors font-medium">
                    ← Back to Login
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">OrgChart Pro · Secured Session</p>
    </div>

    <script>
        const digits  = document.querySelectorAll('.pin-digit');
        const hidden  = document.getElementById('pinHidden');
        const form    = document.getElementById('pinForm');
        const boxes   = document.getElementById('pinBoxes');

        digits.forEach((input, idx) => {
            input.addEventListener('input', () => {
                // Allow only digits
                input.value = input.value.replace(/\D/g, '').slice(-1);
                if (input.value && idx < digits.length - 1) {
                    digits[idx + 1].focus();
                }
                updateHidden();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && idx > 0) {
                    digits[idx - 1].focus();
                    digits[idx - 1].value = '';
                    updateHidden();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
                paste.split('').forEach((ch, i) => {
                    if (digits[i]) digits[i].value = ch;
                });
                if (digits[paste.length - 1]) digits[paste.length - 1].focus();
                updateHidden();
            });
        });

        function updateHidden() {
            hidden.value = [...digits].map(d => d.value).join('');
        }

        form.addEventListener('submit', (e) => {
            if (hidden.value.length !== 6 || !/^\d{6}$/.test(hidden.value)) {
                e.preventDefault();
                boxes.classList.add('shake');
                boxes.addEventListener('animationend', () => boxes.classList.remove('shake'), { once: true });
            }
        });

        // Auto-focus first box
        digits[0].focus();

        <?php if ($error): ?>
        // Shake on page load if error
        boxes.classList.add('shake');
        boxes.addEventListener('animationend', () => boxes.classList.remove('shake'), { once: true });
        <?php endif; ?>
    </script>
</body>
</html>
