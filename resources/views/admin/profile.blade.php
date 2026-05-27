@extends('layouts.admin')
@section('title','Profil Admin')
@section('content')
@php $user = auth()->user(); @endphp
<h1 class="text-2xl font-extrabold mb-6">Profil Admin</h1>
<div class="grid lg:grid-cols-3 gap-6">
    <div class="card p-6 flex flex-col items-center text-center gap-4">
        <div class="w-24 h-24 rounded-2xl bg-gradient-to-br {{ $user->avatar_gradient }} grid place-items-center text-white text-4xl font-extrabold shadow-lg">
            {{ $user->initial }}
        </div>
        <div>
            <div class="text-xl font-extrabold">{{ $user->name }}</div>
            <div class="text-sm text-ink-500">{{ $user->email }}</div>
            <span class="badge bg-purple-100 text-purple-700 mt-2 inline-block">Administrator</span>
        </div>
        <a href="{{ route('admin.settings') }}" class="btn-primary w-full text-sm">
            <i data-lucide="settings" class="w-4 h-4"></i> Edit Pengaturan
        </a>
    </div>
    <div class="lg:col-span-2 space-y-4">
        <div class="card p-5">
            <div class="font-bold mb-3">Statistik Sistem</div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([
                    ['Pengguna',    \App\Models\User::count(),              'users'],
                    ['Diagnosa',    \App\Models\Diagnosis::count(),         'scan-line'],
                    ['AI Chat',     \App\Models\AiChat::count(),            'message-circle'],
                    ['Postingan',   \App\Models\CommunityPost::count(),     'message-square'],
                ] as [$label,$val,$icon])
                    <div class="p-3 rounded-xl bg-ink-50 text-center">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4 mx-auto text-brand-600 mb-1"></i>
                        <div class="text-xl font-extrabold">{{ $val }}</div>
                        <div class="text-xs text-ink-500">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card p-5">
            <div class="font-bold mb-3">Info Akun</div>
            <div class="divide-y divide-ink-100 text-sm">
                @foreach([['Nama',$user->name],['Email',$user->email],['Role',ucfirst($user->role)],['Lokasi',$user->location??'—'],['Bergabung',$user->created_at->isoFormat('D MMM Y')]] as [$k,$v])
                    <div class="flex justify-between py-2.5">
                        <span class="text-ink-500">{{ $k }}</span>
                        <span class="font-semibold">{{ $v }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
