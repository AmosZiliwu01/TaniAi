@extends('layouts.admin')
@section('title','Diagnosis Logs')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Diagnosis Logs</h1>
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-ink-500 border-b border-ink-200">
                <tr>
                    <th class="py-3 px-4">Foto</th>
                    <th class="px-4">Pengguna</th>
                    <th class="px-4">Tanaman</th>
                    <th class="px-4">Penyakit</th>
                    <th class="px-4 text-center">Conf.</th>
                    <th class="px-4">Risiko</th>
                    <th class="px-4">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-200">
                @foreach($logs as $l)
                    <tr class="hover:bg-ink-50">
                        <td class="py-2.5 px-4">
                            @if($l->image_path)
                                <img src="{{ Storage::url($l->image_path) }}"
                                    class="w-10 h-10 rounded-xl object-cover border border-ink-100"
                                    loading="lazy" onerror="this.style.display='none'">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-brand-100 grid place-items-center text-brand-600">
                                    <i data-lucide="leaf" class="w-4 h-4"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 font-semibold">{{ $l->user->name ?? '—' }}</td>
                        <td class="px-4">{{ $l->crop }}</td>
                        <td class="px-4 max-w-[200px] truncate" title="{{ $l->disease }}">{{ $l->disease }}</td>
                        <td class="px-4 text-center"><span class="badge-green text-xs">{{ (int)$l->confidence }}%</span></td>
                        <td class="px-4">
                            <span class="badge text-xs {{ $l->risk_level==='Tinggi'?'bg-red-100 text-red-700':($l->risk_level==='Sedang'?'bg-amber-100 text-amber-700':'badge-green') }}">
                                {{ $l->risk_level }}
                            </span>
                        </td>
                        <td class="px-4 text-ink-400 text-xs">{{ $l->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-ink-200">{{ $logs->links() }}</div>
</div>
@endsection
