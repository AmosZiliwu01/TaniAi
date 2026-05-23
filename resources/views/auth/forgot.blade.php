@extends('layouts.guest')
@section('title','Lupa Password')
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
            
<h1 class="text-3xl font-extrabold">Lupa password?</h1>
<p class="text-ink-500 mt-1 text-sm">Masukkan email Anda, kami kirim link untuk reset.</p>
<form method="POST" action="{{ route('password.request') }}" class="mt-8 space-y-4">@csrf
    <div><label class="label">Email</label><input type="email" name="email" required class="input"></div>
    @if(session('status'))<div class="text-sm text-brand-700 bg-brand-50 p-3 rounded-xl">{{ session('status') }}</div>@endif
    <button class="btn-primary w-full py-3">Kirim Link Reset</button>
</form>
<p class="mt-6 text-sm text-center text-ink-500"><a href="{{ route('login') }}" class="text-brand-700 font-semibold">Kembali ke Masuk</a></p>

        </div>
    </div>
</div>
@endsection
