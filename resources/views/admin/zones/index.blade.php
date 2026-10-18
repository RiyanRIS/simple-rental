@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ 
    showAddModal: false, 
    showEditModal: false,
    editData: { id: '', name: '', fee: 0, description: '', is_active: 1 }
}">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white p-5 md:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-lg md:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">Zona Antar-Jemput & Tarif Ongkir</h1>
            <p class="text-xs text-slate-400 mt-0">Atur titik kumpul gratis (Stasiun Tugu / Lempuyangan) dan ongkir fixed per zona Jogja</p>
        </div>
        <button type="button" @click="showAddModal = true" class="py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Zona Baru</span>
        </button>
    </div>

    <!-- Table Zona -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[800px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-3.5 font-bold w-64 min-w-[200px]">Nama Zona / Titik Lokasi</th>
                        <th class="py-3 px-3.5 font-bold w-40 whitespace-nowrap">Biaya Ongkir (Fixed)</th>
                        <th class="py-3 px-3.5 font-bold min-w-[200px] max-w-sm">Keterangan</th>
                        <th class="py-3 px-3.5 font-bold w-28 whitespace-nowrap">Status</th>
                        <th class="py-3 px-3.5 font-bold w-24 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($zones as $z)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-2.5 px-3.5 font-bold text-slate-900">
                                <span class="block truncate max-w-[240px]" title="{{ $z->name }}">{{ $z->name }}</span>
                            </td>
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                @if($z->fee == 0 || $z->is_free)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-block whitespace-nowrap">
                                        GRATIS (Rp 0)
                                    </span>
                                @else
                                    <span class="font-extrabold text-slate-800 whitespace-nowrap">
                                        Rp {{ number_format($z->fee, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3.5 text-slate-500 max-w-sm truncate" title="{{ $z->description ?? '-' }}">{{ $z->description ?? '-' }}</td>
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                @if($z->is_active)
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
                            <td class="py-2.5 px-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                        @click="editData = {
                                            id: {{ $z->id }},
                                            name: '{{ addslashes($z->name) }}',
                                            fee: {{ (int)$z->fee }},
                                            description: '{{ addslashes($z->description ?? '') }}',
                                            is_active: {{ $z->is_active ? 1 : 0 }}
                                        }; showEditModal = true;" 
                                        title="Edit Zona Ongkir" 
                                        class="p-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">Belum ada zona antar-jemput.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Edit Zona -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="showEditModal = false" class="bg-white rounded-2xl max-w-lg w-full shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
            <!-- Header (Static) -->
            <div class="flex items-center justify-between border-b border-slate-100 p-5 pb-4 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900">Edit Zona Antar-Jemput</h3>
                        <p class="text-[11px] text-slate-400" x-text="editData.name"></p>
                    </div>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1 leading-none text-lg">&times;</button>
            </div>

            <!-- Form with Scrollable Body & Static Footer -->
            <form :action="'{{ url('admin/zones') }}/' + editData.id" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                @method('PUT')
                <div class="p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lokasi / Zona <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editData.name" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tarif Ongkir Fixed (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="fee" x-model="editData.fee" step="1000" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white">
                        <span class="text-[10px] text-slate-400">Masukkan 0 jika gratis ongkir di zona ini.</span>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Zona <span class="text-rose-500">*</span></label>
                        <select name="is_active" x-model="editData.is_active" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Keterangan Wilayah / Titik Temu</label>
                        <textarea name="description" x-model="editData.description" rows="2" placeholder="Cakupan area pengantaran atau instruksi titik temu..." class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white"></textarea>
                    </div>
                </div>

                <!-- Footer (Static) -->
                <div class="flex items-center justify-end gap-2 p-4 px-5 border-t border-slate-100 bg-slate-50/50 shrink-0">
                    <button type="button" @click="showEditModal = false" class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 px-5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md shadow-indigo-600/20">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Zona -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="showAddModal = false" class="bg-white rounded-2xl max-w-lg w-full shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
            <!-- Header (Static) -->
            <div class="flex items-center justify-between border-b border-slate-100 p-5 pb-4 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900">Tambah Zona Antar-Jemput</h3>
                        <p class="text-[11px] text-slate-400">Atur wilayah pengantaran dan biaya ongkir tetap</p>
                    </div>
                </div>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1 leading-none text-lg">&times;</button>
            </div>

            <!-- Form with Scrollable Body & Static Footer -->
            <form action="{{ route('admin.zones.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                <div class="p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lokasi / Zona <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Bandara YIA / Stasiun Tugu / Kaliurang" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tarif Ongkir Fixed (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="fee" value="0" step="1000" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                        <span class="text-[10px] text-slate-400">Masukkan 0 jika gratis ongkir di zona ini.</span>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Keterangan Wilayah / Titik Temu</label>
                        <textarea name="description" rows="2" placeholder="Cakupan area pengantaran atau instruksi titik temu..." class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white"></textarea>
                    </div>
                </div>

                <!-- Footer (Static) -->
                <div class="flex items-center justify-end gap-2 p-4 px-5 border-t border-slate-100 bg-slate-50/50 shrink-0">
                    <button type="button" @click="showAddModal = false" class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-600/20">
                        Simpan Zona
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
