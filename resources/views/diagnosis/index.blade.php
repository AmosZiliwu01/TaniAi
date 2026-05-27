@extends('layouts.app')
@section('title','Diagnosa Tanaman')
@section('content')

@php
  $flashId   = session('flash_diagnosis_id');
  $flashDiag = $flashId ? $history->firstWhere('id', $flashId) : null;
@endphp

<div class="mb-5">
    <h1 class="text-2xl sm:text-3xl font-extrabold">Diagnosa Tanaman</h1>
    <p class="text-ink-500 mt-1 text-sm">Foto atau ambil gambar tanaman, AI akan menganalisa secara akurat.</p>
</div>

{{-- Error banner --}}
@if($errors->has('image'))
    <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 flex items-start gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 shrink-0 mt-0.5"></i>
        <div>
            <div class="font-semibold text-red-800">Gambar Tidak Valid</div>
            <p class="text-sm text-red-700 mt-0.5">{{ $errors->first('image') }}</p>
        </div>
    </div>
@endif

{{-- Main area: two-panel on desktop, stacked on mobile --}}
<div x-data="diagnosisApp()" class="space-y-5">

    <div class="grid lg:grid-cols-2 gap-5">

        {{-- ── LEFT: Upload/Camera Panel ── --}}
        <div class="card p-6 flex flex-col gap-5">

            {{-- Tabs: Unggah / Kamera --}}
            <div class="flex bg-ink-50 p-1 rounded-xl gap-1">
                <button @click="mode='upload'"
                    :class="mode==='upload' ? 'bg-white shadow text-ink-900' : 'text-ink-500'"
                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm font-semibold transition">
                    <i data-lucide="upload" class="w-4 h-4"></i> Unggah
                </button>
                <button @click="mode='camera'"
                    :class="mode==='camera' ? 'bg-white shadow text-ink-900' : 'text-ink-500'"
                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg text-sm font-semibold transition">
                    <i data-lucide="camera" class="w-4 h-4"></i> Kamera
                </button>
            </div>

            <form id="diagForm" method="POST" action="{{ route('diagnosis.analyze') }}"
                enctype="multipart/form-data" @submit.prevent="submitDiag($event)">@csrf

                {{-- Image area --}}
                <div class="relative">
                    {{-- Upload mode --}}
                    <div x-show="mode==='upload'">
                        <label class="block border-2 border-dashed border-ink-200 rounded-2xl overflow-hidden cursor-pointer hover:border-brand-400 transition"
                            :class="preview ? 'border-brand-300' : ''"
                            style="min-height:200px">
                            <input type="file" name="image" id="imageInput" accept="image/*" class="hidden"
                                @change="handleFile($event)">
                            <template x-if="!preview">
                                <div class="flex flex-col items-center justify-center py-12 gap-3 text-ink-400">
                                    <i data-lucide="image-plus" class="w-12 h-12 text-brand-400"></i>
                                    <div class="font-semibold text-ink-700">Klik untuk pilih foto</div>
                                    <div class="text-xs">JPG · PNG · WebP · maks 8MB</div>
                                </div>
                            </template>
                            <template x-if="preview">
                                <div class="relative">
                                    <img :src="preview" class="w-full max-h-56 object-cover rounded-2xl">
                                    <div class="absolute inset-0 bg-black/0 hover:bg-black/20 transition rounded-2xl flex items-center justify-center">
                                        <span class="opacity-0 hover:opacity-100 text-white text-xs font-semibold bg-black/50 px-3 py-1 rounded-lg transition">Ganti Foto</span>
                                    </div>
                                </div>
                            </template>
                        </label>
                    </div>

                    {{-- Camera mode --}}
                    <div x-show="mode==='camera'" x-cloak>
                        <div class="relative rounded-2xl overflow-hidden bg-black" style="min-height:200px">
                            <video id="cameraVideo" autoplay playsinline class="w-full rounded-2xl" style="max-height:280px;object-fit:cover"></video>
                            <canvas id="cameraCanvas" class="hidden"></canvas>
                            <template x-if="cameraPreview">
                                <img :src="cameraPreview" class="absolute inset-0 w-full h-full object-cover rounded-2xl">
                            </template>
                            <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-3">
                                <template x-if="!cameraPreview">
                                    <button type="button" @click="capture()"
                                        class="w-14 h-14 rounded-full bg-white border-4 border-brand-500 flex items-center justify-center shadow-lg hover:scale-105 transition">
                                        <div class="w-10 h-10 rounded-full bg-brand-500"></div>
                                    </button>
                                </template>
                                <template x-if="cameraPreview">
                                    <button type="button" @click="retake()"
                                        class="btn-outline bg-white text-sm px-4 py-2">
                                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Ulang
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Crop selector --}}
                <div x-data="{custom:false}">
                    <label class="label">Jenis Tanaman <span class="text-red-500">*</span></label>
                    <div class="flex gap-1.5 mb-2">
                        <button type="button" @click="custom=false"
                            :class="!custom?'bg-brand-600 text-white':'bg-ink-100 text-ink-600'"
                            class="text-xs px-3 py-1.5 rounded-lg font-semibold transition">Pilih</button>
                        <button type="button" @click="custom=true"
                            :class="custom?'bg-brand-600 text-white':'bg-ink-100 text-ink-600'"
                            class="text-xs px-3 py-1.5 rounded-lg font-semibold transition">Ketik Manual</button>
                    </div>
                    <template x-if="!custom">
                        <select name="crop" class="input" required>
                            @foreach(['Padi','Jagung','Cabai','Tomat','Kedelai','Bawang Merah','Kentang','Kopi','Kakao','Singkong','Pisang','Mangga','Jeruk','Semangka'] as $c)
                                <option {{ old('crop')===$c?'selected':'' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </template>
                    <template x-if="custom">
                        <input name="crop" type="text" required class="input" placeholder="Contoh: Pepaya, Rambutan..." value="{{ old('crop') }}">
                    </template>
                </div>

                {{-- Age + Soil --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Umur Tanaman</label>
                        <div class="relative">
                            <input name="age" type="number" class="input pr-10" value="{{ old('age',30) }}" min="0" max="3650">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-ink-400">hari</span>
                        </div>
                    </div>
                    <div>
                        <label class="label">Kondisi Tanah</label>
                        <select name="soil_condition" class="input">
                            <option>Normal</option><option>Kering</option><option>Basah/Becek</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="weather" value="{{ session('current_weather', 'Cerah') }}">
                <input type="file" name="image_camera" id="cameraInput" class="hidden" accept="image/*">

                <button type="submit" id="analyzeBtn"
                    class="btn-primary w-full py-3 text-base"
                    :disabled="scanning || (!preview && !cameraPreview)">
                    <template x-if="!scanning">
                        <span class="flex items-center justify-center gap-2">
                            <i data-lucide="scan-line" class="w-5 h-5"></i> Analisa dengan AI
                        </span>
                    </template>
                    <template x-if="scanning">
                        <span class="flex items-center justify-center gap-2">
                            <i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> AI sedang menganalisa...
                        </span>
                    </template>
                </button>
            </form>
        </div>

        {{-- ── RIGHT: Result Panel ── --}}
        <div class="card p-6 flex flex-col" id="resultPanel">

            {{-- Skeleton / processing state --}}
            <template x-if="scanning">
                <div class="flex-1 flex flex-col gap-4 animate-pulse">
                    <div class="h-6 bg-ink-100 rounded-xl w-2/3"></div>
                    <div class="h-4 bg-ink-100 rounded-xl w-1/2"></div>
                    <div class="h-32 bg-ink-100 rounded-2xl"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-ink-100 rounded-xl"></div>
                        <div class="h-4 bg-ink-100 rounded-xl w-5/6"></div>
                        <div class="h-4 bg-ink-100 rounded-xl w-4/6"></div>
                    </div>
                    <div class="mt-auto text-center text-sm text-ink-500 flex items-center justify-center gap-2 pt-4">
                        <i data-lucide="cpu" class="w-4 h-4 text-brand-500 animate-spin"></i>
                        AI sedang menganalisa gambar Anda...
                    </div>
                </div>
            </template>

            {{-- Empty state --}}
            <template x-if="!scanning && !hasResult">
                <div class="flex-1 flex flex-col items-center justify-center text-center gap-3 py-12">
                    <div class="w-16 h-16 rounded-2xl bg-brand-50 grid place-items-center text-brand-400">
                        <i data-lucide="scan-line" class="w-8 h-8"></i>
                    </div>
                    <div class="font-semibold text-ink-700">Hasil Diagnosa</div>
                    <p class="text-sm text-ink-400 max-w-xs">Upload foto tanaman dan klik "Analisa dengan AI" untuk melihat hasil diagnosa di sini.</p>
                </div>
            </template>

            {{-- Result (from server) --}}
            @if($flashDiag)
                <div x-data="{expanded:false}">
                    @include('diagnosis._result_panel', ['d' => $flashDiag])
                </div>
            @endif

            {{-- JS result placeholder --}}
            <div id="jsResult" x-show="hasResult && !scanning" x-cloak></div>
        </div>
    </div>

    {{-- ── History Section ── --}}
    @if($history->count())
    <div class="card p-5">
        <div class="font-bold mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-brand-600"></i>
                Riwayat Diagnosa
                <span class="text-xs text-ink-400 font-normal">(auto hapus > 2 bulan)</span>
            </div>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($history as $h)
                <a href="{{ route('diagnosis.show', $h) }}"
                    class="flex items-center gap-3 p-3 rounded-xl hover:bg-brand-50 border border-transparent hover:border-brand-200 transition group">
                    @if($h->image_path)
                        <img src="{{ Storage::url($h->image_path) }}"
                            class="w-12 h-12 rounded-xl object-cover shrink-0 border border-ink-100"
                            loading="lazy"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='grid'">
                        <div style="display:none"
                            class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 place-items-center shrink-0">
                            <i data-lucide="leaf" class="w-5 h-5"></i>
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-xl bg-brand-100 grid place-items-center text-brand-700 shrink-0">
                            <i data-lucide="leaf" class="w-5 h-5"></i>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate">{{ $h->disease }}</div>
                        <div class="text-xs text-ink-500 mt-0.5">{{ $h->crop }} · {{ $h->created_at->isoFormat('D MMM Y') }}</div>
                        <span class="badge text-[10px] mt-1 {{ $h->risk_level==='Tinggi'?'bg-red-100 text-red-700':($h->risk_level==='Sedang'?'bg-amber-100 text-amber-700':'badge-green') }}">
                            {{ $h->risk_level }} · {{ (int)$h->confidence }}%
                        </span>
                    </div>
                    <form method="POST" action="{{ route('diagnosis.destroy', $h) }}" class="shrink-0 opacity-0 group-hover:opacity-100 transition"
                        @click.prevent.stop="if(confirm('Hapus riwayat ini?'))$el.submit()">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-ink-300 hover:text-red-600 transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
function diagnosisApp() {
    return {
        mode: 'upload',
        preview: null,
        cameraPreview: null,
        cameraStream: null,
        scanning: false,
        hasResult: {{ $flashDiag ? 'true' : 'false' }},

        handleFile(e) {
            const f = e.target.files[0];
            if (!f) return;
            const r = new FileReader();
            r.onload = ev => { this.preview = ev.target.result; };
            r.readAsDataURL(f);
        },

        async startCamera() {
            try {
                this.cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1280 } }
                });
                this.$nextTick(() => {
                    const v = document.getElementById('cameraVideo');
                    if (v) { v.srcObject = this.cameraStream; }
                });
            } catch(e) {
                alert('Kamera tidak dapat diakses. Gunakan mode unggah.');
                this.mode = 'upload';
            }
        },

        stopCamera() {
            if (this.cameraStream) {
                this.cameraStream.getTracks().forEach(t => t.stop());
                this.cameraStream = null;
            }
        },

        capture() {
            const v = document.getElementById('cameraVideo');
            const c = document.getElementById('cameraCanvas');
            if (!v || !c) return;
            c.width  = v.videoWidth;
            c.height = v.videoHeight;
            c.getContext('2d').drawImage(v, 0, 0);
            this.cameraPreview = c.toDataURL('image/jpeg', 0.9);
            // Convert to file
            c.toBlob(blob => {
                const file = new File([blob], 'camera.jpg', { type: 'image/jpeg' });
                const dt   = new DataTransfer();
                dt.items.add(file);
                document.getElementById('imageInput').files = dt.files;
            }, 'image/jpeg', 0.9);
            this.stopCamera();
        },

        retake() {
            this.cameraPreview = null;
            this.startCamera();
        },

        submitDiag(e) {
            if (!this.preview && !this.cameraPreview) {
                alert('Silakan upload atau ambil foto tanaman terlebih dahulu.');
                return;
            }
            this.scanning = true;
            this.hasResult = false;
            // Scroll result panel into view on mobile
            this.$nextTick(() => {
                const panel = document.getElementById('resultPanel');
                if (panel && window.innerWidth < 1024) {
                    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
            e.target.submit();
        },

        $watch: {
            mode(val) {
                if (val === 'camera') this.startCamera();
                else this.stopCamera();
            }
        }
    };
}
</script>
@endsection
