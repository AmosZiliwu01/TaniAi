@extends('layouts.app')
@section('title','Diagnosa Tanaman')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Diagnosa Tanaman</h1>
    <p class="text-ink-500 mt-1">Upload foto tanaman untuk mendapatkan analisa AI yang akurat.</p>
</div>

@if($errors->has('image'))
    <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 flex items-start gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 mt-0.5 shrink-0"></i>
        <div>
            <div class="font-semibold text-red-800">Gambar Tidak Valid</div>
            <div class="text-sm text-red-700 mt-1">{{ $errors->first('image') }}</div>
        </div>
    </div>
@endif

<div class="grid lg:grid-cols-2 gap-6" x-data="{ preview:null, scanning:false, fileName:'' }">
    <form method="POST" action="{{ route('diagnosis.analyze') }}" enctype="multipart/form-data" @submit="scanning=true" class="card p-6 space-y-5">@csrf

        {{-- Tanaman --}}
        <div x-data="{ customCrop: false, cropValue: '{{ old('crop', 'Padi') }}' }">
            <label class="label">Jenis Tanaman</label>
            <div class="flex gap-2 mb-2">
                <button type="button" @click="customCrop=false" :class="!customCrop ? 'bg-brand-600 text-white' : 'bg-ink-100 text-ink-600'" class="text-xs px-3 py-1.5 rounded-lg font-semibold transition">Pilih Daftar</button>
                <button type="button" @click="customCrop=true" :class="customCrop ? 'bg-brand-600 text-white' : 'bg-ink-100 text-ink-600'" class="text-xs px-3 py-1.5 rounded-lg font-semibold transition">Ketik Manual</button>
            </div>
            <template x-if="!customCrop">
                <select name="crop" class="input" x-model="cropValue" required>
                    @foreach(['Padi','Jagung','Cabai','Tomat','Kedelai','Bawang Merah','Kentang','Kopi','Kakao','Singkong','Ubi Jalar','Tebu','Pisang','Mangga','Jeruk'] as $c)
                        <option>{{ $c }}</option>
                    @endforeach
                </select>
            </template>
            <template x-if="customCrop">
                <input name="crop" type="text" class="input" placeholder="Contoh: Pepaya, Rambutan, Durian..." required x-model="cropValue" value="{{ old('crop') }}">
            </template>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="label">Umur Tanaman (hari)</label>
                <input name="age" type="number" class="input" value="{{ old('age', 30) }}" min="0" max="3650">
            </div>
            <div x-data="locationAutocomplete()" class="relative">
                <label class="label">Lokasi</label>
                <input
                    type="text"
                    name="location"
                    class="input"
                    placeholder="Ketik nama kota/kab..."
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
                            :class="i === highlighted ? 'bg-brand-50 text-brand-700' : 'hover:bg-ink-50'"
                            class="px-3 py-2 text-sm cursor-pointer" x-text="item"></li>
                    </template>
                </ul>
            </div>
        </div>

        {{-- Kondisi Tanah (pengganti kelembapan) --}}
        <div>
            <label class="label">Kondisi Tanah</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach([
                    ['Kering','Tanah retak/berdebu','sun'],
                    ['Normal','Tanah lembap ideal','check-circle'],
                    ['Basah/Becek','Tanah jenuh air','droplets'],
                ] as [$val, $desc, $icon])
                    <label class="cursor-pointer">
                        <input type="radio" name="soil_condition" value="{{ $val }}" class="hidden peer" {{ old('soil_condition', 'Normal') === $val ? 'checked' : '' }}>
                        <div class="peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 border-2 border-ink-200 rounded-xl p-3 text-center transition">
                            <i data-lucide="{{ $icon }}" class="w-5 h-5 mx-auto mb-1"></i>
                            <div class="font-semibold text-xs">{{ $val }}</div>
                            <div class="text-[10px] text-ink-500 leading-tight mt-0.5">{{ $desc }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Cuaca --}}
        <div>
            <label class="label">Kondisi Cuaca Saat Ini</label>
            <select name="weather" class="input">
                <option>Cerah</option>
                <option>Berawan</option>
                <option>Hujan Ringan</option>
                <option>Hujan Lebat</option>
            </select>
        </div>

        {{-- Upload Gambar --}}
        <div>
            <label class="label">Foto Tanaman <span class="text-red-500">*</span></label>
            <p class="text-xs text-ink-500 mb-2">Upload foto daun, batang, atau bagian tanaman yang bermasalah. Pastikan gambar jelas dan merupakan tanaman.</p>
            <label class="block border-2 border-dashed border-ink-200 rounded-2xl p-6 text-center cursor-pointer hover:border-brand-500 hover:bg-brand-50/30 transition">
                <input type="file" name="image" accept="image/*" class="hidden"
                    @change="const f=$event.target.files[0]; fileName=f?.name||''; if(f){const r=new FileReader(); r.onload=e=>preview=e.target.result; r.readAsDataURL(f)}">
                <template x-if="!preview">
                    <div>
                        <i data-lucide="upload-cloud" class="w-10 h-10 text-brand-600 mx-auto"></i>
                        <div class="font-semibold mt-2">Klik untuk upload foto</div>
                        <div class="text-xs text-ink-500">JPG/PNG · max 5MB · harus foto tanaman</div>
                    </div>
                </template>
                <template x-if="preview">
                    <div>
                        <img :src="preview" class="max-h-48 mx-auto rounded-xl object-contain">
                        <div class="text-xs text-ink-500 mt-2" x-text="fileName"></div>
                        <div class="text-xs text-brand-600 mt-1">✓ Gambar dipilih</div>
                    </div>
                </template>
            </label>
        </div>

        <button class="btn-primary w-full py-3" :disabled="scanning">
            <template x-if="!scanning">
                <span class="flex items-center justify-center gap-2">
                    <i data-lucide="scan-line" class="w-4 h-4"></i> Analisa dengan AI
                </span>
            </template>
            <template x-if="scanning">
                <span class="flex items-center justify-center gap-2">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> AI sedang menganalisa gambar...
                </span>
            </template>
        </button>
    </form>

    {{-- Riwayat Diagnosa --}}
    <div class="card p-6">
        <div class="font-bold mb-4 flex items-center gap-2">
            <i data-lucide="history" class="w-4 h-4"></i> Riwayat Diagnosa
            <span class="text-xs text-ink-500 font-normal ml-1">(otomatis hapus > 2 bulan)</span>
        </div>
        <div class="divide-y divide-ink-200">
            @forelse($history as $h)
                <div class="flex items-center gap-3 py-3">
                    <a href="{{ route('diagnosis.show',$h) }}" class="flex items-center gap-3 flex-1 hover:bg-ink-50 rounded-xl px-2 -mx-2 py-1 transition">
                        @if($h->image_path)
                            <img src="{{ Storage::url($h->image_path) }}" class="w-10 h-10 rounded-lg object-cover">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-brand-100 grid place-items-center text-brand-700">
                                <i data-lucide="leaf" class="w-4 h-4"></i>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm truncate">{{ $h->disease }}</div>
                            <div class="text-xs text-ink-500">{{ $h->crop }} · {{ $h->created_at->isoFormat('D MMM Y') }}</div>
                        </div>
                        <span class="badge {{ $h->risk_level === 'Tinggi' ? 'bg-red-100 text-red-700' : ($h->risk_level === 'Sedang' ? 'bg-amber-100 text-amber-700' : 'badge-green') }}">
                            {{ (int)$h->confidence }}%
                        </span>
                    </a>
                    <form method="POST" action="{{ route('diagnosis.destroy', $h) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-ink-400 hover:text-red-600 transition" title="Hapus riwayat ini">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="py-10 text-sm text-center text-ink-500">Belum ada riwayat diagnosa.</div>
            @endforelse
        </div>
    </div>
