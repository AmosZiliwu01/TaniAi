@extends('layouts.app')
@section('title','Diagnosa Tanaman')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Diagnosa Tanaman</h1>
    <p class="text-ink-500 mt-1">Upload foto daun tanaman untuk mendapatkan analisa AI.</p>
</div>

<div class="grid lg:grid-cols-2 gap-6" x-data="{ preview:null, scanning:false, fileName:'' }">
    <form method="POST" action="{{ route('diagnosis.analyze') }}" enctype="multipart/form-data" @submit="scanning=true" class="card p-6 space-y-5">@csrf
        <div>
            <label class="label">Tanaman</label>
            <select name="crop" class="input" required>
                @foreach(['Padi','Jagung','Cabai','Tomat','Kedelai','Bawang Merah','Kentang','Kopi','Kakao'] as $c)
                    <option>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Umur (hari)</label><input name="age" type="number" class="input" value="30"></div>
            <div><label class="label">Lokasi</label><input name="location" class="input" value="{{ auth()->user()->location }}"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Kelembapan (%)</label><input name="humidity" type="number" class="input" value="78"></div>
            <div><label class="label">Cuaca</label>
                <select name="weather" class="input">
                    <option>Cerah</option><option>Berawan</option><option>Hujan</option><option>Hujan Lebat</option>
                </select>
            </div>
        </div>

        <div>
            <label class="label">Foto Daun</label>
            <label class="block border-2 border-dashed border-ink-200 rounded-2xl p-6 text-center cursor-pointer hover:border-brand-500 hover:bg-brand-50/30 transition">
                <input type="file" name="image" accept="image/*" class="hidden"
                    @change="const f=$event.target.files[0]; fileName=f?.name||''; if(f){const r=new FileReader(); r.onload=e=>preview=e.target.result; r.readAsDataURL(f)}">
                <template x-if="!preview">
                    <div>
                        <i data-lucide="upload-cloud" class="w-10 h-10 text-brand-600 mx-auto"></i>
                        <div class="font-semibold mt-2">Klik untuk upload foto</div>
                        <div class="text-xs text-ink-500">atau drag & drop · JPG/PNG · max 5MB</div>
                    </div>
                </template>
                <template x-if="preview">
                    <div>
                        <img :src="preview" class="max-h-48 mx-auto rounded-xl">
                        <div class="text-xs text-ink-500 mt-2" x-text="fileName"></div>
                    </div>
                </template>
            </label>
        </div>

        <button class="btn-primary w-full py-3" :disabled="scanning">
            <template x-if="!scanning"><span class="flex items-center gap-2"><i data-lucide="scan-line" class="w-4 h-4"></i> Analisa dengan AI</span></template>
            <template x-if="scanning"><span class="flex items-center gap-2"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> AI sedang menganalisa...</span></template>
        </button>
    </form>

    <div class="card p-6">
        <div class="font-bold mb-4 flex items-center gap-2"><i data-lucide="history" class="w-4 h-4"></i> Riwayat Diagnosa</div>
        <div class="divide-y divide-ink-200">
            @forelse($history as $h)
                <a href="{{ route('diagnosis.show',$h) }}" class="flex items-center gap-3 py-3 hover:bg-ink-50 rounded-xl px-2 -mx-2">
                    <div class="w-10 h-10 rounded-lg bg-brand-100 grid place-items-center text-brand-700"><i data-lucide="leaf" class="w-4 h-4"></i></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate">{{ $h->disease }}</div>
                        <div class="text-xs text-ink-500">{{ $h->crop }} · {{ $h->created_at->isoFormat('D MMM Y') }}</div>
                    </div>
                    <span class="badge-green">{{ (int)$h->confidence }}%</span>
                </a>
            @empty
                <div class="py-10 text-sm text-center text-ink-500">Belum ada riwayat diagnosa.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
