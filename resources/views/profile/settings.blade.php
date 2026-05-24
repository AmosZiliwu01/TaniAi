@extends('layouts.app')
@section('title','Pengaturan')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Pengaturan Akun</h1>

@if(session('status'))
    <div class="mb-5 p-3 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 text-sm flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('status') }}
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Left: Profile Info --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Avatar Upload --}}
        <div class="card p-6" x-data="{ preview: null }">
            <div class="font-bold mb-4 flex items-center gap-2">
                <i data-lucide="user-circle" class="w-4 h-4 text-brand-600"></i> Foto Profil
            </div>
            <div class="flex items-center gap-5">
                <div class="relative">
                    @if(auth()->user()->avatar)
                        <img id="avatarPreview" src="{{ Storage::url(auth()->user()->avatar) }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-brand-200">
                    @else
                        <div id="avatarInitial" class="w-20 h-20 rounded-2xl bg-brand-gradient grid place-items-center text-white text-3xl font-extrabold shadow-glow">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" id="avatarForm">@csrf
                    <div class="space-y-2">
                        <label class="btn-outline text-sm cursor-pointer">
                            <i data-lucide="upload" class="w-4 h-4"></i> Ganti Foto
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden"
                                @change="
                                    const f = $event.target.files[0];
                                    if(f) {
                                        const r = new FileReader();
                                        r.onload = e => {
                                            const prev = document.getElementById('avatarPreview') || document.getElementById('avatarInitial');
                                            if(prev) { prev.outerHTML = '<img id=\'avatarPreview\' src=\'' + e.target.result + '\' class=\'w-20 h-20 rounded-2xl object-cover border-2 border-brand-200\'>'; }
                                        };
                                        r.readAsDataURL(f);
                                        $nextTick(() => document.getElementById('avatarForm').submit());
                                    }
                                ">
                        </label>
                        <div class="text-xs text-ink-500">JPG, PNG, atau WebP · maks 2MB</div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Informasi Profil --}}
        <div class="card p-6">
            <div class="font-bold mb-4 flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-brand-600"></i> Informasi Profil
            </div>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">@csrf @method('PATCH')
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Nama Lengkap</label>
                        <input name="name" required class="input" value="{{ old('name', auth()->user()->name) }}">
                        @error('name')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" name="email" required class="input" value="{{ old('email', auth()->user()->email) }}">
                        @error('email')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div x-data="locationAutocomplete()" class="relative">
                    <label class="label">Lokasi</label>
                    <input
                        type="text" name="location" class="input"
                        placeholder="Ketik nama kota/kabupaten..."
                        autocomplete="off"
                        x-model="query"
                        @input="search()"
                        @keydown.arrow-down.prevent="moveDown()"
                        @keydown.arrow-up.prevent="moveUp()"
                        @keydown.enter.prevent="select(highlighted)"
                        @blur="setTimeout(()=>open=false,200)"
                        value="{{ old('location', auth()->user()->location) }}"
                    >
                    <ul x-show="open && results.length > 0" x-cloak
                        class="absolute z-50 left-0 right-0 bg-white border border-ink-200 rounded-xl shadow-lg mt-1 max-h-48 overflow-y-auto">
                        <template x-for="(item, i) in results" :key="i">
                            <li @click="select(i)"
                                :class="i===highlighted?'bg-brand-50 text-brand-700':'hover:bg-ink-50'"
                                class="px-3 py-2 text-sm cursor-pointer" x-text="item"></li>
                        </template>
                    </ul>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">No. Telepon</label>
                        <input name="phone" class="input" placeholder="08xxxxxxxxxx" value="{{ old('phone', auth()->user()->phone) }}">
                    </div>
                    <div>
                        <label class="label">Jenis Petani</label>
                        <select name="farmer_type" class="input">
                            @foreach(['Petani Padi','Petani Sayuran','Petani Buah','Petani Perkebunan','Peternak','Petani Campuran','Lainnya'] as $t)
                                <option {{ (old('farmer_type', auth()->user()->farmer_type) === $t) ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button class="btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
            </form>
        </div>

        {{-- Ubah Password --}}
        <div class="card p-6">
            <div class="font-bold mb-4 flex items-center gap-2">
                <i data-lucide="lock" class="w-4 h-4 text-brand-600"></i> Ubah Password
            </div>
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">@csrf @method('PATCH')
                <div>
                    <label class="label">Password Lama</label>
                    <input type="password" name="current_password" required class="input" autocomplete="current-password">
                    @error('current_password')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Password Baru</label>
                        <input type="password" name="password" required minlength="6" class="input" autocomplete="new-password">
                    </div>
                    <div>
                        <label class="label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required class="input" autocomplete="new-password">
                    </div>
                </div>
                @error('password')<div class="text-xs text-red-500">{{ $message }}</div>@enderror
                <button class="btn-primary">
                    <i data-lucide="shield-check" class="w-4 h-4"></i> Update Password
                </button>
            </form>
        </div>
    </div>

    {{-- Right: Preferences --}}
    <div class="space-y-5">

        {{-- Preferensi Tampilan --}}
        <div class="card p-5" x-data>
            <div class="font-bold mb-4 flex items-center gap-2">
                <i data-lucide="sliders" class="w-4 h-4 text-brand-600"></i> Preferensi
            </div>
            <div class="space-y-4">
                {{-- Dark Mode --}}
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-sm">Mode Gelap</div>
                        <div class="text-xs text-ink-500 mt-0.5">Tampilan warna gelap</div>
                    </div>
                    <button
                        @click="$store.ui.toggleDark()"
                        :class="$store.ui.dark ? 'bg-brand-600' : 'bg-slate-200'"
                        class="w-12 h-6 rounded-full relative transition-colors duration-200 focus:outline-none">
                        <span
                            :class="$store.ui.dark ? 'translate-x-6' : 'translate-x-0.5'"
                            class="absolute top-0.5 left-0 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"></span>
                    </button>
                </div>

                <div class="border-t border-ink-200 pt-4">
                    <div class="font-semibold text-sm mb-2">Bahasa</div>
                    <select class="input text-sm" onchange="alert('Fitur multi-bahasa akan segera hadir.')">
                        <option selected>Bahasa Indonesia</option>
                        <option>English (Coming Soon)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Notifikasi --}}
        <div class="card p-5">
            <div class="font-bold mb-4 flex items-center gap-2">
                <i data-lucide="bell" class="w-4 h-4 text-brand-600"></i> Notifikasi
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span>Notifikasi browser</span>
                    <span class="badge badge-green text-xs">Aktif</span>
                </div>
                <div class="flex items-center justify-between text-ink-500">
                    <span>Notifikasi email</span>
                    <span class="text-xs text-ink-400">Coming soon</span>
                </div>
                <p class="text-xs text-ink-400">Notifikasi dikirim melalui browser saat ada aktivitas penting.</p>
            </div>
        </div>

        {{-- Akun info --}}
        <div class="card p-5">
            <div class="font-bold mb-3 flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-brand-600"></i> Info Akun
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-ink-500">Role</span>
                    <span class="font-semibold">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-500">Bergabung</span>
                    <span class="font-semibold">{{ auth()->user()->created_at->isoFormat('D MMM Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-500">Total Diagnosa</span>
                    <span class="font-semibold">{{ auth()->user()->diagnoses()->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function locationAutocomplete() {
    const cities = [
        'Aceh','Banda Aceh','Langsa','Lhokseumawe','Sabang','Medan','Binjai',
        'Pematangsiantar','Tanjungbalai','Tebing Tinggi','Padang','Bukittinggi',
        'Payakumbuh','Solok','Pekanbaru','Dumai','Batam','Tanjungpinang','Jambi',
        'Palembang','Lubuklinggau','Bengkulu','Bandar Lampung','Metro','Pangkalpinang',
        'Jakarta','Jakarta Pusat','Jakarta Utara','Jakarta Barat','Jakarta Selatan','Jakarta Timur',
        'Bogor','Depok','Bekasi','Tangerang','Tangerang Selatan','Bandung','Cimahi',
        'Sukabumi','Cirebon','Tasikmalaya','Banjar','Semarang','Solo','Surakarta',
        'Magelang','Salatiga','Pekalongan','Tegal','Yogyakarta','Sleman','Bantul',
        'Gunungkidul','Kulonprogo','Surabaya','Malang','Batu','Blitar','Kediri',
        'Madiun','Mojokerto','Pasuruan','Probolinggo','Denpasar','Badung','Gianyar',
        'Tabanan','Mataram','Bima','Kupang','Pontianak','Singkawang','Palangkaraya',
        'Banjarmasin','Banjarbaru','Samarinda','Balikpapan','Bontang','Tarakan',
        'Manado','Bitung','Kotamobagu','Palu','Makassar','Parepare','Palopo',
        'Kendari','Baubau','Gorontalo','Mamuju','Ternate','Ambon','Jayapura','Merauke',
        'Klaten','Purwokerto','Cilacap','Kebumen','Wonosobo','Jepara','Kudus','Demak',
    ];
    return {
        query: '',
        results: [],
        open: false,
        highlighted: -1,
        search() {
            if (this.query.length < 2) { this.open = false; return; }
            const q = this.query.toLowerCase();
            this.results = cities.filter(c => c.toLowerCase().includes(q)).slice(0, 8);
            this.open = this.results.length > 0;
            this.highlighted = -1;
        },
        select(i) {
            if (i >= 0 && this.results[i]) {
                this.query = this.results[i];
                this.open = false;
            }
        },
        moveDown() { if (this.highlighted < this.results.length - 1) this.highlighted++; },
        moveUp()   { if (this.highlighted > 0) this.highlighted--; },
    };
}
</script>
@endsection
