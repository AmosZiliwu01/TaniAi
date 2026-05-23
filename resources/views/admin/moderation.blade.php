@extends('layouts.admin')
@section('title','Moderasi Komunitas')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Moderasi Komunitas</h1>
<div class="card p-5 space-y-3">
    @foreach($posts as $p)
        <div class="flex items-start gap-3 p-3 border border-ink-200 rounded-xl">
            <div class="w-9 h-9 rounded-full bg-brand-gradient grid place-items-center text-white font-bold text-sm">{{ substr($p->user->name ?? 'U',0,1) }}</div>
            <div class="flex-1">
                <div class="font-bold">{{ $p->title }}</div>
                <div class="text-xs text-ink-500">{{ $p->user->name ?? '-' }} · {{ $p->created_at->diffForHumans() }}</div>
                <p class="text-sm text-ink-600 mt-1">{{ \Illuminate\Support\Str::limit($p->content,160) }}</p>
            </div>
            <form method="POST" action="{{ route('admin.community.destroy',$p) }}">@csrf @method('DELETE')
                <button class="text-red-600 p-2 rounded-lg hover:bg-red-50"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </form>
        </div>
    @endforeach
    <div>{{ $posts->links() }}</div>
</div>
@endsection
