@props(['admin' => false])
<header class="sticky top-0 z-20 bg-white/80 backdrop-blur border-b border-ink-200">
    <div class="h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <button @click="$store.ui.sidebarOpen = !$store.ui.sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-ink-50">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            @if(!$admin)
            <div class="hidden sm:block" x-data="globalSearch()">
                <div class="relative flex items-center gap-2 bg-ink-50 px-3 py-2 rounded-xl w-72 max-w-full">
                    <i data-lucide="search" class="w-4 h-4 text-ink-500 shrink-0"></i>
                    <input
                        class="bg-transparent text-sm flex-1 outline-none"
                        placeholder="Cari fitur, tanaman, diagnosa..."
                        x-model="query"
                        @input="search()"
                        @keydown.arrow-down.prevent="moveDown()"
                        @keydown.arrow-up.prevent="moveUp()"
                        @keydown.enter.prevent="go()"
                        @focus="if(query.length>0) open=true"
                        @blur="setTimeout(()=>open=false,150)"
                    >
                    <button x-show="query.length>0" @click="query=''; open=false" class="text-ink-400 hover:text-ink-600">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <div x-show="open && results.length > 0" x-cloak
                    class="absolute mt-1 w-80 bg-white border border-ink-200 rounded-xl shadow-lg z-50 overflow-hidden">
                    <template x-for="(item, i) in results" :key="i">
                        <a :href="item.url"
                            :class="i === highlighted ? 'bg-brand-50' : 'hover:bg-ink-50'"
                            class="flex items-center gap-3 px-4 py-3 transition">
                            <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-700 grid place-items-center shrink-0">
                                <i :data-lucide="item.icon" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-ink-800" x-text="item.label"></div>
                                <div class="text-xs text-ink-500" x-text="item.desc"></div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
            @else
            <div class="hidden sm:flex items-center gap-2 px-3 py-2 rounded-xl bg-ink-900/5">
                <i data-lucide="shield" class="w-4 h-4 text-brand-600"></i>
                <span class="text-sm font-semibold text-ink-700">Admin Panel</span>
            </div>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if(!$admin)
            <div class="hidden md:flex items-center gap-2 px-3 py-2 rounded-xl bg-ink-50">
                <i data-lucide="cloud-sun" class="w-4 h-4 text-amber-500"></i>
                <span class="text-xs font-semibold text-ink-800">27°C</span>
                <span class="text-xs text-ink-500">{{ auth()->user()->location ?? 'Indonesia' }}</span>
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
            @endif

            <div class="relative" x-data="{open:false}">
                <button @click="open=!open" class="flex items-center gap-2 p-1 pr-3 rounded-xl hover:bg-ink-50">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" class="w-9 h-9 rounded-xl object-cover">
                    @else
                        <div class="w-9 h-9 rounded-xl {{ $admin ? 'bg-ink-800' : 'bg-brand-gradient' }} grid place-items-center text-white font-bold text-sm">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                    <div class="hidden sm:block text-left">
                        <div class="text-sm font-semibold text-ink-800 leading-none">{{ auth()->user()->name ?? 'Pengguna' }}</div>
                        <div class="text-[11px] text-ink-500 mt-1">{{ $admin ? 'Administrator' : (auth()->user()->farmer_type ?? 'Petani') }}</div>
                    </div>
                </button>
                <div x-show="open" x-cloak @click.outside="open=false" class="absolute right-0 mt-2 w-56 card p-2 z-30">
                    @if($admin)
                        <a href="{{ route('admin.profile') }}" class="sidebar-link"><i data-lucide="user" class="w-4 h-4"></i>Profil Admin</a>
                        <a href="{{ route('admin.settings') }}" class="sidebar-link"><i data-lucide="settings" class="w-4 h-4"></i>Pengaturan</a>
                        <div class="my-1 border-t border-ink-200"></div>
                        <a href="{{ route('dashboard') }}" class="sidebar-link"><i data-lucide="arrow-left" class="w-4 h-4"></i>Kembali ke App</a>
                    @else
                        <a href="{{ route('profile.show') }}" class="sidebar-link"><i data-lucide="user" class="w-4 h-4"></i>Profil</a>
                        <a href="{{ route('profile.settings') }}" class="sidebar-link"><i data-lucide="settings" class="w-4 h-4"></i>Pengaturan</a>
                        @if(auth()->user()->isAdmin())
                            <div class="my-1 border-t border-ink-200"></div>
                            <a href="{{ route('admin.dashboard') }}" class="sidebar-link text-purple-700"><i data-lucide="shield" class="w-4 h-4"></i>Admin Panel</a>
                        @endif
                    @endif
                    <div class="my-1 border-t border-ink-200"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="sidebar-link w-full text-left text-red-600"><i data-lucide="log-out" class="w-4 h-4"></i>Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

