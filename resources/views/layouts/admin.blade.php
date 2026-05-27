<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Admin') · TaniAI</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100" x-data>

{{-- Single toast --}}
@if(session('status'))
<div x-data="{show:true}" x-show="show" x-cloak
    x-init="setTimeout(()=>show=false,4000)"
    class="fixed bottom-4 right-4 z-[100] flex items-center gap-2 px-4 py-3 rounded-xl bg-brand-600 text-white text-sm font-medium shadow-xl max-w-sm"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 translate-y-2">
    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
    <span>{{ session('status') }}</span>
    <button @click="show=false" class="ml-2 opacity-70 hover:opacity-100"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
</div>
@endif

@if(session('error'))
<div x-data="{show:true}" x-show="show" x-cloak
    x-init="setTimeout(()=>show=false,5000)"
    class="fixed bottom-4 right-4 z-[100] flex items-center gap-2 px-4 py-3 rounded-xl bg-red-600 text-white text-sm font-medium shadow-xl max-w-sm"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 translate-y-2">
    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
    <span>{{ session('error') }}</span>
    <button @click="show=false" class="ml-2 opacity-70 hover:opacity-100"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
</div>
@endif

<div class="flex h-screen overflow-hidden">
    {{-- Admin sidebar (dark, independent scroll) --}}
    @include('partials.admin-sidebar')

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        @include('partials.topbar', ['admin' => true])

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>
</div>
</body>
</html>
