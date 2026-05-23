<aside
    x-data
    :class="$store.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed lg:static z-40 inset-y-0 left-0 w-72 bg-white border-r border-ink-200 transition-transform duration-200 flex flex-col">
    <div class="h-16 px-5 flex items-center gap-3 border-b border-ink-200">
        <div class="w-10 h-10 rounded-xl bg-brand-gradient grid place-items-center text-white shadow-glow">
            <i data-lucide="sprout" class="w-5 h-5"></i>
        </div>
        <div>
            <div class="font-extrabold text-ink-900 leading-none">TaniAI</div>
            <div class="text-[11px] text-ink-500 mt-0.5">AI untuk Petani Indonesia</div>
        </div>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-thin">
        @php
            $links = [
                ['dashboard','Beranda','home'],
                ['diagnosis.index','Diagnosa Tanaman','scan-line'],
                ['chat.index','Penyuluh AI Chat','message-circle'],
                ['weather.index','Cuaca & Peringatan','cloud-sun'],
                ['market.index','Harga Pasar','line-chart'],
                ['cultivation.index','Panduan Budidaya','book-open'],
                ['records.index','Input & Catatan','clipboard-list'],
                ['community.index','Komunitas Petani','users'],
                ['recommendations.index','Toko & Rekomendasi','shopping-bag'],
            ];
        @endphp
        @foreach($links as [$route,$label,$icon])
            <a href="{{ route($route) }}" class="sidebar-link {{ request()->routeIs($route) ? 'active' : '' }}">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>

    <div class="p-3 border-t border-ink-200 space-y-1">
        <a href="{{ route('profile.settings') }}" class="sidebar-link">
            <i data-lucide="settings" class="w-4 h-4"></i><span>Pengaturan</span>
        </a>
        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                    <i data-lucide="shield" class="w-4 h-4"></i><span>Admin Panel</span>
                </a>
            @endif
        @endauth
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="sidebar-link w-full text-left"><i data-lucide="log-out" class="w-4 h-4"></i><span>Keluar</span></button>
        </form>
    </div>
</aside>

<div x-show="$store.ui.sidebarOpen" x-cloak @click="$store.ui.sidebarOpen = false" class="fixed inset-0 bg-ink-900/40 z-30 lg:hidden"></div>
