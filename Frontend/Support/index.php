<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Support Center - OrgChart Pro</title>
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
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-[#0e121b] dark:text-white">
    <div class="flex h-screen overflow-hidden">
        <!-- Side Navigation Bar -->
        <aside
            class="w-64 border-r border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex flex-col p-4">
            <div class="flex flex-col gap-8">
                <div class="flex items-center gap-3 px-2">
                    <div class="bg-primary p-2 rounded-lg text-white">
                        <span class="material-symbols-outlined">account_tree</span>
                    </div>
                    <h1 class="text-[#0e121b] dark:text-white text-lg font-bold tracking-tight">OrgChart Pro</h1>
                </div>
                <!-- Mini nav to go back -->
                <nav class="flex flex-col gap-2">
                    <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        href="../dashboard/index.php">
                        <span class="material-symbols-outlined">arrow_back</span>
                        <p class="text-sm">Back to Dashboard</p>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <header
                class="h-16 border-b border-[#e8ebf3] dark:border-gray-800 bg-white dark:bg-background-dark flex items-center px-8">
                <h2 class="text-lg font-bold">Support & Help Center</h2>
            </header>

            <div class="flex-1 overflow-auto bg-white/50 dark:bg-slate-900/50 p-12">
                <div class="max-w-4xl mx-auto space-y-12">
                    <!-- Hero Section -->
                    <div class="text-center">
                        <h3 class="text-3xl font-extrabold tracking-tight">How can we help you today?</h3>
                        <p class="text-slate-500 mt-2">Search our documentation or contact our support team.</p>
                        <div class="mt-6 max-w-xl mx-auto relative group">
                            <span
                                class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                            <input id="supportSearch"
                                class="w-full h-14 pl-12 pr-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-gray-700 rounded-2xl shadow-sm focus:ring-4 focus:ring-primary/10 transition-all"
                                placeholder="Search for articles, guides..." type="text">
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div data-category="getting-started"
                            class="category-card p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all group cursor-pointer">
                            <div
                                class="size-12 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center text-primary mb-4 group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined">rocket_launch</span>
                            </div>
                            <h4 class="font-bold mb-2">Getting Started</h4>
                            <p class="text-sm text-slate-500">Learn the basics of OrgChart Pro and set up your
                                workspace.</p>
                        </div>
                        <div data-category="account"
                            class="category-card p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all group cursor-pointer">
                            <div
                                class="size-12 bg-amber-50 dark:bg-amber-900/20 rounded-xl flex items-center justify-center text-amber-600 mb-4 group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined">security</span>
                            </div>
                            <h4 class="font-bold mb-2">Account & Security</h4>
                            <p class="text-sm text-slate-500">Manage your profile, roles, and platform permissions.</p>
                        </div>
                        <div data-category="billing"
                            class="category-card p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all group cursor-pointer">
                            <div
                                class="size-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center text-emerald-600 mb-4 group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined">payments</span>
                            </div>
                            <h4 class="font-bold mb-2">Billing & Plans</h4>
                            <p class="text-sm text-slate-500">Invoices, payment methods, and corporate subscriptions.
                            </p>
                        </div>
                    </div>

                    <!-- FAQs -->
                    <div id="faqSection" class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xl font-bold">Frequently Asked Questions</h4>
                            <button id="clearFilters"
                                class="hidden text-sm text-primary hover:underline font-medium">Clear filters</button>
                        </div>
                        <div class="faq-list space-y-4">
                            <!-- Getting Started -->
                            <details data-category="getting-started"
                                class="faq-item group bg-white dark:bg-slate-900 border border-slate-200 dark:border-gray-800 rounded-xl p-4 cursor-pointer transition-all">
                                <summary class="flex items-center justify-between font-semibold list-none">
                                    <span>How do I add a new employee?</span>
                                    <span
                                        class="material-symbols-outlined group-open:rotate-180 transition-transform">expand_more</span>
                                </summary>
                                <p class="mt-4 text-sm text-slate-500 leading-relaxed">You can add new employees by
                                    navigating to the Employees page and clicking the "Add New Employee" button in the
                                    top-right corner. You'll need to provide their name, email, department, and role.
                                </p>
                            </details>
                            <details data-category="getting-started"
                                class="faq-item group bg-white dark:bg-slate-900 border border-slate-200 dark:border-gray-800 rounded-xl p-4 cursor-pointer transition-all">
                                <summary class="flex items-center justify-between font-semibold list-none">
                                    <span>What is the Dashboard overview?</span>
                                    <span
                                        class="material-symbols-outlined group-open:rotate-180 transition-transform">expand_more</span>
                                </summary>
                                <p class="mt-4 text-sm text-slate-500 leading-relaxed">The Dashboard provides a
                                    high-level view of your entire organization structure, recent activities, and key
                                    metrics like department size and employee count.
                                </p>
                            </details>

                            <!-- Account & Security -->
                            <details data-category="account"
                                class="faq-item group bg-white dark:bg-slate-900 border border-slate-200 dark:border-gray-800 rounded-xl p-4 cursor-pointer transition-all">
                                <summary class="flex items-center justify-between font-semibold list-none">
                                    <span>Can I change my account role?</span>
                                    <span
                                        class="material-symbols-outlined group-open:rotate-180 transition-transform">expand_more</span>
                                </summary>
                                <p class="mt-4 text-sm text-slate-500 leading-relaxed">Roles are managed by system
                                    administrators. If you need a different level of access, please contact your IT
                                    department or a Manager to update your profile permissions.</p>
                            </details>
                            <details data-category="account"
                                class="faq-item group bg-white dark:bg-slate-900 border border-slate-200 dark:border-gray-800 rounded-xl p-4 cursor-pointer transition-all">
                                <summary class="flex items-center justify-between font-semibold list-none">
                                    <span>How do I enable two-factor authentication?</span>
                                    <span
                                        class="material-symbols-outlined group-open:rotate-180 transition-transform">expand_more</span>
                                </summary>
                                <p class="mt-4 text-sm text-slate-500 leading-relaxed">You can enable 2FA in your
                                    Account Settings. Go to Settings > Security and follow the prompts to link your
                                    authenticator app.</p>
                            </details>

                            <!-- Billing & Plans -->
                            <details data-category="billing"
                                class="faq-item group bg-white dark:bg-slate-900 border border-slate-200 dark:border-gray-800 rounded-xl p-4 cursor-pointer transition-all">
                                <summary class="flex items-center justify-between font-semibold list-none">
                                    <span>How do I download my invoices?</span>
                                    <span
                                        class="material-symbols-outlined group-open:rotate-180 transition-transform">expand_more</span>
                                </summary>
                                <p class="mt-4 text-sm text-slate-500 leading-relaxed">Navigate to Settings > Billing.
                                    Under the "Invoice History" section, you can click the download icon next to any
                                    recent payment to get a PDF version of your invoice.</p>
                            </details>
                            <details data-category="billing"
                                class="faq-item group bg-white dark:bg-slate-900 border border-slate-200 dark:border-gray-800 rounded-xl p-4 cursor-pointer transition-all">
                                <summary class="flex items-center justify-between font-semibold list-none">
                                    <span>What payment methods are supported?</span>
                                    <span
                                        class="material-symbols-outlined group-open:rotate-180 transition-transform">expand_more</span>
                                </summary>
                                <p class="mt-4 text-sm text-slate-500 leading-relaxed">We support all major credit cards
                                    (Visa, Mastercard, American Express), PayPal, and direct wire transfers for
                                    enterprise subscriptions.</p>
                            </details>
                        </div>
                        <div id="noResults" class="hidden py-12 text-center text-slate-500">
                            <span class="material-symbols-outlined text-4xl mb-4">search_off</span>
                            <p>No articles found matching your search.</p>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div
                        class="bg-primary rounded-3xl p-10 flex flex-col md:flex-row items-center justify-between gap-8 text-white">
                        <div>
                            <h4 class="text-2xl font-bold">Still need help?</h4>
                            <p class="text-white/70 mt-1">Our support specialists are online 24/7 to assist you.</p>
                        </div>
                        <a href="mailto:support@orgchartpro.com?subject=Support%20Request"
                            class="bg-white text-primary px-8 py-4 rounded-2xl font-bold hover:bg-slate-100 transition-colors shadow-lg active:scale-95 inline-block">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Script logic for Support Center -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('supportSearch');
            const faqSection = document.getElementById('faqSection');
            const faqItems = document.querySelectorAll('.faq-item');
            const categoryCards = document.querySelectorAll('.category-card');
            const noResults = document.getElementById('noResults');
            const clearFiltersBtn = document.getElementById('clearFilters');

            let activeCategory = null;

            function filterItems() {
                const query = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;

                faqItems.forEach(item => {
                    const text = item.innerText.toLowerCase();
                    const category = item.getAttribute('data-category');

                    const matchesSearch = text.includes(query);
                    const matchesCategory = !activeCategory || category === activeCategory;

                    if (matchesSearch && matchesCategory) {
                        item.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                noResults.classList.toggle('hidden', visibleCount > 0);
                clearFiltersBtn.classList.toggle('hidden', !query && !activeCategory);
            }

            // Search input handler
            searchInput.addEventListener('input', filterItems);

            // Category card click handler
            categoryCards.forEach(card => {
                card.addEventListener('click', () => {
                    const category = card.getAttribute('data-category');

                    if (activeCategory === category) {
                        activeCategory = null;
                        card.classList.remove('ring-4', 'ring-primary/20', 'border-primary');
                    } else {
                        categoryCards.forEach(c => c.classList.remove('ring-4', 'ring-primary/20', 'border-primary'));
                        activeCategory = category;
                        card.classList.add('ring-4', 'ring-ring-primary/20', 'border-primary');
                    }

                    filterItems();
                });
            });

            // Clear filters button
            clearFiltersBtn.addEventListener('click', () => {
                searchInput.value = '';
                activeCategory = null;
                categoryCards.forEach(c => c.classList.remove('ring-4', 'ring-primary/20', 'border-primary'));
                filterItems();
            });
        });
    </script>
</body>


</html>