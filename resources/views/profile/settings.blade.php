@extends('layouts.app')
@section('title','Pengaturan')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Pengaturan</h1>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="card p-6">
        <div class="font-bold mb-4">Informasi Profil</div>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-3">@csrf @method('PATCH')
            <div><label class="label">Nama</label><input name="name" required class="input" value="{{ auth()->user()->name }}"></div>
            <div><label class="label">Email</label><input type="email" name="email" required class="input" value="{{ auth()->user()->email }}"></div>
            <div><label class="label">Lokasi</label><input name="location" class="input" value="{{ auth()->user()->location }}"></div>
            <div><label class="label">No. Telepon</label><input name="phone" class="input" value="{{ auth()->user()->phone }}"></div>
            <div><label class="label">Jenis Petani</label><input name="farmer_type" class="input" value="{{ auth()->user()->farmer_type }}"></div>
            <button class="btn-primary">Simpan</button>
        </form>
    </div>

    <div class="space-y-6">
        <div class="card p-6">
            <div class="font-bold mb-4">Ubah Password</div>
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-3">@csrf @method('PATCH')
                <div><label class="label">Password Lama</label><input type="password" name="current_password" required class="input"></div>
                <div><label class="label">Password Baru</label><input type="password" name="password" required class="input"></div>
                <div><label class="label">Konfirmasi</label><input type="password" name="password_confirmation" required class="input"></div>
                @if($errors->any())<div class="text-sm text-red-600">{{ $errors->first() }}</div>@endif
                <button class="btn-primary">Update Password</button>
            </form>
        </div>

        <div class="card p-6" x-data>
            <div class="font-bold mb-4">Preferensi</div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-sm">Mode Gelap</div>
                        <div class="text-xs text-ink-500">Tampilan dengan warna gelap.</div>
                    </div>
                    <button @click="$store.ui.toggleDark()" :class="$store.ui.dark ? 'bg-brand-600' : 'bg-ink-200'" class="w-12 h-7 rounded-full relative transition">
                        <span :class="$store.ui.dark ? 'translate-x-5' : 'translate-x-0.5'" class="absolute top-0.5 left-0 w-6 h-6 bg-white rounded-full shadow transition"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-sm">Notifikasi Email</div>
                        <div class="text-xs text-ink-500">Terima ringkasan mingguan.</div>
                    </div>
                    <input type="checkbox" class="toggle text-brand-600" checked>
                </div>
                <div>
                    <div class="font-semibold text-sm">Bahasa</div>
                    <select class="input mt-2"><option>Bahasa Indonesia</option><option>English</option></select>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
