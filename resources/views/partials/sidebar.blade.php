<aside
    x-data
    :class="$store.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed lg:static z-40 inset-y-0 left-0 w-64 bg-white border-r border-ink-200 transition-transform duration-200 flex flex-col">

    {{-- Logo --}}
    <div class="h-16 px-5 flex items-center gap-3 border-b border-ink-200 shrink-0">
        <div class="w-10 h-10 rounded-xl bg-brand-gradient grid place-items-center text-white shadow-glow shrink-0">
            <i data-lucide="sprout" class="w-5 h-5"></i>
        </div>
        <div>
            <div class="font-extrabold text-ink-900 leading-none">TaniAI</div>
            <div class="text-[11px] text-ink-500 mt-0.5">AI untuk Petani Indonesia</div>
        </div>
        {{-- Close button mobile --}}
        <button class="ml-auto lg:hidden p-1 rounded-lg hover:bg-ink-50" @click="$store.ui.sidebarOpen = false">
            <i data-lucide="x" class="w-4 h-4 text-ink-500"></i>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto scrollbar-thin">
        @php
        $navGroups = [
            'Utama' => [
                ['dashboard',          'Beranda',          'home'],
                ['diagnosis.index',    'Diagnosa Tanaman', 'scan-line'],
                ['chat.index',         'Penyuluh AI Chat', 'message-circle'],
            ],
            'Informasi' => [
                ['weather.index',      'Cuaca & Peringatan','cloud-sun'],
                ['market.index',       'Harga Pasar',       'line-chart'],
                ['cultivation.index',  'Panduan Budidaya',  'book-open'],
            ],
            'Aktivitas' => [
                ['records.index',      'Catatan Lahan',     'clipboard-list'],
                ['community.index',    'Komunitas Petani',  'users'],
                ['recommendations.index','Toko & Rekomendasi','shopping-bag'],
            ],
        ];
        @endphp

        @foreach($navGroups as $groupLabel => $links)
            <div class="pt-2 pb-1">
                <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-ink-400">{{ $groupLabel }}</div>
                @foreach($links as [$route, $label, $icon])
                    @php
                        $active = request()->routeIs($route) || request()->routeIs($route . '.*');
                    @endphp
                    <a href="{{ route($route) }}"
                        @click="$store.ui.sidebarOpen = false"
                        class="sidebar-link {{ $active ? 'active bg-brand-50 text-brand-700' : '' }}">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0"></i>
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    {{-- Bottom actions --}}
    <div class="p-3 border-t border-ink-200 space-y-0.5 shrink-0">
        <a href="{{ route('profile.settings') }}"
            class="sidebar-link {{ request()->routeIs('profile.*') ? 'active bg-brand-50 text-brand-700' : '' }}">
            <i data-lucide="settings" class="w-4 h-4"></i><span>Pengaturan</span>
        </a>
        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link text-purple-700 hover:bg-purple-50 hover:text-purple-800">
                    <i data-lucide="shield" class="w-4 h-4"></i><span>Admin Panel</span>
                </a>
            @endif
        @endauth
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="sidebar-link w-full text-left hover:bg-red-50 hover:text-red-700">
                <i data-lucide="log-out" class="w-4 h-4"></i><span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

{{-- Mobile overlay --}}
<div
    x-show="$store.ui.sidebarOpen"
    x-cloak
    @click="$store.ui.sidebarOpen = false"
    class="fixed inset-0 bg-ink-900/40 z-30 lg:hidden"></div>
