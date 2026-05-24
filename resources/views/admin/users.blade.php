@extends('layouts.admin')
@section('title','Manajemen Pengguna')
@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Manajemen Pengguna</h1>
        <p class="text-ink-500 mt-1">{{ $users->total() }} pengguna terdaftar.</p>
    </div>
</div>

@if(session('status'))
    <div class="mb-4 p-3 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 text-sm flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('status') }}
    </div>
@endif
@if($errors->any())
    <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 border border-red-200 text-sm">{{ $errors->first() }}</div>
@endif

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-ink-50 text-left text-xs uppercase tracking-wide text-ink-500 border-b border-ink-200">
                <tr>
                    <th class="py-3 px-4">Pengguna</th>
                    <th class="px-4">Role</th>
                    <th class="px-4">Lokasi</th>
                    <th class="px-4 text-center">Diagnosa</th>
                    <th class="px-4 text-center">Postingan</th>
                    <th class="px-4">Daftar</th>
                    <th class="px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-200">
                @foreach($users as $u)
                    @php
                        $isBanned = str_starts_with($u->role, 'banned');
                        $roleDisplay = $isBanned ? 'Diblokir' : ucfirst($u->role);
                        $roleBadge   = $u->role === 'admin' ? 'bg-purple-100 text-purple-700'
                                     : ($isBanned ? 'bg-red-100 text-red-700' : 'badge-slate');
                    @endphp
                    <tr class="hover:bg-ink-50 {{ $isBanned ? 'opacity-60' : '' }}">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-brand-gradient grid place-items-center text-white font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold">{{ $u->name }}</div>
                                    <div class="text-xs text-ink-500">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4">
                            <span class="badge {{ $roleBadge }}">{{ $roleDisplay }}</span>
                        </td>
                        <td class="px-4 text-ink-500 text-xs">{{ $u->location ?? '-' }}</td>
                        <td class="px-4 text-center font-semibold">{{ $u->diagnoses_count ?? 0 }}</td>
                        <td class="px-4 text-center font-semibold">{{ $u->posts_count ?? 0 }}</td>
                        <td class="px-4 text-ink-500 text-xs">{{ $u->created_at->isoFormat('D MMM Y') }}</td>
                        <td class="px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Toggle Role (not for self) --}}
                                @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle', $u) }}" class="inline">@csrf
                                        <button title="{{ $u->role === 'admin' ? 'Jadikan User' : 'Jadikan Admin' }}"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-ink-200 hover:bg-ink-50 transition">
                                            {{ $u->role === 'admin' ? 'Set User' : 'Set Admin' }}
                                        </button>
                                    </form>
                                    {{-- Ban/Unban --}}
                                    @if(!$u->isAdmin())
                                        <form method="POST" action="{{ route('admin.users.ban', $u) }}" class="inline">@csrf
                                            <button title="{{ $isBanned ? 'Cabut blokir' : 'Blokir pengguna' }}"
                                                class="w-8 h-8 rounded-lg grid place-items-center transition {{ $isBanned ? 'bg-brand-50 text-brand-600 hover:bg-brand-100' : 'bg-amber-50 text-amber-600 hover:bg-amber-100' }}">
                                                <i data-lucide="{{ $isBanned ? 'user-check' : 'user-x' }}" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline">@csrf @method('DELETE')
                                        <button onclick="return confirm('Hapus pengguna {{ $u->name }}? Tindakan tidak dapat dibatalkan.')"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 grid place-items-center transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-ink-400 italic">Akun aktif</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-ink-200">{{ $users->links() }}</div>
</div>
@endsection
