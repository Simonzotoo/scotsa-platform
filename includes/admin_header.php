<?php
require_once __DIR__ . '/auth.php';
require_admin();
$admin      = current_admin();
$pageTitle ??= 'Admin Dashboard';
$adminPage  = basename($_SERVER['PHP_SELF']);
$__flash    = consume_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> — SCOTSA Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Apply saved theme before paint — prevents flash on admin pages -->
    <script>
        (function() {
            var t = localStorage.getItem('scotsa-theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', t);
            if (t === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        scotsaBlue:  '#0A1F44',
                        scotsaLight: '#0d2a5c',
                        scotsaGold:  '#D4AF37',
                        ink:         '#0F172A',
                    },
                    fontFamily: {
                        heading: ['Sora', 'sans-serif'],
                        body:    ['Plus Jakarta Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
</head>
<body class="bg-slate-100 text-ink antialiased" style="font-family:'Plus Jakarta Sans',sans-serif;">

<?php if ($__flash): ?>
<script id="scotsa-flash" type="application/json"><?= json_encode($__flash, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php endif; ?>

<div class="min-h-screen lg:flex">

    <!-- ── Sidebar ─────────────────────────────────────── -->
    <aside class="lg:fixed lg:inset-y-0 lg:w-64 xl:w-72 flex flex-col" style="background:#0A1F44;">

        <!-- Brand -->
        <div class="flex items-center justify-between px-5 py-5 border-b border-white/10">
            <a href="dashboard.php" class="group flex items-center gap-3 min-w-0
                                           focus-visible:outline-none focus-visible:ring-2
                                           focus-visible:ring-scotsaGold focus-visible:ring-offset-1
                                           focus-visible:ring-offset-scotsaBlue rounded-lg">
                <!-- Logo mark for dark sidebar — resolved server-side, no 404 flash -->
                <?php
                $adminLogoSrc = is_file(IMAGES_ROOT . '/logo/logo-light.png')
                    ? IMAGES_URL . '/logo/logo-light.png'
                    : IMAGES_URL . '/logo/logo-dark.png';
                ?>
                <img src="<?= $adminLogoSrc ?>"
                     alt="" aria-hidden="true"
                     class="h-10 w-10 flex-shrink-0 object-contain
                            transition-transform duration-300 ease-out will-change-transform
                            group-hover:scale-[1.07]"
                     style="filter:drop-shadow(0 1px 5px rgba(212,175,55,.14));"
                     onerror="this.src='<?= IMAGES_URL ?>/placeholders/logo-mark-light.svg'; this.onerror=null;"
                     loading="eager">
                <!-- Vertical separator -->
                <div class="w-px h-7 flex-shrink-0 rounded-full" style="background:rgba(255,255,255,0.14);" aria-hidden="true"></div>
                <!-- Text -->
                <div class="leading-none min-w-0">
                    <span class="block font-heading font-black text-white leading-none
                                 tracking-[-0.01em] transition-colors duration-200
                                 group-hover:text-scotsaGold"
                          style="font-size:1.02rem;">SCOTSA</span>
                    <span class="block font-semibold uppercase truncate transition-colors duration-200 mt-[4px]"
                          style="font-size:.58rem; letter-spacing:.08em; color:rgba(147,197,253,.65);">Admin Panel</span>
                </div>
            </a>
            <button class="rounded-lg border border-white/20 px-2.5 py-1.5 text-xs text-white/70 lg:hidden hover:bg-white/10 transition" data-admin-menu>Menu</button>
        </div>

        <!-- Nav links -->
        <?php
        $adminLinks = [
            ['Dashboard',     'dashboard.php',     'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['Programs',      'programs.php',      'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['Courses',       'courses.php',       'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['Uploads',       'uploads.php',       'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
            ['Announcements', 'announcements.php', 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
            ['Users',         'users.php',         'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ];
        ?>
        <nav class="hidden gap-1 px-3 py-4 flex-1 lg:grid" data-admin-nav>
            <?php foreach ($adminLinks as [$label, $file, $icon]): ?>
                <a class="admin-link <?= $adminPage === $file ? 'active-admin' : '' ?>" href="<?= $file ?>">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/>
                    </svg>
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Bottom -->
        <div class="border-t border-white/10 px-3 py-3 hidden lg:block">
            <a class="admin-link" href="<?= BASE_URL ?>/index.php">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                View Site
            </a>
            <a class="admin-link text-red-300 hover:text-red-200" href="logout.php" data-logout>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- ── Main ──────────────────────────────────────────── -->
    <div class="flex-1 lg:ml-64 xl:ml-72">

        <!-- Top bar -->
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white px-4 py-3.5 sm:px-6 lg:px-8 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="font-heading font-black text-xl text-ink"><?= e($pageTitle) ?></h1>
                    <p class="text-xs text-slate-400 mt-0.5">Signed in as <strong class="text-slate-600"><?= e($admin['name']) ?></strong></p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= BASE_URL ?>/index.php"
                       class="rounded-lg border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                        ← View Site
                    </a>
                    <a href="logout.php" data-logout
                       class="rounded-lg border border-red-200 bg-red-50 px-3.5 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                        Logout
                    </a>
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6 lg:p-8">
