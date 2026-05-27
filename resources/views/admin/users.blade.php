@extends('layouts.admin')
@section('title','Manajemen Pengguna')
@section('content')

<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Manajemen Pengguna</h1>
        <p class="text-ink-500 mt-1">{{ $users->total() }} pengguna terdaftar.</p>
    </div>
</div>

<form method="GET" class="mb-4 flex gap-2">
    <div class="flex items-center gap-2 bg-white border border-ink-200 rounded-xl px-3 py-2 flex-1 max-w-sm">
        <i data-lucide="search" class="w-4 h-4 text-ink-400 shrink-0"></i>
        <input name="search" type="text" class="bg-transparent text-sm flex-1 outline-none"
            placeholder="Cari nama atau email..." value="{{ request('search') }}">
    </div>
    <button class="btn-primary text-sm py-2">Cari</button>
    @if(request('search'))
        <a href="{{ route('admin.users') }}" class="btn-outline text-sm py-2">✕ Reset</a>
    @endif
</form>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-ink-500 border-b border-ink-200">
                <tr>
                    <th class="py-3 px-4">Pengguna</th>
                    <th class="px-4">Role</th>
                    <th class="px-4">Lokasi</th>
                    <th class="px-4 text-center">Diagnosa</th>
                    <th class="px-4 text-center">Post</th>
                    <th class="px-4">Daftar</th>
                    <th class="px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-200">
                @foreach($users as $u)
                    @php
                        $grads = ['from-violet-500 to-purple-600','from-blue-500 to-cyan-600','from-emerald-500 to-teal-600','from-rose-500 to-pink-600','from-amber-500 to-orange-600'];
                        $gi = abs(crc32($u->name)) % 5;
                        $isBanned = $u->role === 'banned';
                        $roleBadge = $u->role==='admin' ? 'bg-purple-100 text-purple-700'
                                   : ($isBanned ? 'bg-red-100 text-red-700' : 'badge-slate');
                    @endphp
                    <tr class="hover:bg-ink-50 {{ $isBanned ? 'opacity-60' : '' }}">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $grads[$gi] }} grid place-items-center text-white font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($u->name,0,1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold truncate max-w-[150px]">{{ $u->name }}</div>
                                    <div class="text-xs text-ink-400 truncate max-w-[150px]">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4">
                            <span class="badge text-xs {{ $roleBadge }}">
                                {{ $isBanned ? 'Diblokir' : ucfirst($u->role) }}
                            </span>
                        </td>
                        <td class="px-4 text-xs text-ink-500">{{ $u->location ?? '—' }}</td>
                        <td class="px-4 text-center font-semibold">{{ $u->diagnoses_count ?? 0 }}</td>
                        <td class="px-4 text-center font-semibold">{{ $u->posts_count ?? 0 }}</td>
                        <td class="px-4 text-xs text-ink-400">{{ $u->created_at->isoFormat('D MMM Y') }}</td>
                        <td class="px-4 text-right">
                            @if($u->id !== auth()->id())
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Toggle admin --}}
                                <form method="POST" action="{{ route('admin.users.toggle', $u) }}" class="inline">@csrf
                                    <button title="{{ $u->role==='admin'?'→ User':'→ Admin' }}"
                                        class="px-2 py-1 rounded-lg text-xs font-semibold border border-ink-200 hover:bg-ink-50 transition whitespace-nowrap">
                                        {{ $u->role==='admin'?'→ User':'→ Admin' }}
                                    </button>
                                </form>
                                {{-- Ban --}}
                                @if($u->role !== 'admin')
                                    <form method="POST" action="{{ route('admin.users.ban', $u) }}" class="inline">@csrf
                                        <button title="{{ $isBanned?'Cabut blokir':'Blokir' }}"
                                            class="w-8 h-8 rounded-lg grid place-items-center transition
                                                {{ $isBanned?'bg-brand-50 text-brand-600 hover:bg-brand-100':'bg-amber-50 text-amber-600 hover:bg-amber-100' }}">
                                            <i data-lucide="{{ $isBanned?'user-check':'user-x' }}" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                                {{-- Delete --}}
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline">@csrf @method('DELETE')
                                    <button onclick="return confirm('Hapus pengguna \'{{ addslashes($u->name) }}\'?')"
                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 grid place-items-center transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                            @else
                                <span class="text-xs text-ink-400 italic px-2">Anda</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-ink-200">{{ $users->links() }}</div>
</div>
@endsection