@if(!$admin)
<script>
function globalSearch() {
    const items = [
        { label: 'Diagnosa Tanaman', desc: 'Analisa penyakit dari foto', url: '{{ route("diagnosis.index") }}', icon: 'scan-line', tags: ['diagnosa','penyakit','foto','gambar','analisa'] },
        { label: 'Penyuluh AI Chat', desc: 'Tanya seputar pertanian ke AI', url: '{{ route("chat.index") }}', icon: 'message-circle', tags: ['chat','tanya','ai','penyuluh'] },
        { label: 'Cuaca & Peringatan', desc: 'Info cuaca dan peringatan dini', url: '{{ route("weather.index") }}', icon: 'cloud-sun', tags: ['cuaca','hujan','angin','suhu'] },
        { label: 'Harga Pasar', desc: 'Pantau harga komoditas', url: '{{ route("market.index") }}', icon: 'line-chart', tags: ['harga','pasar','komoditas','padi','jagung','cabai'] },
        { label: 'Panduan Budidaya', desc: 'Cara menanam dan merawat tanaman', url: '{{ route("cultivation.index") }}', icon: 'book-open', tags: ['panduan','budidaya','tanam','cara'] },
        { label: 'Komunitas Petani', desc: 'Diskusi dan berbagi pengalaman', url: '{{ route("community.index") }}', icon: 'users', tags: ['komunitas','diskusi','forum','petani'] },
        { label: 'Catatan Lahan', desc: 'Catat dan pantau lahan Anda', url: '{{ route("records.index") }}', icon: 'clipboard-list', tags: ['catatan','lahan','input','catat'] },
        { label: 'Toko & Rekomendasi', desc: 'Produk pertanian pilihan', url: '{{ route("recommendations.index") }}', icon: 'shopping-bag', tags: ['toko','beli','produk','pupuk'] },
        { label: 'Pengaturan', desc: 'Profil dan preferensi akun', url: '{{ route("profile.settings") }}', icon: 'settings', tags: ['pengaturan','profil','akun','password','tema'] },
        { label: 'Beranda', desc: 'Dashboard utama', url: '{{ route("dashboard") }}', icon: 'home', tags: ['beranda','dashboard','utama','home'] },
    ];
    return {
        query: '', results: [], open: false, highlighted: -1,
        search() {
            if (!this.query || this.query.length < 1) { this.open = false; return; }
            const q = this.query.toLowerCase();
            this.results = items.filter(item =>
                item.label.toLowerCase().includes(q) ||
                item.desc.toLowerCase().includes(q) ||
                item.tags.some(t => t.includes(q))
            ).slice(0, 6);
            this.open = this.results.length > 0;
            this.highlighted = -1;
        },
        moveDown() { if (this.highlighted < this.results.length - 1) this.highlighted++; },
        moveUp()   { if (this.highlighted > 0) this.highlighted--; },
        go() { const i = this.highlighted >= 0 ? this.highlighted : 0; if (this.results[i]) window.location = this.results[i].url; },
    };
}
</script>
@endif
