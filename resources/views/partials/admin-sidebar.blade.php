<aside
    x-data
    :class="$store.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed lg:static z-40 inset-y-0 left-0 w-64 bg-slate-900 text-white transition-transform duration-200 flex flex-col h-full">

    <div class="h-16 px-5 flex items-center gap-3 border-b border-white/10 shrink-0">
        <div class="w-10 h-10 rounded-xl bg-brand-gradient grid place-items-center shadow-glow shrink-0">
            <i data-lucide="shield" class="w-5 h-5"></i>
        </div>
        <div>
            <div class="font-extrabold leading-none">TaniAI</div>
            <div class="text-[11px] text-white/50 mt-0.5">Admin Panel</div>
        </div>
        <button class="ml-auto lg:hidden p-1.5 rounded-lg hover:bg-white/10" @click="$store.ui.sidebarOpen=false">
            <i data-lucide="x" class="w-4 h-4 text-white/70"></i>
        </button>
    </div>

    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">
        @php
        $links = [
            ['admin.dashboard',   'Dashboard',          'layout-dashboard'],
            ['admin.users',       'Pengguna',           'users'],
            ['admin.ai',          'AI Analytics',       'brain-circuit'],
            ['admin.logs',        'Diagnosis Logs',     'file-text'],
            ['admin.moderation',  'Moderasi Komunitas', 'flag'],
        ];
        @endphp
        @foreach($links as [$r,$l,$i])
            @php $act = request()->routeIs($r); @endphp
            <a href="{{ route($r) }}" @click="$store.ui.sidebarOpen=false"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                    {{ $act ? 'bg-white/15 text-white' : 'text-white/65 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="{{ $i }}" class="w-4 h-4 shrink-0"></i>
                <span>{{ $l }}</span>
            </a>
        @endforeach

        <div class="pt-4 pb-1">
            <div class="px-3 text-[10px] font-bold uppercase tracking-widest text-white/30">Akun Admin</div>
        </div>
        @foreach([['admin.profile','Profil Saya','user'],['admin.settings','Pengaturan','settings']] as [$r,$l,$i])
            @php $act = request()->routeIs($r); @endphp
            <a href="{{ route($r) }}" @click="$store.ui.sidebarOpen=false"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                    {{ $act ? 'bg-white/15 text-white' : 'text-white/65 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="{{ $i }}" class="w-4 h-4 shrink-0"></i>
                <span>{{ $l }}</span>
            </a>
        @endforeach
    </nav>

    <div class="p-3 border-t border-white/10 space-y-0.5 shrink-0">
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-white/65 hover:bg-white/10 hover:text-white transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i><span>Kembali ke App</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-white/65 hover:bg-white/10 hover:text-white transition">
                <i data-lucide="log-out" class="w-4 h-4"></i><span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

<div x-show="$store.ui.sidebarOpen" x-cloak @click="$store.ui.sidebarOpen=false"
    class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>
