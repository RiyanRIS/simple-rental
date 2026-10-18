@extends('layouts.admin')

@section('title', 'Laporan & Rekapitulasi')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white p-5 md:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-lg md:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">Laporan & Rekapitulasi
            </h1>
            <p class="text-xs text-slate-400 mt-0">
                Analisis keuangan, utilisasi armada motor, performa kurir lapangan, dan logistik sewa.
            </p>
        </div>

        <!-- Tombol Aksi Ekspor -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.reports.export-csv', request()->query()) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-bold text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Excel / CSV</span>
            </a>

            <a href="{{ route('admin.reports.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-bold text-xs bg-slate-900 text-white hover:bg-slate-800 shadow-sm transition-colors">
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / Simpan PDF</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar Interaktif -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="space-y-4">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5">
                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Mulai Dari</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 font-medium text-slate-800">
                </div>

                <!-- Tanggal Sampai -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Sampai Dengan</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 font-medium text-slate-800">
                </div>

                <!-- Status Booking -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Reservasi</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-slate-800">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Sedang Disewa</option>
                        <option value="delivering" {{ $status === 'delivering' ? 'selected' : '' }}>Sedang Diantar</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending (Verifikasi DP)</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <!-- Filter Kategori -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tipe Motor</label>
                    <select name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-slate-800">
                        <option value="">Semua Tipe Motor</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Unit Fisik -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Plat Motor</label>
                    <select name="motorcycle_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-slate-800">
                        <option value="">Semua Plat Motor</option>
                        @foreach($allMotorcycles as $motor)
                            <option value="{{ $motor->id }}" {{ $motorcycleId == $motor->id ? 'selected' : '' }}>{{ $motor->plate_number }} ({{ $motor->category?->name }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Petugas -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Petugas Kurir</label>
                    <select name="staff_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-slate-800">
                        <option value="">Semua Petugas</option>
                        @foreach($allStaff as $stf)
                            <option value="{{ $stf->id }}" {{ $staffId == $stf->id ? 'selected' : '' }}>{{ $stf->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                <a href="{{ route('admin.reports.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Reset Filter
                </a>
                <button type="submit" class="px-4 py-2 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Ringkasan Metrik Finansial & Operasional -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- 1. Gross Revenue -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-4 sm:p-5 rounded-2xl shadow-sm space-y-1">
            <span class="text-[11px] font-bold text-emerald-100 uppercase tracking-wider block">Total Omset Bruto</span>
            <div class="text-xl sm:text-2xl font-black">
                Rp {{ number_format($metrics['total_gross_revenue'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-emerald-100/90 pt-1 border-t border-emerald-500/40">
                Dari {{ $metrics['valid_bookings_count'] }} transaksi aktif / selesai
            </p>
        </div>

        <!-- 2. Rental Net & Overtime -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pendapatan Sewa & Overtime</span>
            <div class="text-lg sm:text-xl font-extrabold text-slate-900">
                Rp {{ number_format($metrics['total_rental_subtotal'] + $metrics['total_overtime_fee'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 pt-1 border-t border-slate-100 flex items-center justify-between">
                <span>Sewa: Rp {{ number_format($metrics['total_rental_subtotal'], 0, ',', '.') }}</span>
                <span class="text-amber-700">OT: Rp {{ number_format($metrics['total_overtime_fee'], 0, ',', '.') }}</span>
            </p>
        </div>

        <!-- 3. Arus Kas Terkumpul (DP + Pelunasan) -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Kas Diterima (DP + Lunas)</span>
            <div class="text-lg sm:text-xl font-extrabold text-emerald-700">
                Rp {{ number_format($metrics['total_dp_collected'] + $metrics['total_remaining_collected'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 pt-1 border-t border-slate-100 flex items-center justify-between">
                <span>DP: Rp {{ number_format($metrics['total_dp_collected'], 0, ',', '.') }}</span>
                <span>Lap: Rp {{ number_format($metrics['total_remaining_collected'], 0, ',', '.') }}</span>
            </p>
        </div>

        <!-- 4. Piutang Berjalan -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-1">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Sisa Tagihan Berjalan</span>
            <div class="text-lg sm:text-xl font-extrabold text-amber-800">
                Rp {{ number_format($metrics['total_unpaid_remaining'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                Ongkir zona terkumpul: Rp {{ number_format($metrics['total_delivery_fee'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Tab Navigasi Sub-Laporan -->
    <div class="border-b border-slate-200 flex gap-2 overflow-x-auto text-xs font-bold">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'revenue']) }}" 
            class="px-4 py-3 border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'revenue' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Rincian Transaksi & Keuangan ({{ $bookings->count() }})</span>
        </a>

        <a href="{{ request()->fullUrlWithQuery(['tab' => 'fleet']) }}" 
            class="px-4 py-3 border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'fleet' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span>Utilisasi Armada Motor ({{ $motorcycles->count() }})</span>
        </a>

        <a href="{{ request()->fullUrlWithQuery(['tab' => 'staff']) }}" 
            class="px-4 py-3 border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'staff' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Kinerja & Setoran Petugas ({{ $staffMembers->count() }})</span>
        </a>

        <a href="{{ request()->fullUrlWithQuery(['tab' => 'zones']) }}" 
            class="px-4 py-3 border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'zones' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Sebaran Zona Antar-Jemput ({{ $zones->count() }})</span>
        </a>
    </div>

    <!-- KONTEN TAB 1: RINCIAN TRANSAKSI & KEUANGAN -->
    @if($activeTab === 'revenue')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <span class="font-bold text-slate-800 text-sm">Daftar Reservasi Periode {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                <span class="text-xs text-slate-400">Total: {{ $bookings->count() }} baris</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Kode / Waktu</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Motor & Plat</th>
                            <th class="px-4 py-3">Jadwal Sewa</th>
                            <th class="px-4 py-3 text-right">Durasi</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                            <th class="px-4 py-3 text-right">Total Biaya</th>
                            <th class="px-4 py-3 text-right">DP / Sisa</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($bookings as $b)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-extrabold text-slate-900 block">{{ $b->booking_code }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $b->created_at ? $b->created_at->format('d/m/Y H:i') : '-' }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-bold text-slate-800 block">{{ $b->customer_name }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $b->customer_phone }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800 block">{{ $b->category?->name ?? '-' }}</span>
                                    @if($b->motorcycle)
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $b->motorcycle->plate_number }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Belum dialokasikan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-[11px]">
                                    <span class="font-medium text-slate-800">{{ $b->start_time->format('d/m/y H:i') }}</span>
                                    <span class="text-slate-400 block">s/d {{ $b->end_time->format('d/m/y H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <span class="font-bold text-slate-800">{{ $b->total_days }} Hari</span>
                                    @if($b->overtime_hours > 0)
                                        <span class="text-[10px] text-amber-700 block">+{{ $b->overtime_hours }} jam OT</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right font-medium text-slate-600">
                                    Rp {{ number_format($b->rental_subtotal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <span class="font-extrabold text-slate-900 block">Rp {{ number_format($b->total_amount, 0, ',', '.') }}</span>
                                    @if($b->delivery_fee + $b->return_fee > 0)
                                        <span class="text-[10px] text-slate-400">Ongkir: Rp {{ number_format($b->delivery_fee + $b->return_fee, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <span class="text-emerald-700 font-bold block">DP: Rp {{ number_format($b->down_payment_amount, 0, ',', '.') }}</span>
                                    @if($b->remaining_amount > 0 && $b->status !== 'completed' && $b->payment_status !== 'paid_full')
                                        <span class="text-amber-800 text-[10px] font-semibold block">Sisa: Rp {{ number_format($b->remaining_amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-slate-400 text-[10px] block">Lunas</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $b->status_badge_class }}">
                                        {{ $b->formatted_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-800 font-bold hover:underline">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-8 text-center text-slate-400">
                                    Tidak ada data reservasi pada rentang filter tanggal yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- KONTEN TAB 2: UTILISASI ARMADA MOTOR -->
    @if($activeTab === 'fleet')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Tingkat Utilisasi & Kontribusi Unit Fisik</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Memantau berapa kali dan berapa lama masing-masing motor bekerja di jalan</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Plat Nomor</th>
                            <th class="px-4 py-3">Tipe / Kategori</th>
                            <th class="px-4 py-3">Warna</th>
                            <th class="px-4 py-3 text-center">Frekuensi Sewa</th>
                            <th class="px-4 py-3 text-center">Total Hari Beroperasi</th>
                            <th class="px-4 py-3 text-right">Omset Dihasilkan</th>
                            <th class="px-4 py-3 text-center">Status Unit Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($motorcycles as $motor)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3.5 whitespace-nowrap font-black text-slate-900 text-sm">
                                    {{ $motor['plate_number'] }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap font-semibold text-slate-800">
                                    {{ $motor['category_name'] }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-slate-500">
                                    {{ $motor['color'] }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $motor['booking_count'] > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $motor['booking_count'] }} kali
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center font-bold text-slate-800">
                                    {{ $motor['total_days'] }} hari
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right font-extrabold text-emerald-700 text-sm">
                                    Rp {{ number_format($motor['total_revenue'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $motor['status'] === 'available' ? 'bg-emerald-100 text-emerald-800' : ($motor['status'] === 'rented' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ ucfirst($motor['status']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    Belum ada data unit motor fisik yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- KONTEN TAB 3: KINERJA & SETORAN PETUGAS LAPANGAN -->
    @if($activeTab === 'staff')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Kinerja Pengantaran & Rekap Setoran Uang Lapangan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Memantau tugas serah terima dan pelunasan uang sewa yang diterima kurir</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Nama Petugas</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">No. Handphone</th>
                            <th class="px-4 py-3 text-center">Tugas Antar Selesai</th>
                            <th class="px-4 py-3 text-center">Tugas Jemput Selesai</th>
                            <th class="px-4 py-3 text-center">Total Penugasan</th>
                            <th class="px-4 py-3 text-right">Uang Pelunasan Diterima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($staffMembers as $staff)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3.5 whitespace-nowrap font-extrabold text-slate-900">
                                    {{ $staff['name'] }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $staff['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $staff['role'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-slate-500">
                                    {{ $staff['phone'] }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center font-bold text-slate-800">
                                    {{ $staff['delivery_count'] }} order
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center font-bold text-slate-800">
                                    {{ $staff['return_count'] }} order
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center font-black text-slate-900">
                                    {{ $staff['total_tasks'] }} order
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right font-black text-emerald-700 text-sm">
                                    Rp {{ number_format($staff['cash_collected'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    Belum ada data petugas kurir.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- KONTEN TAB 4: SEBARAN ZONA ANTAR-JEMPUT -->
    @if($activeTab === 'zones')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Sebaran Logistik & Titik Antar-Jemput Populer</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Analisis lokasi favorit wisatawan dan kontribusi ongkir masing-masing zona</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">Nama Zona / Titik Lokasi</th>
                            <th class="px-4 py-3">Tarif Ongkir Standar</th>
                            <th class="px-4 py-3 text-center">Frekuensi Terima (Antar)</th>
                            <th class="px-4 py-3 text-center">Frekuensi Kembali (Jemput)</th>
                            <th class="px-4 py-3 text-center">Total Mobilitas</th>
                            <th class="px-4 py-3 text-right">Estimasi Ongkir Dihasilkan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($zones as $z)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-3.5 whitespace-nowrap font-extrabold text-slate-900">
                                    {{ $z['name'] }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap font-semibold {{ $z['fee'] == 0 ? 'text-emerald-700' : 'text-slate-800' }}">
                                    {{ $z['fee'] == 0 ? 'GRATIS' : 'Rp ' . number_format($z['fee'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center font-bold text-slate-800">
                                    {{ $z['delivery_count'] }} kali
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center font-bold text-slate-800">
                                    {{ $z['return_count'] }} kali
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center font-black text-slate-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        {{ $z['total_trips'] }} trip
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right font-extrabold text-slate-900 text-sm">
                                    Rp {{ number_format($z['revenue_generated'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">
                                    Belum ada zona pengantaran yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
