<header class="sticky top-0 z-20 bg-white/80 backdrop-blur border-b border-ink-200">
    <div class="h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <button @click="$store.ui.sidebarOpen = !$store.ui.sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-ink-50">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <div class="hidden sm:flex items-center gap-2 bg-ink-50 px-3 py-2 rounded-xl w-72 max-w-full">
                <i data-lucide="search" class="w-4 h-4 text-ink-500"></i>
                <input class="bg-transparent text-sm flex-1 outline-none" placeholder="Cari sesuatu...">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div class="hidden md:flex items-center gap-2 px-3 py-2 rounded-xl bg-ink-50">
                <i data-lucide="cloud-sun" class="w-4 h-4 text-amber-500"></i>
                <span class="text-xs font-semibold text-ink-800">27°C</span>
                <span class="text-xs text-ink-500">Sleman, DIY</span>
            </div>

            <div class="relative" x-data="{open:false}">
                <button @click="open=!open" class="relative p-2 rounded-lg hover:bg-ink-50">
                    <i data-lucide="bell" class="w-5 h-5 text-ink-600"></i>
                    @if(($unread ?? 0) > 0)
                        <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500"></span>
                    @endif
                </button>
                <div x-show="open" x-cloak @click.outside="open=false" class="absolute right-0 mt-2 w-80 card p-2 z-30">
                    <div class="px-3 py-2 flex items-center justify-between">
                        <div class="font-semibold text-sm">Notifikasi</div>
                        <a href="{{ route('notifications.index') }}" class="text-xs text-brand-600 font-semibold">Lihat semua</a>
                    </div>
                    <div class="max-h-80 overflow-y-auto scrollbar-thin">
                        @forelse(($notifications ?? []) as $n)
                            <div class="px-3 py-2 hover:bg-ink-50 rounded-lg">
                                <div class="text-sm font-semibold text-ink-800">{{ $n->title }}</div>
                                <div class="text-xs text-ink-500 line-clamp-2">{{ $n->message }}</div>
                            </div>
                        @empty
                            <div class="px-3 py-6 text-xs text-ink-500 text-center">Belum ada notifikasi</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="relative" x-data="{open:false}">
                <button @click="open=!open" class="flex items-center gap-2 p-1 pr-3 rounded-xl hover:bg-ink-50">
                    <div class="w-9 h-9 rounded-xl bg-brand-gradient grid place-items-center text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U',0,1)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-sm font-semibold text-ink-800 leading-none">{{ auth()->user()->name ?? 'Pengguna' }}</div>
                        <div class="text-[11px] text-ink-500 mt-1">{{ auth()->user()->farmer_type ?? 'Petani' }}</div>
                    </div>
                </button>
                <div x-show="open" x-cloak @click.outside="open=false" class="absolute right-0 mt-2 w-56 card p-2 z-30">
                    <a href="{{ route('profile.show') }}" class="sidebar-link"><i data-lucide="user" class="w-4 h-4"></i>Profil</a>
                    <a href="{{ route('profile.settings') }}" class="sidebar-link"><i data-lucide="settings" class="w-4 h-4"></i>Pengaturan</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="sidebar-link w-full text-left text-red-600"><i data-lucide="log-out" class="w-4 h-4"></i>Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
