<?php
$pageLabels = [
    'dashboard' => 'Overview',
    'device' => 'Device Management',
    'api-keys' => 'API Keys',
    'queue' => 'Message Queue',
    'contacts' => 'Contacts',
    'message' => 'New Message',
    'birthday-tasks' => 'Birthday Tasks',
    'webhooks' => 'Webhooks',
    'campaigns' => 'Campaigns',
];
$title = $pageLabels[$_GET['page'] ?? 'dashboard'] ?? 'Overview';
?>
<!-- Top Navbar (Glassmorphic SaaS Style) -->
<header class="h-14 glass-header border-b border-slate-200 flex items-center justify-between px-5 shrink-0 z-10 sticky top-0">
    <div class="flex items-center gap-3">
        <button id="openSidebar" class="md:hidden text-slate-500 hover:text-slate-900 p-1.5 rounded-md hover:bg-slate-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        
        <!-- Breadcrumbs/Context -->
        <div class="hidden sm:flex items-center text-[13px]">
            <span class="text-slate-500 hover:text-slate-900 cursor-pointer font-medium transition-colors">Admin Console</span>
            <span class="text-slate-300 mx-2">/</span>
            <span class="font-semibold text-slate-900"><?= e($title) ?></span>
            
            <!-- Environment Badge -->
            <span class="ml-3 px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200/60 uppercase tracking-wider flex items-center gap-1 shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Operational
            </span>
        </div>
    </div>

    <div class="flex items-center gap-3 sm:gap-4">
        <!-- SaaS Search Box -->
        <div class="hidden md:flex relative group">
            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-slate-400 group-focus-within:text-brand-wa_med transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" class="block w-64 pl-9 pr-12 py-1.5 border border-slate-200 rounded-lg leading-5 bg-slate-50/50 text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-brand-wa_light/20 focus:border-brand-wa_med sm:text-[13px] transition-all shadow-saas inset-shadow" placeholder="Search...">
            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                <kbd class="inline-flex items-center bg-white border border-slate-200 rounded px-1.5 text-[10px] font-sans font-semibold text-slate-400 shadow-sm">⌘K</kbd>
            </div>
        </div>

        <!-- Icons -->
        <div class="h-4 w-px bg-slate-200 hidden sm:block mx-1"></div>
        
        <a href="<?= e(base_url('index.php?page=docs')) ?>" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-md hover:bg-slate-100 hidden sm:block transition-colors" title="Documentation">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </a>
        <button class="text-slate-400 hover:text-slate-700 p-1.5 rounded-md hover:bg-slate-100 relative transition-colors" title="Notifications">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <span class="absolute top-1.5 right-1.5 block h-1.5 w-1.5 rounded-full bg-red-500 ring-2 ring-white"></span>
        </button>
    </div>
</header>
