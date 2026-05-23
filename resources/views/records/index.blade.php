@extends('layouts.app')
@section('title','Input & Catatan')
@section('content')

<div class="mb-6 flex items-center justify-between" x-data="{open:false}">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Input & Catatan Lahan</h1>
        <p class="text-ink-500 mt-1">Catat kegiatan dan kondisi lahan Anda.</p>
    </div>
    <button @click="open=true" class="btn-primary"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Catatan</button>

    <div x-show="open" x-cloak class="fixed inset-0 bg-ink-900/50 z-40 grid place-items-center p-4">
        <div @click.outside="open=false" class="card p-6 w-full max-w-lg">
            <div class="flex items-center justify-between mb-4">
                <div class="font-bold">Tambah Catatan Lahan</div>
                <button @click="open=false"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('records.store') }}" class="space-y-3">@csrf
                <div><label class="label">Tanaman</label><input name="crop" required class="input"></div>
                <div><label class="label">Nama Lahan</label><input name="field_name" required class="input"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="label">Luas (ha)</label><input name="area" type="number" step="0.01" class="input"></div>
                    <div><label class="label">Tanggal Tanam</label><input name="planting_date" type="date" class="input"></div>
                </div>
                <div><label class="label">Status</label>
                    <select name="status" class="input">
                        <option>Tumbuh Baik</option><option>Perlu Perawatan</option><option>Berbuah</option><option>Siap Panen</option>
                    </select>
                </div>
                <div><label class="label">Catatan</label><textarea name="notes" class="input" rows="3"></textarea></div>
                <button class="btn-primary w-full">Simpan</button>
            </form>
        </div>
    </div>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($records as $r)
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-100 grid place-items-center text-brand-700"><i data-lucide="sprout" class="w-5 h-5"></i></div>
                    <div>
                        <div class="font-bold">{{ $r->field_name }}</div>
                        <div class="text-xs text-ink-500">{{ $r->crop }} · {{ $r->area }} ha</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('records.destroy',$r) }}">@csrf @method('DELETE')
                    <button class="p-2 rounded-lg hover:bg-red-50 text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                </form>
            </div>
            @php $st=$r->status; $col=str_contains($st,'Perawatan')?'amber':(str_contains($st,'Panen')||$st==='Berbuah'?'brand':'brand'); @endphp
            <div class="mt-3"><span class="badge bg-{{ $col }}-100 text-{{ $col }}-700">{{ $st }}</span></div>
            <div class="text-xs text-ink-500 mt-2">Tanam: {{ $r->planting_date ? \Carbon\Carbon::parse($r->planting_date)->isoFormat('D MMM Y') : '-' }}</div>
            @if($r->notes)<p class="text-sm text-ink-600 mt-3">{{ $r->notes }}</p>@endif
        </div>
    @empty
        <div class="col-span-full card p-10 text-center text-ink-500">Belum ada catatan lahan. Tambahkan yang pertama!</div>
    @endforelse
</div>
@endsection