</div>

<script>
function locationAutocomplete() {
    const cities = [
        'Aceh','Banda Aceh','Langsa','Lhokseumawe','Sabang',
        'Medan','Binjai','Pematangsiantar','Tanjungbalai','Tebing Tinggi',
        'Padang','Bukittinggi','Payakumbuh','Solok','Padang Panjang',
        'Pekanbaru','Dumai',
        'Batam','Tanjungpinang',
        'Jambi','Sungai Penuh',
        'Palembang','Lubuklinggau','Pagar Alam','Prabumulih',
        'Bengkulu',
        'Bandar Lampung','Metro',
        'Pangkalpinang',
        'Jakarta','Jakarta Pusat','Jakarta Utara','Jakarta Barat','Jakarta Selatan','Jakarta Timur',
        'Bogor','Depok','Bekasi','Tangerang','Tangerang Selatan','Bandung','Cimahi',
        'Sukabumi','Cirebon','Tasikmalaya','Banjar','Bekasi',
        'Semarang','Solo','Surakarta','Magelang','Salatiga','Pekalongan','Tegal',
        'Yogyakarta','Sleman','Bantul','Gunungkidul','Kulonprogo',
        'Surabaya','Malang','Batu','Blitar','Kediri','Madiun','Mojokerto','Pasuruan','Probolinggo',
        'Denpasar','Badung','Gianyar','Tabanan','Klungkung','Karangasem','Buleleng','Jembrana','Bangli',
        'Mataram','Bima','Sumbawa Besar','Praya',
        'Kupang','Ende','Maumere','Ruteng','Waikabubak','Waingapu',
        'Pontianak','Singkawang',
        'Palangkaraya','Sampit',
        'Banjarmasin','Banjarbaru',
        'Samarinda','Balikpapan','Bontang','Tarakan','Nunukan',
        'Tanjung Selor',
        'Manado','Bitung','Kotamobagu','Tomohon',
        'Palu','Luwuk',
        'Makassar','Parepare','Palopo',
        'Kendari','Baubau',
        'Gorontalo',
        'Mamuju',
        'Ternate','Tidore Kepulauan',
        'Ambon','Tual',
        'Manokwari','Sorong',
        'Jayapura','Merauke','Timika',
        'Klaten','Purwokerto','Cilacap','Purbalingga','Banyumas','Kebumen','Wonosobo',
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
            if (i >= 0 && i < this.results.length) {
                this.query = this.results[i];
                this.open = false;
            }
        },
        moveDown() {
            if (this.highlighted < this.results.length - 1) this.highlighted++;
        },
        moveUp() {
            if (this.highlighted > 0) this.highlighted--;
        },
    };
}
</script>
@endsection
