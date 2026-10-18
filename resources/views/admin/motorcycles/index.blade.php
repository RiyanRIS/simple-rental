@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ 
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    editData: {
        id: '',
        category_id: '',
        plate_number: '',
        color: '',
        year: '',
        default_helmets_count: 2,
        default_raincoats_count: 1,
        default_key_and_stnk: 1,
        status: 'available',
        notes: ''
    },
    deleteData: {
        id: '',
        plate_number: '',
        category_name: '',
        status: ''
    },
    openEdit(m) {
        this.editData = {
            id: m.id,
            category_id: m.category_id,
            plate_number: m.plate_number,
            color: m.color || '',
            year: m.year || '',
            default_helmets_count: m.default_helmets_count !== undefined && m.default_helmets_count !== null ? m.default_helmets_count : 2,
            default_raincoats_count: m.default_raincoats_count !== undefined && m.default_raincoats_count !== null ? m.default_raincoats_count : 1,
            default_key_and_stnk: (m.default_key_and_stnk !== undefined && m.default_key_and_stnk !== null) ? (m.default_key_and_stnk ? 1 : 0) : 1,
            status: m.status,
            notes: m.notes || ''
        };
        this.showEditModal = true;
    },
    openDelete(m) {
        this.deleteData = {
            id: m.id,
            plate_number: m.plate_number,
            category_name: m.category ? m.category.name : '',
            status: m.status
        };
        this.showDeleteModal = true;
    }
}">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white p-5 md:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-lg md:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">Manajemen Unit Motor Fisik</h1>
            <p class="text-xs text-slate-400 mt-0">Kelola nomor plat motor, unit di garasi, pemeliharaan servis, dan data armada</p>
        </div>
        <button type="button" @click="showAddModal = true" class="py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Motor Baru</span>
        </button>
    </div>

    <!-- Table Unit Motor -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[900px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px]">
                    <tr>
                        <th class="py-3 px-3.5 font-bold w-36 whitespace-nowrap">Plat Nomor</th>
                        <th class="py-3 px-3.5 font-bold w-56 min-w-[200px]">Kategori & Merk</th>
                        <th class="py-3 px-3.5 font-bold w-32 whitespace-nowrap">Warna / Tahun</th>
                        <th class="py-3 px-3.5 font-bold w-48 whitespace-nowrap">Status Unit</th>
                        <th class="py-3 px-3.5 font-bold min-w-[150px]">Catatan</th>
                        <th class="py-3 px-3.5 font-bold w-48 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($motorcycles as $m)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                <span class="font-extrabold text-xs text-slate-900 bg-slate-100 px-2 py-1 rounded-md border border-slate-200 inline-block whitespace-nowrap">
                                    {{ $m->plate_number }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5">
                                <span class="font-semibold text-slate-800 block truncate max-w-[200px]" title="{{ $m->category->name }}">{{ $m->category->name }}</span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                                    {{ $m->category->brand ?? 'Honda' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                <span>{{ $m->color ?? '-' }}</span>
                                <span class="text-slate-400 block text-[11px]">Thn {{ $m->year ?? '-' }}</span>
                            </td>
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                @if($m->status === 'available')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ● Tersedia di Garasi
                                    </span>
                                @elseif($m->status === 'rented')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        ● Sedang Disewa Tamu
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        ● Bengkel / Servis (Non-stok)
                                    </span>
                                @endif
                                <div class="mt-1">
                                    <span class="text-[10px] text-slate-500 block font-medium">
                                        Bawaan: {{ $m->default_helmets_count }} Helm • {{ $m->default_raincoats_count }} Jas Hujan
                                    </span>
                                    <span class="text-[10px] {{ $m->default_key_and_stnk ? 'text-emerald-700' : 'text-amber-700' }} font-bold block">
                                        {{ $m->default_key_and_stnk ? '✓ Kunci & STNK' : '⚠ Tanpa Kunci/STNK' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3.5 text-slate-500 max-w-xs truncate" title="{{ $m->notes ?? '-' }}">{{ $m->notes ?? '-' }}</td>
                            <td class="py-2.5 px-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <!-- Quick Status Select -->
                                    <form action="{{ route('admin.motorcycles.update', $m->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="category_id" value="{{ $m->category_id }}">
                                        <input type="hidden" name="plate_number" value="{{ $m->plate_number }}">
                                        <input type="hidden" name="color" value="{{ $m->color }}">
                                        <input type="hidden" name="year" value="{{ $m->year }}">
                                        <input type="hidden" name="default_helmets_count" value="{{ $m->default_helmets_count }}">
                                        <input type="hidden" name="default_raincoats_count" value="{{ $m->default_raincoats_count }}">
                                        <input type="hidden" name="default_key_and_stnk" value="{{ $m->default_key_and_stnk ? 1 : 0 }}">
                                        <input type="hidden" name="notes" value="{{ $m->notes }}">
                                        
                                        <select name="status" onchange="this.form.submit()" title="Ubah status cepat" class="text-[11px] py-1 px-1.5 rounded-lg border border-slate-300 bg-white font-medium focus:ring-1 focus:ring-emerald-500">
                                            <option value="available" {{ $m->status === 'available' ? 'selected' : '' }}>Tersedia</option>
                                            <option value="rented" {{ $m->status === 'rented' ? 'selected' : '' }}>Disewa</option>
                                            <option value="maintenance" {{ $m->status === 'maintenance' ? 'selected' : '' }}>Servis</option>
                                        </select>
                                    </form>

                                    <!-- Tombol Edit Lengkap -->
                                    <button type="button" @click="openEdit({{ $m->toJson() }})" title="Edit Data Motor" class="p-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <button type="button" @click="openDelete({{ $m->toJson() }})" title="Hapus Unit Motor" class="p-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 text-slate-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">Belum ada data motor fisik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Unit Motor -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="showAddModal = false" class="bg-white rounded-2xl max-w-lg w-full shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
            <!-- Header (Static) -->
            <div class="flex items-center justify-between border-b border-slate-100 p-5 pb-4 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900">Tambah Unit Motor Baru</h3>
                        <p class="text-[11px] text-slate-400">Daftarkan nomor plat dan fisik motor ke garasi armada</p>
                    </div>
                </div>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1 text-lg leading-none">&times;</button>
            </div>

            <!-- Form with Scrollable Body & Static Footer -->
            <form action="{{ route('admin.motorcycles.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                <div class="p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kategori Tipe Motor <span class="text-rose-500">*</span></label>
                        <select name="category_id" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (Merk: {{ $c->brand ?? 'Honda' }})</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Merek pabrikan otomatis mengikuti tipe motor yang dipilih.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Plat <span class="text-rose-500">*</span></label>
                        <input type="text" name="plate_number" required placeholder="Contoh: AB 1234 XY" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold uppercase focus:ring-2 focus:ring-emerald-500 bg-white">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Warna Bodi</label>
                            <input type="text" name="color" placeholder="Contoh: Hitam Doff" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tahun Perakitan</label>
                            <input type="number" name="year" value="{{ date('Y') }}" min="2015" max="2030" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>

                    <!-- Aksesoris Bawaan Fisik Unit Motor -->
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2.5">
                        <span class="font-bold text-slate-800 uppercase tracking-wider text-[10px] block">Aksesoris Standar Bawaan Unit:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1 text-[11px]">Helm Bawaan</label>
                                <select name="default_helmets_count" class="w-full p-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                                    <option value="2" selected>2 Buah Helm (Standar)</option>
                                    <option value="1">1 Buah Helm</option>
                                    <option value="0">Tanpa Helm</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1 text-[11px]">Jas Hujan Bawaan</label>
                                <select name="default_raincoats_count" class="w-full p-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                                    <option value="1" selected>1 Buah Jas Hujan</option>
                                    <option value="2">2 Buah Jas Hujan</option>
                                    <option value="0">Tanpa Jas Hujan</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1 text-[11px]">Kelengkapan Kunci & STNK Asli</label>
                            <select name="default_key_and_stnk" class="w-full p-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                                <option value="1" selected>Lengkap (Kunci + STNK Tersedia)</option>
                                <option value="0">Tidak Lengkap / STNK Diurus</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Awal <span class="text-rose-500">*</span></label>
                        <select name="status" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                            <option value="available">Tersedia di Garasi (Siap disewa)</option>
                            <option value="maintenance">Bengkel / Servis (Non-stok)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Kondisi fisik, nomor rangka, dll..." class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white"></textarea>
                    </div>
                </div>

                <!-- Footer (Static) -->
                <div class="flex items-center justify-end gap-2 p-4 px-5 border-t border-slate-100 bg-slate-50/50 shrink-0">
                    <button type="button" @click="showAddModal = false" class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-600/20">
                        Simpan Motor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Unit Motor -->
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
                        <h3 class="font-extrabold text-sm text-slate-900">Edit Data Motor Fisik</h3>
                        <p class="text-[11px] text-slate-400" x-text="'Plat Nomor: ' + editData.plate_number"></p>
                    </div>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1 text-lg leading-none">&times;</button>
            </div>

            <!-- Form with Scrollable Body & Static Footer -->
            <form :action="'{{ url('admin/motorcycles') }}/' + editData.id" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                @method('PUT')
                <div class="p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kategori Tipe Motor <span class="text-rose-500">*</span></label>
                        <select name="category_id" x-model="editData.category_id" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (Merk: {{ $c->brand ?? 'Honda' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Plat <span class="text-rose-500">*</span></label>
                        <input type="text" name="plate_number" x-model="editData.plate_number" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold uppercase focus:ring-2 focus:ring-indigo-500 bg-white">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Warna Bodi</label>
                            <input type="text" name="color" x-model="editData.color" placeholder="Contoh: Hitam Doff" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tahun Perakitan</label>
                            <input type="number" name="year" x-model="editData.year" min="2015" max="2030" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white">
                        </div>
                    </div>

                    <!-- Aksesoris Bawaan Fisik Unit Motor -->
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2.5">
                        <span class="font-bold text-slate-800 uppercase tracking-wider text-[10px] block">Aksesoris Standar Bawaan Unit:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1 text-[11px]">Helm Bawaan</label>
                                <select name="default_helmets_count" x-model="editData.default_helmets_count" class="w-full p-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <option value="2">2 Buah Helm (Standar)</option>
                                    <option value="1">1 Buah Helm</option>
                                    <option value="0">Tanpa Helm</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1 text-[11px]">Jas Hujan Bawaan</label>
                                <select name="default_raincoats_count" x-model="editData.default_raincoats_count" class="w-full p-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <option value="1">1 Buah Jas Hujan</option>
                                    <option value="2">2 Buah Jas Hujan</option>
                                    <option value="0">Tanpa Jas Hujan</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1 text-[11px]">Kelengkapan Kunci & STNK Asli</label>
                            <select name="default_key_and_stnk" x-model="editData.default_key_and_stnk" class="w-full p-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="1">Lengkap (Kunci + STNK Tersedia)</option>
                                <option value="0">Tidak Lengkap / STNK Diurus</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Unit <span class="text-rose-500">*</span></label>
                        <select name="status" x-model="editData.status" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="available">Tersedia di Garasi (Siap disewa)</option>
                            <option value="rented">Sedang Disewa Tamu</option>
                            <option value="maintenance">Bengkel / Servis (Non-stok)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                        <textarea name="notes" x-model="editData.notes" rows="2" placeholder="Kondisi fisik, nomor rangka, dll..." class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 bg-white"></textarea>
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

    <!-- Modal Konfirmasi Hapus Motor -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="showDeleteModal = false" class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Hapus Unit Motor?</h3>
                    <p class="text-xs text-slate-500">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <!-- Peringatan jika motor sedang disewa -->
            <template x-if="deleteData.status === 'rented'">
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-amber-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Unit Sedang Disewa
                    </div>
                    <p class="text-[11px] leading-relaxed">
                        Motor dengan nomor plat <span class="font-bold font-mono" x-text="deleteData.plate_number"></span> saat ini sedang disewa tamu. Anda tidak dapat menghapus motor yang sedang aktif di jalan.
                    </p>
                </div>
            </template>

            <!-- Keterangan normal jika siap dihapus -->
            <template x-if="deleteData.status !== 'rented'">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 text-xs space-y-1">
                    <p>
                        Apakah Anda yakin ingin menghapus motor dengan nomor plat:
                    </p>
                    <p class="font-extrabold text-sm text-slate-900 font-mono" x-text="deleteData.plate_number"></p>
                    <p class="text-[11px] text-slate-500" x-text="'Tipe: ' + deleteData.category_name"></p>
                </div>
            </template>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition-colors">
                    Batal
                </button>
                <template x-if="deleteData.status !== 'rented'">
                    <form :action="'{{ url('admin/motorcycles') }}/' + deleteData.id" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors shadow-xs">
                            Ya, Hapus Motor
                        </button>
                    </form>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
