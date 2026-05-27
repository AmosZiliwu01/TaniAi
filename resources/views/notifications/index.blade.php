@extends('layouts.app')
@section('title','Notifikasi')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Notifikasi</h1>
        <p class="text-ink-500 mt-0.5 text-sm">Pembaruan aktivitas akun Anda.</p>
    </div>
    @if($items->where('read_at',null)->count() > 0)
        <form method="POST" action="{{ route('notifications.read') }}">@csrf
            <button class="btn-outline text-sm">
                <i data-lucide="check-check" class="w-4 h-4"></i> Tandai semua dibaca
            </button>
        </form>
    @endif
</div>

<div class="card divide-y divide-ink-100">
    @forelse($items as $n)
        @php
            $typeMap = [
                'community'  => ['message-circle','brand'],
                'warning'    => ['alert-triangle','amber'],
                'moderation' => ['shield','red'],
                'system'     => ['bell','blue'],
                'success'    => ['check-circle','green'],
            ];
            [$ico,$col] = $typeMap[$n->type ?? 'system'] ?? ['bell','blue'];
        @endphp
        <div class="flex items-start gap-4 p-4 hover:bg-ink-50 transition {{ !$n->read_at ? 'bg-brand-50/30' : '' }}">
            <div class="w-10 h-10 rounded-xl bg-{{ $col }}-100 text-{{ $col }}-600 grid place-items-center shrink-0 mt-0.5">
                <i data-lucide="{{ $ico }}" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <div class="font-semibold text-sm">{{ $n->title }}</div>
                    @if(!$n->read_at)
                        <span class="w-2 h-2 rounded-full bg-brand-500 shrink-0"></span>
                    @endif
                </div>
                <p class="text-sm text-ink-600 mt-0.5 leading-relaxed">{{ $n->message }}</p>
                <div class="text-xs text-ink-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
            </div>
        </div>
    @empty
        <div class="py-16 text-center text-ink-500">
            <i data-lucide="bell-off" class="w-12 h-12 mx-auto mb-3 text-ink-300"></i>
            <div class="font-semibold">Belum ada notifikasi</div>
            <p class="text-sm mt-1">Notifikasi muncul saat ada aktivitas pada akun Anda.</p>
        </div>
    @endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
