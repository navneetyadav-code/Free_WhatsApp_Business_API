<?php
$userForLayout = current_user();
$isAuthed = (bool) $userForLayout;
$currentPage = $_GET['page'] ?? 'dashboard';
$seo = seo_meta($currentPage);
$isPublicDocs = !$isAuthed && $currentPage === 'docs';
$initials = 'AD';
if ($userForLayout) {
    $parts = preg_split('/\s+/', trim((string) $userForLayout['name'])) ?: [];
    $initials = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? 'D', 0, 1));
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($seo['title']) ?></title>
    <meta name="description" content="<?= e($seo['description']) ?>">
    
    <!-- Old CSS for backward compatibility of non-dashboard pages -->
    <link rel="stylesheet" href="<?= e(base_url('assets/app.css')) ?>">
    
    <?php if ($isAuthed): ?>
    <!-- New Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', "Liberation Mono", "Courier New", 'monospace'],
                    },
                    colors: {
                        brand: {
                            wa_dark: '#075E54', 
                            wa_med: '#128C7E',  
                            wa_light: '#25D366',
                            accent: '#0F172A'
                        }
                    },
                    boxShadow: {
                        'saas': '0 0 0 1px rgba(0,0,0,.03), 0 1px 2px -1px rgba(0,0,0,.04), 0 2px 4px rgba(0,0,0,.02)',
                        'saas-hover': '0 0 0 1px rgba(0,0,0,.04), 0 4px 6px -1px rgba(0,0,0,.04), 0 2px 4px -2px rgba(0,0,0,.02)',
                        'saas-nav': '0 1px 0 0 rgba(0,0,0,.05)',
                    }
                }
            }
        };
        window.AppConfig = {
            defaultCountryCode: "<?= e(app_config('app.default_country_code')) ?>" || "in"
        };
    </script>
    <style>
        .iti { width: 100%; }
        body {
            background-color: #ffffff;
            color: #0F172A;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .bg-stripes {
            background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(0, 0, 0, 0.01) 10px, rgba(0, 0, 0, 0.01) 20px);
        }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }
        @keyframes growUp { from { height: 0; opacity: 0; } to { opacity: 1; } }
        .animate-grow { animation: growUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
    <?php endif; ?>
</head>
<body class="<?= $isAuthed ? 'flex h-screen overflow-hidden text-[13px]' : ($isPublicDocs ? 'docs-body' : 'auth-body') ?>">

<?php if ($isAuthed): ?>
    <?php require __DIR__ . '/components/sidebar.php'; ?>
    
    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-20 hidden md:hidden transition-opacity"></div>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-screen min-w-0 bg-white relative">
        <?php require __DIR__ . '/components/navbar.php'; ?>
        
        <!-- Scrollable Workspace -->
        <div class="flex-1 overflow-auto custom-scrollbar bg-[#FAFAFA] p-4 md:p-6 lg:p-8">
            <div class="max-w-6xl mx-auto space-y-6">
                <?php foreach (flashes() as $flash): ?>
                    <div class="p-4 mb-4 text-sm rounded-lg <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-800' ?>">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endforeach; ?>
                
                <?= $content ?>
            </div>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const openSidebarBtn = document.getElementById('openSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');

        if (openSidebarBtn && closeSidebarBtn && sidebar && sidebarOverlay) {
            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            }
            openSidebarBtn.addEventListener('click', toggleSidebar);
            closeSidebarBtn.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }
    </script>
<?php elseif ($isPublicDocs): ?>
    <main class="public-doc-shell">
        <div class="public-doc-topbar">
            <a class="auth-brand" href="<?= e(base_url('index.php?page=login')) ?>">
                <span class="auth-logo">WA</span>
                <span>WhatsApp <b>API Hub</b></span>
            </a>
            <a class="ghost small" href="<?= e(base_url('index.php?page=login')) ?>">Log in</a>
        </div>
        <?php foreach (flashes() as $flash): ?>
            <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </main>
<?php endif; ?>
</body>
</html>
