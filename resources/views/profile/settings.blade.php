@extends('layouts.app')
@section('title','Pengaturan')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Pengaturan Akun</h1>

@php $u = auth()->user(); @endphp

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-2 space-y-5">

    {{-- Avatar preview (letter only, no upload) --}}
    <div class="card p-5 flex items-center gap-5">
        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br {{ $u->avatar_gradient }} grid place-items-center text-white text-4xl font-extrabold shadow-lg shrink-0 select-none">
            {{ $u->initial }}
        </div>
        <div>
            <div class="font-bold">Avatar Otomatis</div>
            <p class="text-sm text-ink-500 mt-0.5">Avatar menggunakan inisial nama Anda dengan warna unik.</p>
            <p class="text-xs text-ink-400 mt-1">Ubah nama untuk mendapatkan inisial yang berbeda.</p>
        </div>
    </div>

    {{-- Profile form --}}
    <div class="card p-6">
        <div class="font-bold mb-4 flex items-center gap-2">
            <i data-lucide="user" class="w-4 h-4 text-brand-600"></i> Informasi Profil
        </div>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">@csrf @method('PATCH')
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input name="name" required class="input" value="{{ old('name',$u->name) }}">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required class="input" value="{{ old('email',$u->email) }}">
                    @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Location with autocomplete --}}
            <div x-data="locationAC('{{ old('location',$u->location) }}')" class="relative">
                <label class="label">Lokasi</label>
                @if(!$u->location)
                    <p class="text-xs text-amber-600 mb-1.5 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        Isi lokasi agar data cuaca dan rekomendasi lebih akurat
                    </p>
                @endif
                <input type="text" name="location" class="input"
                    placeholder="Ketik nama kota/kabupaten..."
                    autocomplete="off"
                    x-model="query"
                    @input="search()"
                    @keydown.arrow-down.prevent="down()"
                    @keydown.arrow-up.prevent="up()"
                    @keydown.enter.prevent="pick(hl)"
                    @blur="setTimeout(()=>show=false,200)">
                <ul x-show="show&&results.length" x-cloak
                    class="absolute z-50 left-0 right-0 mt-1 bg-white border border-ink-200 rounded-xl shadow-lg max-h-44 overflow-y-auto">
                    <template x-for="(c,i) in results" :key="i">
                        <li @click="pick(i)" x-text="c"
                            :class="i===hl?'bg-brand-50 text-brand-700':'hover:bg-ink-50'"
                            class="px-3 py-2 text-sm cursor-pointer"></li>
                    </template>
                </ul>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="label">No. Telepon</label>
                    <input name="phone" class="input" placeholder="08xxxxxxxxxx" value="{{ old('phone',$u->phone) }}">
                </div>
                <div>
                    <label class="label">Jenis Petani</label>
                    <select name="farmer_type" class="input">
                        @foreach(['Petani Padi','Petani Sayuran','Petani Buah','Petani Perkebunan','Peternak','Petani Campuran','Lainnya'] as $t)
                            <option {{ old('farmer_type',$u->farmer_type)===$t?'selected':'' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button class="btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan</button>
        </form>
    </div>

    {{-- Password --}}
    <div class="card p-6">
        <div class="font-bold mb-4 flex items-center gap-2">
            <i data-lucide="lock" class="w-4 h-4 text-brand-600"></i> Ubah Password
        </div>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">@csrf @method('PATCH')
            <div>
                <label class="label">Password Lama</label>
                <input type="password" name="current_password" required class="input" autocomplete="current-password">
                @error('current_password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
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
            @error('password')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
            <button class="btn-primary"><i data-lucide="shield-check" class="w-4 h-4"></i> Update Password</button>
        </form>
    </div>
</div>

{{-- Right sidebar --}}
<div class="space-y-5">
    <div class="card p-5">
        <div class="font-bold mb-3 flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 text-brand-600"></i> Info Akun
        </div>
        <div class="divide-y divide-ink-100 text-sm">
            @foreach([
                ['Role',       ucfirst($u->role)],
                ['Bergabung',  $u->created_at->isoFormat('D MMM Y')],
                ['Diagnosa',   \App\Models\Diagnosis::where('user_id',$u->id)->count()],
                ['Postingan',  \App\Models\CommunityPost::where('user_id',$u->id)->count()],
            ] as [$k,$v])
                <div class="flex justify-between py-2.5">
                    <span class="text-ink-500">{{ $k }}</span>
                    <span class="font-semibold">{{ $v }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card p-5">
        <div class="font-bold mb-2 flex items-center gap-2">
            <i data-lucide="bell" class="w-4 h-4 text-brand-600"></i> Notifikasi
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-ink-600">Notifikasi sistem</span>
                <span class="badge badge-green text-xs">Aktif</span>
            </div>
            <p class="text-xs text-ink-400">Notifikasi muncul saat ada aktivitas pada akun Anda.</p>
        </div>
    </div>
</div>
</div>

<script>
function locationAC(initial = '') {
    const cities = [
        'Aceh','Banda Aceh','Langsa','Lhokseumawe','Medan','Binjai','Pematangsiantar','Padang',
        'Bukittinggi','Payakumbuh','Pekanbaru','Dumai','Batam','Jambi','Palembang','Lubuklinggau',
        'Bengkulu','Bandar Lampung','Metro','Pangkalpinang','Jakarta','Jakarta Pusat','Jakarta Utara',
        'Jakarta Barat','Jakarta Selatan','Jakarta Timur','Bogor','Depok','Bekasi','Tangerang',
        'Tangerang Selatan','Bandung','Cimahi','Sukabumi','Cirebon','Tasikmalaya','Banjar',
        'Semarang','Solo','Surakarta','Magelang','Salatiga','Pekalongan','Tegal','Klaten',
        'Purwokerto','Cilacap','Jepara','Kudus','Demak','Yogyakarta','Sleman','Bantul',
        'Gunungkidul','Kulonprogo','Surabaya','Malang','Batu','Blitar','Kediri','Madiun',
        'Mojokerto','Pasuruan','Probolingko','Jember','Banyuwangi','Gresik','Sidoarjo',
        'Denpasar','Badung','Gianyar','Tabanan','Mataram','Bima','Kupang','Pontianak',
        'Singkawang','Palangkaraya','Sampit','Banjarmasin','Banjarbaru','Samarinda',
        'Balikpapan','Bontang','Tarakan','Manado','Bitung','Kotamobagu','Palu','Luwuk',
        'Makassar','Parepare','Palopo','Kendari','Baubau','Gorontalo','Mamuju','Ternate',
        'Ambon','Tual','Manokwari','Sorong','Jayapura','Merauke','Timika',
    ];
    return {
        query: initial,
        results: [],
        show: false,
        hl: -1,
        search() {
            if (this.query.length < 2) { this.show = false; return; }
            const q = this.query.toLowerCase();
            this.results = cities.filter(c => c.toLowerCase().includes(q)).slice(0, 8);
            this.show = this.results.length > 0;
            this.hl = -1;
        },
        pick(i) {
            if (i >= 0 && this.results[i]) { this.query = this.results[i]; this.show = false; }
        },
        down() { if (this.hl < this.results.length-1) this.hl++; },
        up()   { if (this.hl > 0) this.hl--; },
    };
}
</script>
@endsection
