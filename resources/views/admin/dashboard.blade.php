@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">Menunggu Review</p>
                <h3 class="text-xl sm:text-2xl font-black text-amber-600 mt-0.5">{{ $pendingCount }}</h3>
                <span class="text-[10px] text-slate-500 block truncate">Perlu cek bukti DP</span>
            </div>
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base shrink-0">
                ⏳
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">Antar Hari Ini</p>
                <h3 class="text-xl sm:text-2xl font-black text-blue-600 mt-0.5">{{ $approvedTodayCount }}</h3>
                <span class="text-[10px] text-slate-500 block truncate">Jadwal pengantaran</span>
            </div>
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base shrink-0">
                🛵
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">Unit Sedang Disewa</p>
                <h3 class="text-xl sm:text-2xl font-black text-emerald-600 mt-0.5">{{ $activeRentalsCount }}</h3>
                <span class="text-[10px] text-slate-500 block truncate">Di tangan pelanggan</span>
            </div>
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base shrink-0">
                🔑
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider truncate">Stok Ready</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800 mt-0.5">{{ $availableMotorsCount }} <span class="text-xs font-normal text-slate-400">/ {{ $totalMotorsCount }}</span></h3>
                <span class="text-[10px] text-slate-500 block truncate">Unit di garasi siap pakai</span>
            </div>
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-base shrink-0">
                🅿️
            </div>
        </div>
    </div>

    <!-- 1. Antaran Mendatang (Siap Antar & OTW) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <div>
                <h2 class="font-bold text-xs sm:text-sm text-slate-800 uppercase tracking-wider">Jadwal Pengantaran Motor (Siap Antar & OTW)</h2>
                <p class="text-[11px] text-slate-400">Pastikan unit motor dan kurir pengantar sudah teralokasi</p>
            </div>
            <a href="{{ route('admin.bookings.index', ['status' => 'approved']) }}" class="text-xs font-bold text-emerald-700 hover:underline shrink-0">
                Semua &rarr;
            </a>
        </div>

        <!-- Desktop View (Tanpa horizontal scroll, fluid 100%) -->
        <div class="hidden md:block">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead>
                    <tr class="text-slate-400 uppercase text-[10px] font-bold">
                        <th class="pb-2.5 w-[26%]">Kode & Pelanggan</th>
                        <th class="pb-2.5 w-[26%]">Motor & Plat</th>
                        <th class="pb-2.5 w-[26%]">Waktu & Titik Antar</th>
                        <th class="pb-2.5 w-[22%] text-right">Kurir & Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($upcomingDeliveries as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 pr-3">
                                <a href="{{ route('admin.bookings.show', $item->id) }}" class="font-mono font-extrabold text-xs text-slate-900 hover:text-emerald-700 block">
                                    {{ $item->booking_code }}
                                </a>
                                <span class="font-bold text-slate-800 block truncate text-xs mt-0.5" title="{{ $item->customer_name }}">{{ $item->customer_name }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $item->customer_phone }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-semibold text-slate-900 block truncate">{{ $item->category->name }}</span>
                                @if($item->motorcycle)
                                    <span class="inline-block px-1.5 py-0.2 rounded font-mono font-bold text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200 mt-0.5">
                                        {{ $item->motorcycle->plate_number }}
                                    </span>
                                @else
                                    <span class="inline-block px-1.5 py-0.2 rounded font-medium text-[10px] bg-amber-50 text-amber-800 border border-amber-200 mt-0.5">
                                        Belum Diplot
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-emerald-700 block">{{ $item->start_time->format('d M, H:i') }} WIB</span>
                                <span class="text-[11px] text-slate-600 block truncate mt-0.5" title="{{ $item->deliveryZone?->name }}">{{ $item->deliveryZone?->name }}</span>
                            </td>
                            <td class="py-3 pl-3 text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold inline-block mb-1.5 {{ $item->deliveryStaff ? 'bg-slate-100 text-slate-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $item->deliveryStaff?->name ?? 'Belum Ditugaskan' }}
                                </span>
                                <div>
                                    <a href="{{ route('admin.bookings.show', $item->id) }}" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold border border-emerald-200 text-xs inline-block">
                                        Detail / Plot
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">Tidak ada pengantaran mendesak saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List (Zero horizontal scroll) -->
        <div class="md:hidden space-y-3">
            @forelse($upcomingDeliveries as $item)
                <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-200/80 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <a href="{{ route('admin.bookings.show', $item->id) }}" class="font-mono font-extrabold text-xs text-slate-900 hover:text-emerald-700">
                                {{ $item->booking_code }}
                            </a>
                            <span class="font-bold text-slate-800 block text-xs mt-0.5">{{ $item->customer_name }}</span>
                            <span class="text-[11px] text-slate-400">{{ $item->customer_phone }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0">
                            {{ $item->start_time->format('d M, H:i') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-200/60">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Unit Motor</span>
                            <span class="font-medium text-slate-800 block truncate">{{ $item->category->name }}</span>
                            <span class="font-mono font-bold text-slate-900 text-[11px]">{{ $item->motorcycle?->plate_number ?? 'Belum diplot' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Titik Antar</span>
                            <span class="font-medium text-slate-800 block truncate" title="{{ $item->deliveryZone?->name }}">{{ $item->deliveryZone?->name }}</span>
                            <span class="text-[10px] text-slate-500 block truncate">{{ $item->deliveryStaff?->name ?? 'Kurir belum diset' }}</span>
                        </div>
                    </div>

                    <div class="pt-1">
                        <a href="{{ route('admin.bookings.show', $item->id) }}" class="w-full py-1.5 px-3 rounded-lg bg-emerald-600 text-white font-bold text-xs text-center block">
                            Detail & Plot Petugas
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-5 text-center text-slate-400 text-xs">
                    Tidak ada pengantaran mendesak saat ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. Unit Sedang Digunakan Tamu (Aktif Disewa) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <div>
                <h2 class="font-bold text-xs sm:text-sm text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Unit Sedang Digunakan Tamu (Aktif Disewa)
                </h2>
                <p class="text-[11px] text-slate-400">Unit yang telah diserah-terimakan oleh petugas kurir</p>
            </div>
            <a href="{{ route('admin.bookings.index', ['status' => 'active']) }}" class="text-xs font-bold text-emerald-700 hover:underline shrink-0">
                Semua &rarr;
            </a>
        </div>

        <!-- Desktop View (Tanpa horizontal scroll, fluid 100%) -->
        <div class="hidden md:block">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead>
                    <tr class="text-slate-400 uppercase text-[10px] font-bold">
                        <th class="pb-2.5 w-[26%]">Kode & Pelanggan</th>
                        <th class="pb-2.5 w-[26%]">Motor & Plat</th>
                        <th class="pb-2.5 w-[26%]">Jadwal Kembali & Titik</th>
                        <th class="pb-2.5 w-[22%] text-right">Kurir & Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($activeRentals as $active)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 pr-3">
                                <a href="{{ route('admin.bookings.show', $active->id) }}" class="font-mono font-extrabold text-xs text-slate-900 hover:text-emerald-700 block">
                                    {{ $active->booking_code }}
                                </a>
                                <span class="font-bold text-slate-800 block truncate text-xs mt-0.5" title="{{ $active->customer_name }}">{{ $active->customer_name }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $active->customer_phone }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-semibold text-slate-900 block truncate">{{ $active->category->name }}</span>
                                <span class="inline-block px-1.5 py-0.2 rounded font-mono font-bold text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200 mt-0.5">
                                    {{ $active->motorcycle?->plate_number }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $active->end_time->format('d M, H:i') }} WIB</span>
                                <span class="text-[11px] text-slate-600 block truncate mt-0.5" title="{{ $active->returnZone?->name }}">{{ $active->returnZone?->name }}</span>
                            </td>
                            <td class="py-3 pl-3 text-right">
                                <span class="text-[11px] text-slate-600 block font-medium mb-1.5 truncate">
                                    {{ $active->deliveryStaff?->name ?? '-' }}
                                </span>
                                <div>
                                    <a href="{{ route('admin.bookings.show', $active->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs inline-block">
                                        Detail Ceklis
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">Belum ada unit yang sedang aktif disewa saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List (Zero horizontal scroll) -->
        <div class="md:hidden space-y-3">
            @forelse($activeRentals as $active)
                <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-200/80 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <a href="{{ route('admin.bookings.show', $active->id) }}" class="font-mono font-extrabold text-xs text-slate-900 hover:text-emerald-700">
                                {{ $active->booking_code }}
                            </a>
                            <span class="font-bold text-slate-800 block text-xs mt-0.5">{{ $active->customer_name }}</span>
                            <span class="text-[11px] text-slate-400">{{ $active->customer_phone }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 shrink-0">
                            Aktif
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-200/60">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Unit Motor</span>
                            <span class="font-medium text-slate-800 block truncate">{{ $active->category->name }}</span>
                            <span class="font-mono font-bold text-emerald-700 text-[11px]">{{ $active->motorcycle?->plate_number }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Jadwal Kembali</span>
                            <span class="font-bold text-slate-900 block">{{ $active->end_time->format('d M, H:i') }}</span>
                            <span class="text-[10px] text-slate-500 block truncate">{{ $active->returnZone?->name }}</span>
                        </div>
                    </div>

                    <div class="pt-1">
                        <a href="{{ route('admin.bookings.show', $active->id) }}" class="w-full py-1.5 px-3 rounded-lg bg-slate-800 text-white font-bold text-xs text-center block">
                            Buka Detail Ceklis Serah-Terima
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-5 text-center text-slate-400 text-xs">
                    Belum ada unit yang sedang aktif disewa saat ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 3. Aktivitas Reservasi Terbaru -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <div>
                <h2 class="font-bold text-xs sm:text-sm text-slate-800 uppercase tracking-wider">Aktivitas Reservasi Terbaru</h2>
                <p class="text-[11px] text-slate-400">Daftar booking yang baru masuk via website</p>
            </div>
            <a href="{{ route('admin.bookings.index') }}" class="text-xs font-bold text-emerald-700 hover:underline shrink-0">
                Kelola Semua &rarr;
            </a>
        </div>

        <!-- Desktop View (Tanpa horizontal scroll, fluid 100%) -->
        <div class="hidden md:block">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead>
                    <tr class="text-slate-400 uppercase text-[10px] font-bold">
                        <th class="pb-2.5 w-[26%]">Kode & Pelanggan</th>
                        <th class="pb-2.5 w-[26%]">Motor & Jadwal</th>
                        <th class="pb-2.5 w-[26%]">Total Biaya & DP</th>
                        <th class="pb-2.5 w-[22%] text-right">Status & Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentBookings as $b)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 pr-3">
                                <a href="{{ route('admin.bookings.show', $b->id) }}" class="font-mono font-extrabold text-xs text-slate-900 hover:text-emerald-700 block">
                                    {{ $b->booking_code }}
                                </a>
                                <span class="font-bold text-slate-800 block truncate text-xs mt-0.5" title="{{ $b->customer_name }}">{{ $b->customer_name }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $b->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-semibold text-slate-900 block truncate">{{ $b->category->name }}</span>
                                <span class="text-[11px] text-slate-500 block mt-0.5">
                                    {{ $b->start_time->format('d M') }} s/d {{ $b->end_time->format('d M') }} ({{ $b->total_days }} Hari)
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block text-xs">Rp {{ number_format($b->total_amount, 0, ',', '.') }}</span>
                                <span class="text-[11px] text-emerald-700 font-semibold block">DP: Rp {{ number_format($b->down_payment_amount, 0, ',', '.') }}</span>
                            </td>
                            <td class="py-3 pl-3 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border inline-block mb-1.5 {{ $b->status_badge_class }}">
                                    {{ $b->formatted_status }}
                                </span>
                                <div>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs inline-block">
                                        Buka &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">Belum ada data reservasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List (Zero horizontal scroll) -->
        <div class="md:hidden space-y-3">
            @forelse($recentBookings as $b)
                <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-200/80 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <a href="{{ route('admin.bookings.show', $b->id) }}" class="font-mono font-extrabold text-xs text-slate-900 hover:text-emerald-700">
                                {{ $b->booking_code }}
                            </a>
                            <span class="font-bold text-slate-800 block text-xs mt-0.5">{{ $b->customer_name }}</span>
                            <span class="text-[10px] text-slate-400">{{ $b->created_at->diffForHumans() }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border shrink-0 {{ $b->status_badge_class }}">
                            {{ $b->formatted_status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-200/60">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Motor</span>
                            <span class="font-medium text-slate-800 block truncate">{{ $b->category->name }}</span>
                            <span class="text-[10px] text-slate-500 block">{{ $b->start_time->format('d M') }} - {{ $b->end_time->format('d M') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Biaya & DP</span>
                            <span class="font-bold text-slate-900 block">Rp {{ number_format($b->total_amount, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-emerald-700 font-semibold block">DP: Rp {{ number_format($b->down_payment_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="pt-1">
                        <a href="{{ route('admin.bookings.show', $b->id) }}" class="w-full py-1.5 px-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-xs text-center block">
                            Buka Detail Reservasi
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-5 text-center text-slate-400 text-xs">
                    Belum ada data reservasi.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
