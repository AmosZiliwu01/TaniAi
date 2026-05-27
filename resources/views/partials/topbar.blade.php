@props(['admin' => false])
<header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-ink-200 shrink-0">
    <div class="h-16 px-4 sm:px-6 flex items-center justify-between gap-4">

        <div class="flex items-center gap-3 min-w-0">
            <button @click="$store.ui.sidebarOpen=!$store.ui.sidebarOpen"
                class="lg:hidden p-2 rounded-lg hover:bg-ink-50">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            @if(!$admin)
            <div class="hidden sm:block" x-data="globalSearch()">
                <div class="relative flex items-center gap-2 bg-ink-50 border border-ink-200 px-3 py-2 rounded-xl w-64">
                    <i data-lucide="search" class="w-4 h-4 text-ink-400 shrink-0"></i>
                    <input class="bg-transparent text-sm flex-1 outline-none"
                        placeholder="Cari fitur, tanaman..."
                        x-model="query"
                        @input="search()"
                        @keydown.arrow-down.prevent="down()"
                        @keydown.arrow-up.prevent="up()"
                        @keydown.enter.prevent="go()"
                        @focus="if(query.length>0)open=true"
                        @blur="setTimeout(()=>open=false,200)">
                    <button x-show="query.length>0" @click="query='';open=false" class="text-ink-400 hover:text-ink-600">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <div x-show="open&&results.length" x-cloak
                    class="absolute top-14 mt-0.5 w-80 bg-white border border-ink-200 rounded-2xl shadow-xl z-50 py-1 overflow-hidden">
                    <template x-for="(item,i) in results" :key="i">
                        <a :href="item.url"
                            :class="i===hl?'bg-brand-50':'hover:bg-ink-50'"
                            class="flex items-center gap-3 px-4 py-2.5 transition">
                            <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-700 grid place-items-center shrink-0">
                                <i :data-lucide="item.icon" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-ink-800" x-text="item.label"></div>
                                <div class="text-xs text-ink-400" x-text="item.desc"></div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
            @else
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100">
                <i data-lucide="shield" class="w-4 h-4 text-brand-600"></i>
                <span class="text-sm font-semibold text-slate-700">Admin Panel</span>
            </div>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if(!$admin)
            {{-- Location pill → weather --}}
            <a href="{{ route('weather.index') }}"
                class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-ink-50 hover:bg-ink-100 transition text-xs font-semibold text-ink-700">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand-600"></i>
                {{ auth()->user()->location ?? 'Atur lokasi' }}
            </a>

            {{-- Notifications --}}
            <div class="relative" x-data="{open:false}">
                @php
                    try { $unread = \App\Models\NotificationLog::where('user_id',auth()->id())->whereNull('read_at')->count(); }
                    catch (\Throwable $e) { $unread = 0; }
                @endphp
                <button @click="open=!open" class="relative p-2 rounded-lg hover:bg-ink-50">
                    <i data-lucide="bell" class="w-5 h-5 text-ink-600"></i>
                    @if($unread > 0)
                        <span class="absolute top-0.5 right-0.5 w-4 h-4 rounded-full bg-red-500 text-white text-[9px] font-bold grid place-items-center">
                            {{ min($unread,9) }}
                        </span>
                    @endif
                </button>
                <div x-show="open" x-cloak @click.outside="open=false"
                    class="absolute right-0 mt-2 w-80 card p-2 z-30 shadow-xl">
                    <div class="px-3 py-2 flex items-center justify-between border-b border-ink-100 mb-1">
                        <div class="font-semibold text-sm">Notifikasi</div>
                        <form method="POST" action="{{ route('notifications.read') }}">@csrf
                            <button class="text-xs text-brand-600 font-semibold hover:underline">Tandai dibaca</button>
                        </form>
                    </div>
                    <div class="max-h-72 overflow-y-auto">
                        @php
                            try { $notifItems = \App\Models\NotificationLog::where('user_id',auth()->id())->latest()->limit(8)->get(); }
                            catch (\Throwable $e) { $notifItems = collect(); }
                        @endphp
                        @forelse($notifItems as $n)
                            <div class="px-3 py-2.5 hover:bg-ink-50 rounded-xl {{ $n->read_at ? 'opacity-60' : '' }}">
                                <div class="text-sm font-semibold flex items-center gap-1.5">
                                    @if(!$n->read_at)<span class="w-1.5 h-1.5 rounded-full bg-brand-500 shrink-0"></span>@endif
                                    {{ $n->title }}
                                </div>
                                <p class="text-xs text-ink-500 mt-0.5 line-clamp-2">{{ $n->message }}</p>
                                <div class="text-[10px] text-ink-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                            </div>
                        @empty
                            <div class="py-8 text-xs text-ink-400 text-center">Belum ada notifikasi</div>
                        @endforelse
                    </div>
                    <a href="{{ route('notifications.index') }}"
                        class="block text-center text-xs text-brand-600 font-semibold py-2 border-t border-ink-100 mt-1 hover:underline">
                        Lihat semua
                    </a>
                </div>
            </div>
            @endif

            {{-- User menu --}}
            <div class="relative" x-data="{open:false}">
                @php $av = auth()->user(); @endphp
                <button @click="open=!open" class="flex items-center gap-2 p-1 pr-3 rounded-xl hover:bg-ink-50 transition">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $av->avatar_gradient ?? 'from-brand-500 to-brand-700' }} grid place-items-center text-white font-bold text-sm shrink-0 select-none">
                        {{ $av->initial ?? 'U' }}
                    </div>
                    <div class="hidden sm:block text-left min-w-0">
                        <div class="text-sm font-semibold text-ink-800 leading-none truncate max-w-[110px]">{{ $av->name }}</div>
                        <div class="text-[11px] text-ink-400 mt-0.5">{{ $admin ? 'Administrator' : ($av->farmer_type ?? 'Petani') }}</div>
                    </div>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-ink-400 shrink-0"></i>
                </button>

                <div x-show="open" x-cloak @click.outside="open=false"
                    class="absolute right-0 mt-2 w-52 card p-2 z-30 shadow-xl">
                    @if($admin)
                        <a href="{{ route('admin.profile') }}" class="sidebar-link"><i data-lucide="user" class="w-4 h-4"></i>Profil Admin</a>
                        <a href="{{ route('admin.settings') }}" class="sidebar-link"><i data-lucide="settings" class="w-4 h-4"></i>Pengaturan</a>
                        <div class="my-1 border-t border-ink-100"></div>
                        <a href="{{ route('dashboard') }}" class="sidebar-link"><i data-lucide="arrow-left" class="w-4 h-4"></i>Kembali ke App</a>
                    @else
                        <a href="{{ route('profile.show') }}" class="sidebar-link"><i data-lucide="user" class="w-4 h-4"></i>Profil Saya</a>
                        <a href="{{ route('profile.settings') }}" class="sidebar-link"><i data-lucide="settings" class="w-4 h-4"></i>Pengaturan</a>
                        @if(auth()->user()->isAdmin())
                            <div class="my-1 border-t border-ink-100"></div>
                            <a href="{{ route('admin.dashboard') }}" class="sidebar-link text-purple-700 hover:bg-purple-50">
                                <i data-lucide="shield" class="w-4 h-4"></i>Admin Panel
                            </a>
                        @endif
                    @endif
                    <div class="my-1 border-t border-ink-100"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="sidebar-link w-full text-left text-red-600 hover:bg-red-50">
                            <i data-lucide="log-out" class="w-4 h-4"></i>Keluar
                        </button>
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
        {label:'Diagnosa Tanaman', desc:'Foto & analisa penyakit', url:'{{ route("diagnosis.index") }}', icon:'scan-line', tags:['diagnosa','penyakit','foto']},
        {label:'Asisten Tani AI', desc:'Tanya seputar pertanian', url:'{{ route("chat.index") }}', icon:'message-circle', tags:['chat','ai','tanya','asisten']},
        {label:'Cuaca & Peringatan', desc:'Info cuaca real-time', url:'{{ route("weather.index") }}', icon:'cloud-sun', tags:['cuaca','hujan','suhu','peringatan']},
        {label:'Harga Pasar', desc:'Pantau harga hasil tani', url:'{{ route("market.index") }}', icon:'line-chart', tags:['harga','pasar','jual','padi','cabai']},
        {label:'Panduan Budidaya', desc:'Cara menanam & merawat', url:'{{ route("cultivation.index") }}', icon:'book-open', tags:['panduan','budidaya','tanam']},
        {label:'Komunitas Petani', desc:'Diskusi dengan petani lain', url:'{{ route("community.index") }}', icon:'users', tags:['komunitas','diskusi','forum']},
        {label:'Catatan Lahan', desc:'Pantau status lahan', url:'{{ route("records.index") }}', icon:'clipboard-list', tags:['catatan','lahan','record']},
        {label:'Pengaturan', desc:'Profil dan lokasi', url:'{{ route("profile.settings") }}', icon:'settings', tags:['pengaturan','profil','lokasi']},
        {label:'Beranda', desc:'Dashboard utama', url:'{{ route("dashboard") }}', icon:'home', tags:['beranda','dashboard','home']},
    ];
    return {
        query:'', results:[], open:false, hl:-1,
        search() {
            if (!this.query||this.query.length<1){this.open=false;return;}
            const q=this.query.toLowerCase();
            this.results=items.filter(i=>i.label.toLowerCase().includes(q)||i.desc.toLowerCase().includes(q)||i.tags.some(t=>t.includes(q))).slice(0,6);
            this.open=this.results.length>0; this.hl=-1;
        },
        down(){if(this.hl<this.results.length-1)this.hl++;},
        up(){if(this.hl>0)this.hl--;},
        go(){const i=this.hl>=0?this.hl:0;if(this.results[i])window.location=this.results[i].url;},
    };
}
</script>
@endif
