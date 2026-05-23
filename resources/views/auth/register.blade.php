@extends('layouts.guest')
@section('title','Daftar')
@section('content')
<div class="min-h-screen grid lg:grid-cols-2">
    <div class="hidden lg:flex flex-col justify-between p-12 bg-brand-gradient text-white relative overflow-hidden">
        <div>
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 grid place-items-center backdrop-blur"><i data-lucide="sprout" class="w-5 h-5"></i></div>
                <div class="font-extrabold">TaniAI</div>
            </a>
        </div>
        <div class="relative z-10">
            <h2 class="text-4xl font-extrabold leading-tight">Pertanian cerdas dimulai dari sini.</h2>
            <p class="mt-3 text-white/90">Diagnosa AI, cuaca, harga pasar — semua dalam satu platform.</p>
        </div>
        <div class="text-xs text-white/70">© {{ date('Y') }} TaniAI</div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
    </div>
    <div class="flex items-center justify-center p-6 sm:p-12 bg-white">
        <div class="w-full max-w-md">
            
<h1 class="text-3xl font-extrabold">Buat akun baru</h1>
<p class="text-ink-500 mt-1 text-sm">Gratis, hanya butuh 1 menit.</p>
<form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">@csrf
    <div><label class="label">Nama lengkap</label><input name="name" required class="input" value="{{ old('name') }}"></div>
    <div><label class="label">Email</label><input type="email" name="email" required class="input" value="{{ old('email') }}"></div>
    <div><label class="label">Lokasi (opsional)</label><input name="location" class="input" placeholder="Kab/Kota, Provinsi" value="{{ old('location') }}"></div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="label">Password</label><input type="password" name="password" required class="input"></div>
        <div><label class="label">Konfirmasi</label><input type="password" name="password_confirmation" required class="input"></div>
    </div>
    @if($errors->any())<div class="text-sm text-red-600">{{ $errors->first() }}</div>@endif
    <button class="btn-primary w-full py-3">Daftar Sekarang</button>
</form>
<p class="mt-6 text-sm text-center text-ink-500">Sudah punya akun? <a href="{{ route('login') }}" class="text-brand-700 font-semibold">Masuk</a></p>

        </div>
    </div>
</div>
@endsection
