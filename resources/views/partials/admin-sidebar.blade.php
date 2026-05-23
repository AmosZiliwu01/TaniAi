<aside
    x-data
    :class="$store.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed lg:static z-40 inset-y-0 left-0 w-72 bg-ink-900 text-white transition-transform duration-200 flex flex-col">
    <div class="h-16 px-5 flex items-center gap-3 border-b border-white/10">
        <div class="w-10 h-10 rounded-xl bg-brand-gradient grid place-items-center shadow-glow">
            <i data-lucide="shield" class="w-5 h-5"></i>
        </div>
        <div>
            <div class="font-extrabold leading-none">TaniAI Admin</div>
            <div class="text-[11px] text-white/60 mt-0.5">Control Panel</div>
        </div>
    </div>
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-thin">
        @php
            $alinks = [
                ['admin.dashboard','Dashboard','layout-dashboard'],
                ['admin.users','Pengguna','users'],
                ['admin.ai','AI Analytics','brain-circuit'],
                ['admin.logs','Diagnosis Logs','file-text'],
                ['admin.moderation','Moderasi Komunitas','message-square-warning'],
            ];
        @endphp
        @foreach($alinks as [$route,$label,$icon])
            <a href="{{ route($route) }}" class="sidebar-link text-white/70 hover:bg-white/10 hover:text-white {{ request()->routeIs($route) ? 'bg-white/10 text-white' : '' }}">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i><span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>
    <div class="p-3 border-t border-white/10 space-y-1">
        <a href="{{ route('dashboard') }}" class="sidebar-link text-white/70 hover:bg-white/10 hover:text-white"><i data-lucide="arrow-left" class="w-4 h-4"></i><span>Kembali ke App</span></a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="sidebar-link w-full text-left text-white/70 hover:bg-white/10 hover:text-white"><i data-lucide="log-out" class="w-4 h-4"></i><span>Keluar</span></button>
        </form>
    </div>
</aside>
<div x-show="$store.ui.sidebarOpen" x-cloak @click="$store.ui.sidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>
