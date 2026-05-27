<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Dashboard') · TaniAI</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50" x-data>

{{-- Single global toast --}}
@if(session('status'))
<div x-data="{show:true}" x-show="show" x-cloak
    x-init="setTimeout(()=>show=false,4000)"
    class="fixed bottom-20 lg:bottom-4 right-4 z-[100] flex items-center gap-2 px-4 py-3 rounded-xl bg-brand-600 text-white text-sm font-medium shadow-xl max-w-sm"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-end="opacity-0 translate-y-2">
    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
    <span>{{ session('status') }}</span>
    <button @click="show=false" class="ml-2 opacity-70 hover:opacity-100 shrink-0">
        <i data-lucide="x" class="w-3.5 h-3.5"></i>
    </button>
</div>
@endif

@if($errors->any() && !$errors->has('image'))
<div x-data="{show:true}" x-show="show" x-cloak
    x-init="setTimeout(()=>show=false,6000)"
    class="fixed bottom-20 lg:bottom-4 right-4 z-[100] flex items-center gap-2 px-4 py-3 rounded-xl bg-red-600 text-white text-sm font-medium shadow-xl max-w-sm"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-end="opacity-0 translate-y-2">
    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
    <span>{{ $errors->first() }}</span>
    <button @click="show=false" class="ml-2 opacity-70 hover:opacity-100 shrink-0">
        <i data-lucide="x" class="w-3.5 h-3.5"></i>
    </button>
</div>
@endif

<div class="flex h-screen overflow-hidden">
    {{-- Sidebar: fixed height, independent scroll --}}
    @include('partials.sidebar')

    {{-- Main area --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        @include('partials.topbar')

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 pb-20 lg:pb-8">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>

        {{-- Mobile bottom navigation bar --}}
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-20 flex items-center justify-around border-t border-ink-200 bg-white/95 backdrop-blur-sm py-2 px-2">
            @php
            $mobileLinks = [
                [route('dashboard'),          'home',             'Beranda'],
                [route('diagnosis.index'),     'scan-line',        'Diagnosa'],
                [route('chat.index'),          'message-circle',   'Tanya AI'],
                [route('community.index'),     'users',            'Komunitas'],
                [route('profile.settings'),    'settings',         'Pengaturan'],
            ];
            @endphp
            @foreach($mobileLinks as [$href, $icon, $label])
                @php
                    $isActive = request()->url() === $href
                        || str_starts_with(request()->url(), rtrim($href,'/').'/')
                        || request()->routeIs(str_replace(url('/'),'',$href).'*');
                @endphp
                <a href="{{ $href }}"
                    class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition {{ $isActive ? 'text-brand-600' : 'text-ink-500' }}">
                    <i data-lucide="{{ $icon }}" class="w-5 h-5 {{ $isActive ? 'stroke-[2.5]' : '' }}"></i>
                    <span class="text-[10px] font-{{ $isActive ? 'bold' : 'semibold' }}">{{ $label }}</span>
                </a>
            @endforeach
        </nav>
    </div>
</div>

</body>
</html>
