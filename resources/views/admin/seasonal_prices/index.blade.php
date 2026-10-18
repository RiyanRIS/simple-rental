@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ 
    showAddModal: false, 
    showEditModal: false,
    editData: {
        id: '',
        name: '',
        start_date: '',
        end_date: '',
        category_id: '',
        price_type: 'surcharge',
        amount: 0,
        notes: '',
        is_active: 1
    },
    openEdit(item) {
        this.editData = {
            id: item.id,
            name: item.name,
            start_date: item.start_date.substring(0, 10),
            end_date: item.end_date.substring(0, 10),
            category_id: item.category_id || '',
            price_type: item.price_type,
            amount: parseInt(item.amount),
            notes: item.notes || '',
            is_active: item.is_active ? 1 : 0
        };
        this.showEditModal = true;
    }
}">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white p-5 md:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-lg md:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                Tarif Musim Libur & Hari Besar (Peak Season)
            </h1>
            <p class="text-xs text-slate-400 mt-0">Atur lonjakan harga khusus libur panjang, lebaran, nataru, atau event tertentu</p>
        </div>
        <button type="button" @click="showAddModal = true" class="py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Musim Libur</span>
        </button>
    </div>

    <!-- Info Box -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-2xl p-4 text-xs text-purple-950 flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-purple-100 border border-purple-200 flex items-center justify-center text-purple-700 shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="space-y-1">
            <p class="font-bold text-purple-900 text-sm">Bagaimana Cara Kerja Tarif Musim Libur?</p>
            <p class="text-[11px] text-purple-800 leading-relaxed">
                Ketika tamu memilih rentang tanggal sewa yang melewati periode musim libur ini, sistem akan <strong>secara otomatis menghitung tarif tambahan (surcharge)</strong> atau <strong>tarif mutlak</strong> pada setiap hari yang berada di dalam periode tersebut. Tamu juga akan melihat rincian transparan pada kalkulasi biaya sewa.
            </p>
        </div>
    </div>

    <!-- Table Seasonal Prices -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[850px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-3.5 font-bold">Nama Musim Libur / Event</th>
                        <th class="py-3 px-3.5 font-bold">Periode Tanggal</th>
                        <th class="py-3 px-3.5 font-bold">Target Motor</th>
                        <th class="py-3 px-3.5 font-bold">Skema & Penyesuaian Harga</th>
                        <th class="py-3 px-3.5 font-bold">Status</th>
                        <th class="py-3 px-3.5 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($seasonalPrices as $sp)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-3.5 font-bold text-slate-900">
                            <span>{{ $sp->name }}</span>
                            @if($sp->notes)
                            <p class="text-[11px] font-normal text-slate-400 mt-0.5">{{ $sp->notes }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-3.5 whitespace-nowrap">
                            <span class="font-semibold text-slate-800">{{ $sp->start_date->format('d M Y') }}</span>
                            <span class="text-slate-400 text-[10px]"> s/d </span>
                            <span class="font-semibold text-slate-800">{{ $sp->end_date->format('d M Y') }}</span>
                            <span class="block text-[10px] text-slate-400 mt-0.5">
                                ({{ $sp->start_date->diffInDays($sp->end_date) + 1 }} hari)
                            </span>
                        </td>
                        <td class="py-3 px-3.5">
                            @if($sp->category)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                {{ $sp->category->name }}
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Semua Tipe Motor
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-3.5 whitespace-nowrap">
                            @if($sp->price_type === 'surcharge')
                            <span class="font-extrabold text-amber-700 text-xs">
                                + Rp {{ number_format($sp->amount, 0, ',', '.') }} / hari
                            </span>
                            <span class="block text-[10px] text-slate-400">Tambahan dari tarif normal</span>
                            @else
                            <span class="font-extrabold text-purple-700 text-xs">
                                Rp {{ number_format($sp->amount, 0, ',', '.') }} / hari (Fixed)
                            </span>
                            <span class="block text-[10px] text-slate-400">Tarif mutlak</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($sp->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                Nonaktif
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <button type="button" @click="openEdit({{ $sp->toJson() }})" class="p-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-600 transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                <form action="{{ route('admin.seasonal-prices.destroy', $sp->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengaturan musim libur {{ $sp->name }}?')" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 text-slate-600 transition-colors" title="Hapus">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            Belum ada pengaturan musim libur khusus. Klik tombol "Tambah Musim Libur" untuk mendaftarkan libur panjang/lebaran/nataru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Musim Libur -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="showAddModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-sm text-slate-900">Tambah Musim Libur / Peak Season</h3>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-700 text-lg leading-none">&times;</button>
            </div>

            <form action="{{ route('admin.seasonal-prices.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Musim Libur / Event</label>
                    <input type="text" name="name" required placeholder="Contoh: Libur Lebaran Idul Fitri" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Berlaku Untuk Tipe Motor</label>
                    <select name="category_id" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                        <option value="">Semua Tipe Motor (Global)</option>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Skema Harga</label>
                        <select name="price_type" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                            <option value="surcharge">Tambahan (+ Rp / hari)</option>
                            <option value="fixed">Harga Tetap (Fixed Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nominal (Rp)</label>
                        <input type="number" name="amount" required step="5000" placeholder="25000" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Keterangan event/musim liburan..." class="w-full p-2.5 rounded-xl border border-slate-300 text-xs"></textarea>
                </div>

                <div>
                    <label class="inline-flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Aktifkan aturan tarif libur ini sekarang</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-colors shadow-xs">Simpan Musim Libur</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Musim Libur -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="showEditModal = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-sm text-slate-900">Edit Musim Libur / Event</h3>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-700 text-lg leading-none">&times;</button>
            </div>

            <form :action="'{{ url('admin/seasonal-prices') }}/' + editData.id" method="POST" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Musim Libur / Event</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" x-model="editData.start_date" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" x-model="editData.end_date" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Berlaku Untuk Tipe Motor</label>
                    <select name="category_id" x-model="editData.category_id" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                        <option value="">Semua Tipe Motor (Global)</option>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Skema Harga</label>
                        <select name="price_type" x-model="editData.price_type" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                            <option value="surcharge">Tambahan (+ Rp / hari)</option>
                            <option value="fixed">Harga Tetap (Fixed Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nominal (Rp)</label>
                        <input type="number" name="amount" x-model="editData.amount" required step="5000" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" x-model="editData.notes" rows="2" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status Aturan</label>
                    <select name="is_active" x-model="editData.is_active" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                        <option value="1">Aktif Diterapkan</option>
                        <option value="0">Non-aktifkan Sementara</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-colors shadow-xs">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection