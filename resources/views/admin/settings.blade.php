@extends('layouts.admin')
@section('title','Pengaturan Admin')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Pengaturan Akun Admin</h1>

@if(session('status'))
    <div class="mb-5 p-3 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 text-sm flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('status') }}
    </div>
@endif

<div class="grid lg:grid-cols-2 gap-6">
    <div class="card p-6">
        <div class="font-bold mb-4">Informasi Profil</div>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">@csrf @method('PATCH')
            <div>
                <label class="label">Nama</label>
                <input name="name" required class="input" value="{{ old('name', auth()->user()->name) }}">
                @error('name')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="label">Email</label>
                <input type="email" name="email" required class="input" value="{{ old('email', auth()->user()->email) }}">
                @error('email')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="label">Lokasi</label>
                <input name="location" class="input" value="{{ old('location', auth()->user()->location) }}" placeholder="Kota/Kabupaten">
            </div>
            <div>
                <label class="label">No. Telepon</label>
                <input name="phone" class="input" value="{{ old('phone', auth()->user()->phone) }}" placeholder="08xxxxxxxxxx">
            </div>
            <button class="btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Simpan</button>
        </form>
    </div>

    <div class="card p-6">
        <div class="font-bold mb-4">Ubah Password</div>
        <form method="POST" action="{{ route('admin.settings.password') }}" class="space-y-4">@csrf @method('PATCH')
            <div>
                <label class="label">Password Lama</label>
                <input type="password" name="current_password" required class="input">
                @error('current_password')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="label">Password Baru</label>
                <input type="password" name="password" required minlength="6" class="input">
            </div>
            <div>
                <label class="label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required class="input">
            </div>
            @error('password')<div class="text-xs text-red-500">{{ $message }}</div>@enderror
            <button class="btn-primary"><i data-lucide="shield-check" class="w-4 h-4"></i> Update Password</button>
        </form>
    </div>
</div>
@endsection
