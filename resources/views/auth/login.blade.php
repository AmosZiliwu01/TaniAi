@extends('layouts.guest')
@section('title','Masuk')
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
            
<a href="/" class="lg:hidden flex items-center gap-3 mb-8">
    <div class="w-10 h-10 rounded-xl bg-brand-gradient grid place-items-center text-white"><i data-lucide="sprout" class="w-5 h-5"></i></div>
    <div class="font-extrabold">TaniAI</div>
</a>
<h1 class="text-3xl font-extrabold">Masuk ke TaniAI</h1>
<p class="text-ink-500 mt-1 text-sm">Selamat datang kembali, mari panen lebih cerdas.</p>

<form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">@csrf
    <div>
        <label class="label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required class="input" placeholder="nama@email.com">
    </div>
    <div>
        <label class="label">Password</label>
        <input type="password" name="password" required class="input" placeholder="••••••••">
    </div>
    <div class="flex items-center justify-between text-sm">
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="remember" class="rounded text-brand-600"> Ingat saya</label>
        <a href="{{ route('password.request') }}" class="text-brand-700 font-semibold">Lupa password?</a>
    </div>
    @if($errors->any())<div class="text-sm text-red-600">{{ $errors->first() }}</div>@endif
    <button class="btn-primary w-full py-3">Masuk</button>
</form>
<p class="mt-6 text-sm text-center text-ink-500">Belum punya akun? <a href="{{ route('register') }}" class="text-brand-700 font-semibold">Daftar gratis</a></p>
<div class="mt-6 p-3 rounded-xl bg-brand-50 text-xs text-brand-800">
    <b>Demo admin:</b> admin@taniai.local / password
</div>

        </div>
    </div>
</div>
@endsection
