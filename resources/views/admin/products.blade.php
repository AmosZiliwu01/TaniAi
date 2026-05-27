@extends('layouts.admin')
@section('title','Produk & Rekomendasi')
@section('content')

<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Produk & Rekomendasi</h1>
        <p class="text-ink-500 mt-1">Kelola produk yang ditampilkan ke pengguna.</p>
    </div>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Produk
    </button>
</div>

{{-- Add Modal --}}
<div id="addModal" class="hidden fixed inset-0 bg-black/50 z-50 grid place-items-center p-4">
    <div class="card p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="font-bold">Tambah Produk</div>
            <button onclick="document.getElementById('addModal').classList.add('hidden')">
                <i data-lucide="x" class="w-5 h-5 text-ink-400"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.products.store') }}" class="space-y-3">@csrf
            <div>
                <label class="label">Nama Produk</label>
                <input name="title" required class="input" placeholder="Contoh: Pupuk Urea 50kg">
            </div>
            <div>
                <label class="label">Deskripsi</label>
                <textarea name="content" required rows="2" class="input" placeholder="Deskripsi singkat produk..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Harga (Rp)</label>
                    <input name="price" type="number" min="0" class="input" placeholder="150000">
                </div>
                <div>
                    <label class="label">Kategori</label>
                    <select name="category" class="input">
                        <option>Pupuk</option><option>Pestisida</option><option>Benih</option>
                        <option>Alat Tani</option><option>Lainnya</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">Tipe Beli</label>
                    <select name="buy_type" class="input" id="addBuyType" onchange="toggleTarget('add')">
                        <option value="wa">WhatsApp</option>
                        <option value="shopee">Shopee</option>
                        <option value="tokopedia">Tokopedia</option>
                    </select>
                </div>
                <div>
                    <label class="label" id="addBuyLabel">Nomor WhatsApp</label>
                    <input name="buy_target" id="addBuyTarget" class="input" placeholder="628xxxxxxxxxx">
                </div>
            </div>
            <div>
                <label class="label">Prioritas</label>
                <select name="priority" class="input">
                    <option value="low">Normal</option>
                    <option value="medium">Sedang</option>
                    <option value="high">Tinggi</option>
                </select>
            </div>
            <div class="flex gap-2 justify-end pt-2">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="btn-outline text-sm">Batal</button>
                <button class="btn-primary text-sm"><i data-lucide="save" class="w-4 h-4"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Products table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-ink-50 text-xs uppercase tracking-wide text-ink-500 border-b border-ink-200">
                <tr>
                    <th class="py-3 px-4 text-left">Produk</th>
                    <th class="px-4 text-left">Kategori</th>
                    <th class="px-4 text-left">Harga</th>
                    <th class="px-4 text-left">Tipe Beli</th>
                    <th class="px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-200">
                @forelse($products as $prod)
                    <tr class="hover:bg-ink-50" x-data="{editOpen:false}">
                        <td class="py-3 px-4">
                            <div class="font-semibold">{{ $prod->title }}</div>
                            <div class="text-xs text-ink-500 mt-0.5 max-w-xs truncate">{{ $prod->content }}</div>
                        </td>
                        <td class="px-4 text-ink-600">{{ $prod->category ?? '-' }}</td>
                        <td class="px-4 font-bold">
                            {{ $prod->price ? 'Rp '.number_format($prod->price,0,',','.') : '-' }}
                        </td>
                        <td class="px-4">
                            <span class="badge {{ $prod->buy_type==='wa' ? 'bg-green-100 text-green-700' : 'badge-slate' }}">
                                {{ strtoupper($prod->buy_type ?? 'wa') }}
                            </span>
                        </td>
                        <td class="px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button @click="editOpen=true" class="w-8 h-8 rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100 grid place-items-center transition">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.products.destroy', $prod) }}">@csrf @method('DELETE')
                                    <button onclick="return confirm('Hapus produk ini?')" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 grid place-items-center transition">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>

                        {{-- Edit Modal --}}
                        <td colspan="0">
                        <div x-show="editOpen" x-cloak class="fixed inset-0 bg-black/50 z-50 grid place-items-center p-4">
                            <div @click.outside="editOpen=false" class="card p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="font-bold">Edit Produk</div>
                                    <button @click="editOpen=false"><i data-lucide="x" class="w-5 h-5 text-ink-400"></i></button>
                                </div>
                                <form method="POST" action="{{ route('admin.products.update', $prod) }}" class="space-y-3">@csrf @method('PATCH')
                                    <div>
                                        <label class="label">Nama Produk</label>
                                        <input name="title" required class="input" value="{{ $prod->title }}">
                                    </div>
                                    <div>
                                        <label class="label">Deskripsi</label>
                                        <textarea name="content" required rows="2" class="input">{{ $prod->content }}</textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="label">Harga (Rp)</label>
                                            <input name="price" type="number" min="0" class="input" value="{{ $prod->price }}">
                                        </div>
                                        <div>
                                            <label class="label">Kategori</label>
                                            <select name="category" class="input">
                                                @foreach(['Pupuk','Pestisida','Benih','Alat Tani','Lainnya'] as $cat)
                                                    <option {{ $prod->category===$cat?'selected':'' }}>{{ $cat }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="label">Tipe Beli</label>
                                            <select name="buy_type" class="input">
                                                @foreach(['wa'=>'WhatsApp','shopee'=>'Shopee','tokopedia'=>'Tokopedia'] as $k=>$v)
                                                    <option value="{{ $k }}" {{ $prod->buy_type===$k?'selected':'' }}>{{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="label">Target (WA/URL)</label>
                                            <input name="buy_target" class="input" value="{{ $prod->buy_target }}">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="label">Prioritas</label>
                                        <select name="priority" class="input">
                                            @foreach(['low'=>'Normal','medium'=>'Sedang','high'=>'Tinggi'] as $k=>$v)
                                                <option value="{{ $k }}" {{ $prod->priority===$k?'selected':'' }}>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex gap-2 justify-end pt-2">
                                        <button type="button" @click="editOpen=false" class="btn-outline text-sm">Batal</button>
                                        <button class="btn-primary text-sm"><i data-lucide="save" class="w-4 h-4"></i> Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-ink-500">
                            Belum ada produk. Klik "Tambah Produk" untuk mulai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-ink-200">{{ $products->links() }}</div>
</div>

<script>
function toggleTarget(prefix) {
    const type  = document.getElementById(prefix+'BuyType').value;
    const label = document.getElementById(prefix+'BuyLabel');
    const input = document.getElementById(prefix+'BuyTarget');
    if (type === 'wa') {
        label.textContent = 'Nomor WhatsApp'; input.placeholder = '628xxxxxxxxxx';
    } else {
        label.textContent = 'URL Toko'; input.placeholder = 'https://shopee.co.id/...';
    }
}
</script>
@endsection
