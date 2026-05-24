@extends('layouts.admin')
@section('title','Profil Admin')
@section('content')
@php $user = auth()->user(); @endphp

<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Profil Admin</h1>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="card p-6 flex flex-col items-center text-center gap-4">
        <div class="w-24 h-24 rounded-2xl bg-brand-gradient grid place-items-center text-white text-3xl font-extrabold shadow-glow">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <div class="text-xl font-extrabold">{{ $user->name }}</div>
            <div class="text-sm text-ink-500">{{ $user->email }}</div>
            <span class="badge bg-purple-100 text-purple-700 mt-2">Administrator</span>
        </div>
        <a href="{{ route('admin.settings') }}" class="btn-primary w-full text-sm">
            <i data-lucide="settings" class="w-4 h-4"></i> Edit Pengaturan
        </a>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <div class="card p-5">
            <div class="font-bold mb-3">Statistik Admin</div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([
                    ['Total User', \App\Models\User::count(), 'users'],
                    ['Total Diagnosa', \App\Models\Diagnosis::count(), 'scan-line'],
                    ['AI Chat', \App\Models\AiChat::count(), 'message-circle'],
                    ['Postingan', \App\Models\CommunityPost::count(), 'message-square'],
                ] as [$label, $val, $icon])
                    <div class="p-4 rounded-xl bg-ink-50 text-center">
                        <i data-lucide="{{ $icon }}" class="w-5 h-5 mx-auto text-brand-600 mb-1"></i>
                        <div class="text-xl font-extrabold">{{ $val }}</div>
                        <div class="text-xs text-ink-500">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-5">
            <div class="font-bold mb-3">Info Akun</div>
            <div class="space-y-2 text-sm divide-y divide-ink-200">
                <div class="flex justify-between py-2"><span class="text-ink-500">Nama</span><span class="font-semibold">{{ $user->name }}</span></div>
                <div class="flex justify-between py-2"><span class="text-ink-500">Email</span><span class="font-semibold">{{ $user->email }}</span></div>
                <div class="flex justify-between py-2"><span class="text-ink-500">Role</span><span class="font-semibold">{{ ucfirst($user->role) }}</span></div>
                <div class="flex justify-between py-2"><span class="text-ink-500">Lokasi</span><span class="font-semibold">{{ $user->location ?? '-' }}</span></div>
                <div class="flex justify-between py-2"><span class="text-ink-500">Bergabung</span><span class="font-semibold">{{ $user->created_at->isoFormat('D MMM Y') }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
