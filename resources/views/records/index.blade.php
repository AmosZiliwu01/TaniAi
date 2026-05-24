@extends('layouts.app')
@section('title','Catatan Lahan')
@section('content')

<div class="mb-6 flex items-center justify-between" x-data="{open:false, editRecord: null}">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Catatan Lahan</h1>
        <p class="text-ink-500 mt-1">Catat dan pantau perkembangan setiap lahan Anda.</p>
    </div>
    <button @click="open=true; editRecord=null" class="btn-primary">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Catatan
    </button>

    {{-- Modal Tambah --}}
    <div x-show="open" x-cloak class="fixed inset-0 bg-ink-900/50 z-40 grid place-items-center p-4" @keydown.escape.window="open=false">
        <div @click.outside="open=false" class="card p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <div class="font-bold">Catatan Lahan Baru</div>
                <button @click="open=false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('records.store') }}" class="space-y-4">@csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Tanaman</label>
                        <input name="crop" required class="input" placeholder="Padi, Jagung..." list="crop-list">
                        <datalist id="crop-list">
                            @foreach(['Padi','Jagung','Cabai','Tomat','Kedelai','Bawang Merah','Kentang','Kopi','Kakao'] as $c)
                                <option value="{{ $c }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="label">Nama Lahan</label>
                        <input name="field_name" required class="input" placeholder="Contoh: Sawah Barat">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Luas Lahan (ha)</label>
                        <input name="area" type="number" step="0.01" min="0" class="input" placeholder="0.5">
                    </div>
                    <div>
                        <label class="label">Tanggal Tanam</label>
                        <input name="planting_date" type="date" class="input">
                    </div>
                </div>

                <div>
                    <label class="label">Status Tanaman</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([
                            ['Baru Tanam','seedling'],
                            ['Tumbuh Baik','sprout'],
                            ['Perlu Perawatan','alert-triangle'],
                            ['Berbuah','apple'],
                            ['Siap Panen','scissors'],
                            ['Sudah Panen','check-circle'],
                        ] as [$stat, $ico])
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="{{ $stat }}" class="hidden peer" {{ $stat==='Tumbuh Baik'?'checked':'' }}>
                                <div class="peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 border border-ink-200 rounded-xl p-2.5 flex items-center gap-2 text-sm transition hover:bg-ink-50">
                                    <i data-lucide="{{ $ico }}" class="w-4 h-4 shrink-0"></i>
                                    {{ $stat }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="label">Kegiatan / Catatan</label>
                    <textarea name="notes" class="input" rows="4" placeholder="Contoh: Penyemprotan fungisida mancozeb 2g/L. Pupuk susulan UREA 50kg/ha. Ditemukan gejala bercak daun di lahan bagian timur..."></textarea>
                </div>

                <button class="btn-primary w-full">Simpan Catatan</button>
            </form>
        </div>
    </div>
</div>

@if(session('status'))
    <div class="mb-4 p-3 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 text-sm">{{ session('status') }}</div>
@endif

{{-- Summary Stats --}}
@if($records->count())
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $statusCount = $records->groupBy('status');
            $perlu = $records->where('status', 'Perlu Perawatan')->count();
            $panen = $records->whereIn('status', ['Siap Panen', 'Sudah Panen'])->count();
            $totalArea = $records->sum('area');
        @endphp
        <div class="card p-4 text-center">
            <div class="text-2xl font-extrabold text-brand-700">{{ $records->count() }}</div>
            <div class="text-xs text-ink-500 mt-1">Total Lahan</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-extrabold text-brand-700">{{ number_format($totalArea, 1) }}</div>
            <div class="text-xs text-ink-500 mt-1">Luas (ha)</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-extrabold {{ $perlu ? 'text-amber-600' : 'text-ink-700' }}">{{ $perlu }}</div>
            <div class="text-xs text-ink-500 mt-1">Perlu Perawatan</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-extrabold text-green-600">{{ $panen }}</div>
            <div class="text-xs text-ink-500 mt-1">Siap/Sudah Panen</div>
        </div>
    </div>
@endif

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($records as $r)
        @php
            $statusColors = [
                'Tumbuh Baik'      => 'bg-brand-100 text-brand-700',
                'Baru Tanam'       => 'bg-blue-100 text-blue-700',
                'Perlu Perawatan'  => 'bg-amber-100 text-amber-700',
                'Berbuah'          => 'bg-purple-100 text-purple-700',
                'Siap Panen'       => 'bg-orange-100 text-orange-700',
                'Sudah Panen'      => 'bg-green-100 text-green-700',
            ];
            $statusIcons = [
                'Tumbuh Baik'      => 'sprout',
                'Baru Tanam'       => 'seedling',
                'Perlu Perawatan'  => 'alert-triangle',
                'Berbuah'          => 'apple',
                'Siap Panen'       => 'scissors',
                'Sudah Panen'      => 'check-circle',
            ];
            $sc = $statusColors[$r->status] ?? 'bg-ink-100 text-ink-600';
            $si = $statusIcons[$r->status] ?? 'sprout';
        @endphp
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-100 grid place-items-center text-brand-700">
                        <i data-lucide="{{ $si }}" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="font-bold">{{ $r->field_name }}</div>
                        <div class="text-xs text-ink-500">{{ $r->crop }} · {{ $r->area ? $r->area.' ha' : '-' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('records.destroy',$r) }}">@csrf @method('DELETE')
                    <button class="p-2 rounded-lg hover:bg-red-50 text-ink-400 hover:text-red-600 transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full {{ $sc }}">
                <i data-lucide="{{ $si }}" class="w-3 h-3"></i> {{ $r->status }}
            </span>

            @if($r->planting_date)
                <div class="mt-2 text-xs text-ink-500 flex items-center gap-1">
                    <i data-lucide="calendar" class="w-3 h-3"></i>
                    Tanam: {{ \Carbon\Carbon::parse($r->planting_date)->isoFormat('D MMM Y') }}
                    @php $age = \Carbon\Carbon::parse($r->planting_date)->diffInDays(now()); @endphp
                    <span class="text-brand-600 font-semibold">({{ $age }} HST)</span>
                </div>
            @endif

            @if($r->notes)
                <div class="mt-3 p-3 rounded-xl bg-ink-50 text-xs text-ink-600 leading-relaxed line-clamp-3">
                    {{ $r->notes }}
                </div>
            @endif

            <div class="mt-3 flex gap-2">
                <a href="{{ route('diagnosis.index') }}?crop={{ urlencode($r->crop) }}" class="btn-outline text-xs flex-1 text-center py-1.5">
                    <i data-lucide="scan-line" class="w-3 h-3"></i> Diagnosa
                </a>
                <a href="{{ route('chat.index') }}" class="btn-outline text-xs flex-1 text-center py-1.5">
                    <i data-lucide="bot" class="w-3 h-3"></i> Tanya AI
                </a>
            </div>
        </div>
    @empty
        <div class="sm:col-span-2 lg:col-span-3 card p-12 text-center text-ink-500">
            <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-3 text-ink-300"></i>
            <div class="font-semibold mb-1">Belum ada catatan lahan</div>
            <p class="text-sm">Mulai catat kegiatan lahan Anda untuk memantau perkembangan tanaman.</p>
        </div>
    @endforelse
</div>
@endsection
