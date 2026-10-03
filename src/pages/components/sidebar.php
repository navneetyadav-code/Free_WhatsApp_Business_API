<?php
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!-- Sidebar (Collapsible on tablet, Drawer on mobile) -->
<aside id="sidebar" class="bg-[#F8FAFC] border-r border-slate-200 text-slate-600 w-64 flex-shrink-0 flex flex-col transition-transform duration-300 z-30 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0">
    
    <!-- Sidebar Header (Logo) -->
    <div class="h-14 flex items-center px-5 shrink-0">
        <div class="flex items-center gap-2.5 text-slate-900">
            <div class="w-6 h-6 rounded bg-gradient-to-tr from-brand-wa_med to-brand-wa_light flex items-center justify-center text-white shadow-sm shadow-brand-wa_light/20">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
            </div>
            <span class="font-semibold tracking-tight text-[15px]">API Hub</span>
        </div>
        
        <button id="closeSidebar" class="md:hidden ml-auto text-slate-400 hover:text-slate-900 p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Sidebar Navigation -->
    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5 custom-scrollbar font-medium">
        
        <!-- Section: Core -->
        <div class="px-3 mt-2 mb-2 text-[11px] font-semibold text-slate-400 tracking-wider">CORE</div>
        
        <a href="<?= e(base_url('index.php?page=dashboard')) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $currentPage === 'dashboard' ? 'bg-white shadow-saas text-brand-wa_dark mb-1 relative border border-slate-200/50' : 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors mb-1 group' ?>">
            <?php if ($currentPage === 'dashboard'): ?>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-brand-wa_light rounded-r-full"></div>
                <svg class="w-[18px] h-[18px] text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <?php else: ?>
                <svg class="w-[18px] h-[18px] text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <?php endif; ?>
            Overview
        </a>
        
        <a href="<?= e(base_url('index.php?page=message')) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $currentPage === 'message' ? 'bg-white shadow-saas text-brand-wa_dark mb-1 relative border border-slate-200/50' : 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors mb-1 group' ?>">
            <?php if ($currentPage === 'message'): ?>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-brand-wa_light rounded-r-full"></div>
                <svg class="w-[18px] h-[18px] text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            <?php else: ?>
                <svg class="w-[18px] h-[18px] text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            <?php endif; ?>
            Messaging
            <span class="ml-auto bg-slate-100 border border-slate-200 text-slate-600 text-[10px] font-semibold px-1.5 py-0.5 rounded-full shadow-sm">New</span>
        </a>

        <a href="<?= e(base_url('index.php?page=contacts')) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $currentPage === 'contacts' ? 'bg-white shadow-saas text-brand-wa_dark mb-4 relative border border-slate-200/50' : 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors mb-4 group' ?>">
            <?php if ($currentPage === 'contacts'): ?>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-brand-wa_light rounded-r-full"></div>
                <svg class="w-[18px] h-[18px] text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <?php else: ?>
                <svg class="w-[18px] h-[18px] text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <?php endif; ?>
            Contacts
        </a>

        <!-- Section: Platform -->
        <div class="px-3 mt-6 mb-2 text-[11px] font-semibold text-slate-400 tracking-wider">PLATFORM</div>
        
        <a href="<?= e(base_url('index.php?page=device')) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $currentPage === 'device' ? 'bg-white shadow-saas text-brand-wa_dark mb-1 relative border border-slate-200/50' : 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors mb-1 group' ?>">
            <?php if ($currentPage === 'device'): ?>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-brand-wa_light rounded-r-full"></div>
                <svg class="w-[18px] h-[18px] text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <?php else: ?>
                <svg class="w-[18px] h-[18px] text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <?php endif; ?>
            Device
        </a>

        <a href="<?= e(base_url('index.php?page=campaigns')) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $currentPage === 'campaigns' ? 'bg-white shadow-saas text-brand-wa_dark mb-4 relative border border-slate-200/50' : 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors mb-4 group' ?>">
            <?php if ($currentPage === 'campaigns'): ?>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-brand-wa_light rounded-r-full"></div>
                <svg class="w-[18px] h-[18px] text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            <?php else: ?>
                <svg class="w-[18px] h-[18px] text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            <?php endif; ?>
            Campaigns
        </a>

        <a href="<?= e(base_url('index.php?page=chatbot')) ?>" class="flex items-center justify-between px-3 py-2 rounded-lg <?= $currentPage === 'chatbot' ? 'bg-white shadow-saas text-brand-wa_dark mb-4 relative border border-slate-200/50' : 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors mb-4 group' ?>">
            <div class="flex items-center gap-3">
                <?php if ($currentPage === 'chatbot'): ?>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-brand-wa_light rounded-r-full"></div>
                    <svg class="w-[18px] h-[18px] text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                <?php else: ?>
                    <svg class="w-[18px] h-[18px] text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                <?php endif; ?>
                <span>Chatbots</span>
            </div>
            <span class="px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-bold border border-emerald-100/50">Auto</span>
        </a>

        <!-- Section: Developer -->
        <div class="px-3 mt-6 mb-2 text-[11px] font-semibold text-slate-400 tracking-wider">DEVELOPER</div>
        
        <a href="<?= e(base_url('index.php?page=webhooks')) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $currentPage === 'webhooks' ? 'bg-white shadow-saas text-brand-wa_dark mb-1 relative border border-slate-200/50' : 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors mb-1 group' ?>">
            <?php if ($currentPage === 'webhooks'): ?>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-brand-wa_light rounded-r-full"></div>
                <svg class="w-[18px] h-[18px] text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <?php else: ?>
                <svg class="w-[18px] h-[18px] text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <?php endif; ?>
            Webhooks
        </a>
        
        <a href="<?= e(base_url('index.php?page=api-keys')) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $currentPage === 'api-keys' ? 'bg-white shadow-saas text-brand-wa_dark mb-1 relative border border-slate-200/50' : 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors mb-1 group' ?>">
            <?php if ($currentPage === 'api-keys'): ?>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-brand-wa_light rounded-r-full"></div>
                <svg class="w-[18px] h-[18px] text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            <?php else: ?>
                <svg class="w-[18px] h-[18px] text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            <?php endif; ?>
            API Keys
        </a>
        
        <a href="<?= e(base_url('index.php?page=audit')) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $currentPage === 'audit' ? 'bg-white shadow-saas text-brand-wa_dark mb-1 relative border border-slate-200/50' : 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors mb-1 group' ?>">
            <?php if ($currentPage === 'audit'): ?>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-brand-wa_light rounded-r-full"></div>
                <svg class="w-[18px] h-[18px] text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <?php else: ?>
                <svg class="w-[18px] h-[18px] text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <?php endif; ?>
            Audit Logs
        </a>
        
        <a href="<?= e(base_url('index.php?page=queue')) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $currentPage === 'queue' ? 'bg-white shadow-saas text-brand-wa_dark mb-1 relative border border-slate-200/50' : 'hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors mb-1 group' ?>">
            <?php if ($currentPage === 'queue'): ?>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 bg-brand-wa_light rounded-r-full"></div>
                <svg class="w-[18px] h-[18px] text-brand-wa_med" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <?php else: ?>
                <svg class="w-[18px] h-[18px] text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <?php endif; ?>
            Message Queue
        </a>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-3 border-t border-slate-200 bg-[#F8FAFC] shrink-0 space-y-1">
        <a href="<?= e(base_url('index.php?page=docs')) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-600 hover:text-slate-900 transition-colors font-medium">
            <svg class="w-[18px] h-[18px] text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Documentation
        </a>
        <div class="flex items-center gap-3 px-3 py-2 mt-1 rounded-lg hover:bg-slate-100 cursor-pointer transition-colors relative group">
            <div class="w-7 h-7 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold shadow-sm"><?= e($initials ?? 'US') ?></div>
            <div class="flex-1 truncate">
                <p class="text-[13px] font-semibold text-slate-900 truncate"><?= e($userForLayout['name'] ?? 'User') ?></p>
                <p class="text-[11px] text-slate-500 truncate">Administrator</p>
            </div>
            <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
            
            <form method="post" action="<?= e(base_url('index.php?page=logout')) ?>" class="absolute top-0 right-0 h-full w-8 hidden group-hover:flex items-center justify-center bg-slate-100 rounded-lg" title="Logout">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="text-red-500 hover:text-red-700 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg></button>
            </form>
        </div>
    </div>
</aside>

