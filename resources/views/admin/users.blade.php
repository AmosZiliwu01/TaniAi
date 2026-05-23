@extends('layouts.admin')
@section('title','Manajemen Pengguna')
@section('content')
<h1 class="text-2xl sm:text-3xl font-extrabold mb-6">Manajemen Pengguna</h1>

<div class="card p-5">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-xs uppercase tracking-wide text-ink-500 border-b border-ink-200">
                <tr><th class="py-3">Nama</th><th>Email</th><th>Role</th><th>Lokasi</th><th>Daftar</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-ink-200">
                @foreach($users as $u)
                    <tr>
                        <td class="py-3 font-semibold">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge {{ $u->role==='admin'?'bg-purple-100 text-purple-700':'badge-slate' }}">{{ $u->role }}</span></td>
                        <td class="text-ink-500">{{ $u->location ?? '-' }}</td>
                        <td class="text-ink-500">{{ $u->created_at->isoFormat('D MMM Y') }}</td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('admin.users.toggle',$u) }}" class="inline">@csrf
                                <button class="btn-outline text-xs py-1">Toggle Role</button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy',$u) }}" class="inline">@csrf @method('DELETE')
                                <button class="text-red-600 p-1 hover:bg-red-50 rounded-lg"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
