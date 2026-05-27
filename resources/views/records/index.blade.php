@extends('layouts.app')
@section('title','Catatan Tanaman & Lahan')
@section('content')

<div class="mb-5 flex items-center justify-between flex-wrap gap-3" x-data="{open:false}">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Catatan Tanaman & Lahan</h1>
        <p class="text-ink-500 mt-1 text-sm">Pantau dan catat perkembangan setiap lahan Anda.</p>
    </div>
    <button @click="open=true" class="btn-primary">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Catatan
    </button>

    {{-- Add modal --}}
    <div x-show="open" x-cloak
        class="fixed inset-0 bg-black/50 z-50 grid place-items-center p-4"
        @keydown.escape.window="open=false">
        <div @click.outside="open=false" class="card p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <div class="font-bold text-lg">Catatan Baru</div>
                <button @click="open=false"><i data-lucide="x" class="w-5 h-5 text-ink-400"></i></button>
            </div>
            <form method="POST" action="{{ route('records.store') }}" class="space-y-4">@csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Nama Lahan <span class="text-red-500">*</span></label>
                        <input name="field_name" required class="input" placeholder="Sawah Barat, Kebun Utara...">
                    </div>
                    <div>
                        <label class="label">Jenis Tanaman <span class="text-red-500">*</span></label>
                        <input name="crop" required class="input" placeholder="Padi, Cabai..." list="cropList">
                        <datalist id="cropList">
                            @foreach(['Padi','Jagung','Cabai','Tomat','Kedelai','Bawang Merah','Kentang','Kopi','Kakao','Singkong'] as $c)
                                <option value="{{ $c }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Luas Lahan</label>
                        <div class="relative">
                            <input name="area" type="number" step="0.01" min="0" class="input pr-10" placeholder="0.5">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-ink-400 font-semibold">ha</span>
                        </div>
                        <p class="text-[11px] text-ink-400 mt-1">1 hektar = 10.000 m²</p>
                    </div>
                    <div>
                        <label class="label">Tanggal Tanam</label>
                        <input name="planting_date" type="date" class="input" max="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div>
                    <label class="label">Status Lahan</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([
                            ['Baru Tanam',     'seedling',      'blue'],
                            ['Tumbuh Baik',    'sprout',        'brand'],
                            ['Perlu Perawatan','alert-triangle','amber'],
                            ['Berbuah',        'apple',         'purple'],
                            ['Siap Panen',     'scissors',      'orange'],
                            ['Sudah Panen',    'check-circle',  'green'],
                        ] as [$val, $ico, $col])
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="{{ $val }}" class="hidden peer" {{ $val==='Tumbuh Baik'?'checked':'' }}>
                                <div class="peer-checked:border-{{ $col }}-400 peer-checked:bg-{{ $col }}-50 peer-checked:text-{{ $col }}-700 border-2 border-ink-200 rounded-xl p-2.5 flex items-center gap-2 text-sm transition hover:bg-ink-50 text-ink-600">
                                    <i data-lucide="{{ $ico }}" class="w-4 h-4 shrink-0"></i>
                                    <span class="font-medium">{{ $val }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="label">Catatan Kegiatan</label>
                    <textarea name="notes" class="input" rows="3"
                        placeholder="Contoh: Penyemprotan fungisida, pemupukan, ditemukan hama..."></textarea>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" @click="open=false" class="btn-outline">Batal</button>
                    <button class="btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Summary stats --}}
@if($records->count())
@php
    $summary = [
        $records->count(),
        number_format($records->sum('area'), 2),
        $records->where('status','Perlu Perawatan')->count(),
        $records->whereIn('status',['Siap Panen','Sudah Panen'])->count(),
    ];
@endphp
<div class="grid grid-cols-4 gap-3 mb-5">
    @foreach([['Total Lahan','clipboard-list','brand'],['Total Luas (ha)','map','blue'],['Perlu Perawatan','alert-triangle','amber'],['Siap/Panen','scissors','green']] as $i => [$l,$ic,$c])
        <div class="stat-card text-center py-3">
            <i data-lucide="{{ $ic }}" class="w-4 h-4 mx-auto text-{{ $c }}-600 mb-1"></i>
            <div class="text-xl font-extrabold text-{{ $c }}-700">{{ $summary[$i] }}</div>
            <div class="text-xs text-ink-500">{{ $l }}</div>
        </div>
    @endforeach
</div>
@endif

{{-- Cards grid --}}
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($records as $r)
        @php
            $sc = [
                'Baru Tanam'     =>['bg-blue-100','text-blue-700','seedling'],
                'Tumbuh Baik'    =>['bg-brand-100','text-brand-700','sprout'],
                'Perlu Perawatan'=>['bg-amber-100','text-amber-700','alert-triangle'],
                'Berbuah'        =>['bg-purple-100','text-purple-700','apple'],
                'Siap Panen'     =>['bg-orange-100','text-orange-700','scissors'],
                'Sudah Panen'    =>['bg-green-100','text-green-700','check-circle'],
            ][$r->status] ?? ['bg-ink-100','text-ink-600','sprout'];
        @endphp
        <div class="card p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl {{ $sc[0] }} {{ $sc[1] }} grid place-items-center shrink-0">
                        <i data-lucide="{{ $sc[2] }}" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="font-bold leading-tight">{{ $r->field_name }}</div>
                        <div class="text-xs text-ink-500 mt-0.5">{{ $r->crop }}{{ $r->area ? ' · '.$r->area.' ha' : '' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('records.destroy', $r) }}"
                    onsubmit="return confirm('Hapus catatan ini?')">@csrf @method('DELETE')
                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-ink-300 hover:text-red-600 transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc[0] }} {{ $sc[1] }}">
                <i data-lucide="{{ $sc[2] }}" class="w-3 h-3"></i>
                {{ $r->status }}
            </span>

            @if($r->planting_date)
                @php $hst = \Carbon\Carbon::parse($r->planting_date)->diffInDays(now()); @endphp
                <div class="mt-2 text-xs text-ink-500 flex items-center gap-1">
                    <i data-lucide="calendar" class="w-3 h-3"></i>
                    Tanam {{ \Carbon\Carbon::parse($r->planting_date)->isoFormat('D MMM Y') }}
                    <span class="font-semibold text-brand-600 ml-1">({{ $hst }} HST)</span>
                </div>
            @endif

            @if($r->notes)
                <div class="mt-2.5 p-2.5 rounded-xl bg-ink-50 text-xs text-ink-600 leading-relaxed line-clamp-3">
                    {{ $r->notes }}
                </div>
            @endif
        </div>
    @empty
        <div class="sm:col-span-2 lg:col-span-3 card p-14 text-center text-ink-500">
            <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-3 text-ink-300"></i>
            <div class="font-semibold mb-1">Belum ada catatan lahan</div>
            <p class="text-sm">Mulai catat kegiatan lahan untuk memantau tanaman Anda.</p>
        </div>
    @endforelse
</div>
@endsection
