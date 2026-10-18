@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ 
    showAddModal: false, 
    showEditModal: false,
    showAssetTutorialModal: false,
    editData: {
        id: '',
        name: '',
        brand: '',
        cc_capacity: 110,
        daily_price: 0,
        weekend_price: '',
        hourly_overtime_price: 0,
        is_active: 1,
        description: ''
    },
    openEdit(cat) {
        this.editData = {
            id: cat.id,
            name: cat.name,
            brand: cat.brand || 'Honda',
            cc_capacity: cat.cc_capacity,
            daily_price: parseInt(cat.daily_price),
            weekend_price: cat.weekend_price ? parseInt(cat.weekend_price) : '',
            hourly_overtime_price: parseInt(cat.hourly_overtime_price),
            is_active: cat.is_active ? 1 : 0,
            description: cat.description || ''
        };
        this.showEditModal = true;
    }
}">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white p-5 md:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-lg md:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">Kategori Tipe & Tarif Sewa</h1>
            <p class="text-xs text-slate-400 mt-0">Atur tarif sewa per 24 jam dan biaya overtime per jam</p>
        </div>
        <button type="button" @click="showAddModal = true" class="py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Kategori Baru</span>
        </button>
    </div>

    <!-- Grid Kategori Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($categories as $cat)
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 flex flex-col">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h2 class="font-extrabold text-base text-slate-900">{{ $cat->name }}</h2>
                            <span class="inline-flex items-center text-[10px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                {{ $cat->brand ?? 'Honda' }}
                            </span>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">{{ $cat->cc_capacity }} cc</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $cat->motorcycles_count }} Unit Motor
                    </span>
                </div>

                <!-- Foto Motor Thumbnail -->
                <div class="h-32 w-full flex items-center justify-center bg-slate-50/80 rounded-xl p-2 overflow-hidden border border-slate-100">
                    @if($cat->image_thumbnail_url ?? $cat->image_url)
                        <img src="{{ $cat->image_thumbnail_url ?? $cat->image_url }}" alt="{{ $cat->name }}" class="max-h-full max-w-full object-contain filter drop-shadow">
                    @else
                        <div class="text-slate-300 flex flex-col items-center">
                            <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4" />
                            </svg>
                            <span class="text-[10px] text-slate-400">Belum ada foto</span>
                        </div>
                    @endif
                </div>

                <p class="text-xs text-slate-600 line-clamp-2">{{ $cat->description }}</p>

                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tarif Reguler (Sen-Kam):</span>
                        <strong class="text-emerald-700 font-extrabold">Rp {{ number_format($cat->daily_price, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tarif Weekend (Jum-Min):</span>
                        @if($cat->weekend_price && $cat->weekend_price > $cat->daily_price)
                            <strong class="text-purple-700 font-extrabold">Rp {{ number_format($cat->weekend_price, 0, ',', '.') }}</strong>
                        @else
                            <span class="text-slate-400 font-semibold">Sama dg reguler</span>
                        @endif
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tarif Overtime / Jam:</span>
                        <strong class="text-amber-700 font-extrabold">Rp {{ number_format($cat->hourly_overtime_price, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs mt-auto">
                    <span class="text-[11px] font-bold {{ $cat->is_active ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $cat->is_active ? '● Aktif Ditampilkan' : '○ Dinonaktifkan' }}
                    </span>

                    <button type="button" @click="openEdit({{ $cat->toJson() }})" class="font-bold text-slate-600 hover:text-emerald-700 transition-colors">
                        Edit Kategori ✎
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Edit Kategori (Pop-up) -->
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
                        <h3 class="font-extrabold text-sm text-slate-900">Edit Kategori Tipe Motor</h3>
                        <p class="text-[11px] text-slate-400" x-text="editData.name"></p>
                    </div>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1 leading-none text-lg">&times;</button>
            </div>

            <!-- Form with Scrollable Body & Static Footer -->
            <form :action="'{{ url('admin/categories') }}/' + editData.id" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                @csrf
                @method('PUT')
                <div class="p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Model Tipe <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editData.name" required placeholder="Contoh: Honda Vario 125cc eSP ISS" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Merk Pabrikan <span class="text-rose-500">*</span></label>
                            <input type="text" name="brand" x-model="editData.brand" required placeholder="Honda / Yamaha" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kapasitas Mesin (cc) <span class="text-rose-500">*</span></label>
                            <input type="number" name="cc_capacity" x-model="editData.cc_capacity" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tarif Reguler (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="daily_price" x-model="editData.daily_price" step="5000" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                            <span class="text-[10px] text-slate-400">Senin - Kamis</span>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tarif Weekend (Rp)</label>
                            <input type="number" name="weekend_price" x-model="editData.weekend_price" step="5000" placeholder="Sama dg reguler" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                            <span class="text-[10px] text-purple-600 font-medium">Jumat - Minggu (Opsional)</span>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Overtime/Jam (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="hourly_overtime_price" x-model="editData.hourly_overtime_price" step="1000" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                            <span class="text-[10px] text-slate-400">Tarif telat per jam</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-bold text-slate-700">Ganti Foto Motor (Opsional)</label>
                            <button type="button" @click="showAssetTutorialModal = true" class="text-[10px] text-emerald-700 hover:text-emerald-800 font-bold underline inline-flex items-center gap-1">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Panduan Cari Foto PNG ↗</span>
                            </button>
                        </div>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-slate-200">
                        <p class="text-[10px] text-slate-400 mt-1">Disarankan foto tampak samping tanpa background (format PNG/WebP).</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Tampil</label>
                        <select name="is_active" x-model="editData.is_active" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                            <option value="1">Aktif Ditampilkan</option>
                            <option value="0">Sembunyikan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                        <textarea name="description" x-model="editData.description" rows="2" placeholder="Keunggulan unit..." class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white"></textarea>
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

    <!-- Modal Tambah Kategori -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="showAddModal = false" class="bg-white rounded-2xl max-w-lg w-full shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
            <!-- Header (Static) -->
            <div class="flex items-center justify-between border-b border-slate-100 p-5 pb-4 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900">Tambah Tipe Motor Baru</h3>
                        <p class="text-[11px] text-slate-400">Daftarkan varian model, cc, tarif sewa dan foto unit</p>
                    </div>
                </div>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1 leading-none text-lg">&times;</button>
            </div>

            <!-- Form with Scrollable Body & Static Footer -->
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                @csrf
                <div class="p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Model Tipe <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Honda Vario 160cc ABS" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Merk Pabrikan <span class="text-rose-500">*</span></label>
                            <input type="text" name="brand" placeholder="Honda / Yamaha" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kapasitas Mesin (cc) <span class="text-rose-500">*</span></label>
                            <input type="number" name="cc_capacity" value="125" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tarif Reguler (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="daily_price" value="95000" step="5000" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                            <span class="text-[10px] text-slate-400">Senin - Kamis</span>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tarif Weekend (Rp)</label>
                            <input type="number" name="weekend_price" step="5000" placeholder="Opsional (misal: 110000)" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                            <span class="text-[10px] text-purple-600 font-medium">Jumat - Minggu (Opsional)</span>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tarif Overtime/Jam <span class="text-rose-500">*</span></label>
                            <input type="number" name="hourly_overtime_price" value="10000" step="1000" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                            <span class="text-[10px] text-slate-400">Tarif telat per jam</span>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-bold text-slate-700">Foto Motor (PNG Transparan)</label>
                            <button type="button" @click="showAssetTutorialModal = true" class="text-[10px] text-emerald-700 hover:text-emerald-800 font-bold underline inline-flex items-center gap-1">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Panduan ↗</span>
                            </button>
                        </div>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-slate-200">
                        <p class="text-[10px] text-slate-400 mt-1">Disarankan foto tampak samping tanpa background (format PNG/WebP).</p>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                        <textarea name="description" rows="2" placeholder="Keunggulan unit..." class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white"></textarea>
                    </div>
                </div>

                <!-- Footer (Static) -->
                <div class="flex items-center justify-end gap-2 p-4 px-5 border-t border-slate-100 bg-slate-50/50 shrink-0">
                    <button type="button" @click="showAddModal = false" class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-600/20">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tutorial: Panduan Mencari Aset Foto Motor Transparan (PNG) -->
    <div x-show="showAssetTutorialModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="showAssetTutorialModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <!-- Header Modal -->
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900">Panduan Cari Foto Motor (PNG)</h3>
                        <p class="text-xs text-slate-500">Cara cepat download foto motor tanpa background siap pakai</p>
                    </div>
                </div>
                <button type="button" @click="showAssetTutorialModal = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold p-1 leading-none">&times;</button>
            </div>

            <!-- Isi Tutorial Langkah demi Langkah -->
            <div class="space-y-3.5 text-xs">
                <!-- Langkah 1 -->
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-emerald-600 text-white font-extrabold text-[11px] flex items-center justify-center">1</span>
                        <h4 class="font-bold text-slate-800">Kunjungi Website Penyedia PNG Transparan</h4>
                    </div>
                    <p class="text-slate-600 text-[11px] leading-relaxed">
                        Klik salah satu situs di bawah ini (sudah menyediakan jutaan aset gambar motor siap pakai tanpa perlu hapus background):
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                        <a href="https://www.pngwing.com" target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition-all group">
                            <div>
                                <strong class="text-slate-900 font-bold block group-hover:text-emerald-700">PNGWing.com</strong>
                                <span class="text-[10px] text-slate-400">Paling lengkap & resolusi jernih</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        <a href="https://www.pngegg.com" target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition-all group">
                            <div>
                                <strong class="text-slate-900 font-bold block group-hover:text-emerald-700">PNGEgg.com</strong>
                                <span class="text-[10px] text-slate-400">Koleksi motor matic & bebek</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Langkah 2 -->
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-emerald-600 text-white font-extrabold text-[11px] flex items-center justify-center">2</span>
                        <h4 class="font-bold text-slate-800">Ketik Kata Kunci Nama Motor</h4>
                    </div>
                    <p class="text-slate-600 text-[11px]">
                        Di kolom pencarian situs tersebut, ketik nama tipe motor ditambah kata <code class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-900 font-mono font-bold text-[10px]">png</code>:
                    </p>
                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 space-y-1 font-mono text-[11px] text-slate-700">
                        <div>• <span class="text-emerald-700 font-bold">Honda Stylo 160 png</span></div>
                        <div>• <span class="text-emerald-700 font-bold">Yamaha Aerox png</span></div>
                        <div>• <span class="text-emerald-700 font-bold">Vespa Primavera png</span></div>
                    </div>
                </div>

                <!-- Langkah 3 -->
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-emerald-600 text-white font-extrabold text-[11px] flex items-center justify-center">3</span>
                        <h4 class="font-bold text-slate-800">Pilih & Unduh Foto Tampak Samping (Side View)</h4>
                    </div>
                    <ul class="text-slate-600 text-[11px] space-y-1 list-disc list-inside">
                        <li>Pilih foto motor yang menghadap samping agar proporsional dan serasi dengan motor lainnya.</li>
                        <li>Klik tombol <strong>Download PNG</strong> pada situs tersebut.</li>
                        <li>Pastikan background file berupa kotak-kotak catur (transparan).</li>
                    </ul>
                </div>

                <!-- Langkah 4 -->
                <div class="bg-emerald-50/70 p-3.5 rounded-xl border border-emerald-200 space-y-1 text-[11px] text-emerald-950">
                    <div class="flex items-center gap-1.5 font-bold text-emerald-900 text-xs">
                        <span>✓ Unggah ke Form Kategori</span>
                    </div>
                    <p class="text-emerald-800 leading-relaxed">
                        Pilih file foto yang telah diunduh tadi pada kolom <strong>Foto Motor</strong> di form Tambah/Edit Kategori, lalu klik tombol Simpan. Foto akan langsung tampil di halaman reservasi pelanggan!
                    </p>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="flex justify-end pt-3 border-t border-slate-100">
                <button type="button" @click="showAssetTutorialModal = false"
                    class="py-2.5 px-5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors shadow-xs">
                    Saya Mengerti, Tutup Panduan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
