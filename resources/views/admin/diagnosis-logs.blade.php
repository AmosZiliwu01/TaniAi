@extends('layouts.admin')
@section('title','Diagnosis Logs')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Diagnosis Logs</h1>
<div class="card p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-xs uppercase tracking-wide text-ink-500 border-b border-ink-200">
                <tr><th class="py-3">Pengguna</th><th>Tanaman</th><th>Penyakit</th><th>Confidence</th><th>Risiko</th><th>Waktu</th></tr>
            </thead>
            <tbody class="divide-y divide-ink-200">
                @foreach($logs as $l)
                    <tr>
                        <td class="py-3 font-semibold">{{ $l->user->name ?? '-' }}</td>
                        <td>{{ $l->crop }}</td>
                        <td>{{ $l->disease }}</td>
                        <td><span class="badge-green">{{ (int)$l->confidence }}%</span></td>
                        <td>{{ $l->risk_level }}</td>
                        <td class="text-ink-500">{{ $l->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
