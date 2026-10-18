@extends('layouts.staff')

@section('content')
<div x-data="{ activeTab: '{{ session('active_tab', 'deliveries') }}' }" class="space-y-4">
    <!-- Ringkasan Info Petugas & Rekap Cepat -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 text-white rounded-2xl p-4 shadow-sm border border-slate-800/80">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block">Halo, Petugas Lapangan</span>
                <h2 class="text-sm font-extrabold text-white mt-0.5">{{ Auth::user()->name }}</h2>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 block font-semibold">Tuntas Hari Ini</span>
                <span class="text-xs font-black text-emerald-400 bg-emerald-950/80 px-2 py-0.5 rounded-md border border-emerald-500/30">
                    {{ $completedTodayCount }} Unit Selesai
                </span>
            </div>
        </div>
        <p class="text-[11px] text-slate-300 mt-2 pt-2 border-t border-slate-700/60 leading-relaxed">
            Pilih dan ambil tugas pengantaran atau penjemputan di bawah secara mandiri. Petugas antar dan petugas jemput dapat berbeda.
        </p>
    </div>

    <!-- Tab Navigasi Ringkas (3 Tabs) -->
    <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-200/80 rounded-xl text-xs sticky top-16 z-40 backdrop-blur-md">
        <button type="button" @click="activeTab = 'deliveries'"
            :class="activeTab === 'deliveries' ? 'bg-white text-emerald-800 shadow-sm font-extrabold' : 'text-slate-600 font-semibold'"
            class="py-2 px-2 rounded-lg transition-all flex flex-col sm:flex-row items-center justify-center gap-1 text-center">
            <span>Antar</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-bold">
                {{ $deliveries->count() }}
            </span>
        </button>

        <button type="button" @click="activeTab = 'active'"
            :class="activeTab === 'active' ? 'bg-white text-blue-800 shadow-sm font-extrabold' : 'text-slate-600 font-semibold'"
            class="py-2 px-2 rounded-lg transition-all flex flex-col sm:flex-row items-center justify-center gap-1 text-center">
            <span>Aktif</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-blue-100 text-blue-800 font-bold">
                {{ $activeRentals->count() }}
            </span>
        </button>

        <button type="button" @click="activeTab = 'returns'"
            :class="activeTab === 'returns' ? 'bg-white text-purple-800 shadow-sm font-extrabold' : 'text-slate-600 font-semibold'"
            class="py-2 px-2 rounded-lg transition-all flex flex-col sm:flex-row items-center justify-center gap-1 text-center">
            <span>Jemput</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-purple-100 text-purple-800 font-bold">
                {{ $returns->count() }}
            </span>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: DAFTAR TUGAS ANTAR (DELIVERIES)      -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'deliveries'" class="space-y-3.5">
        <div class="flex items-center justify-between px-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jadwal Pengantaran Motor</span>
            <span class="text-[11px] text-slate-400 font-semibold">{{ $deliveries->count() }} antaran tersedia</span>
        </div>

        @forelse($deliveries as $item)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $item->customer_phone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
                $cleanEmergencyPhone = preg_replace('/[^0-9]/', '', $item->emergency_contact_phone);
                if (str_starts_with($cleanEmergencyPhone, '0')) {
                    $cleanEmergencyPhone = '62' . substr($cleanEmergencyPhone, 1);
                }
                $waText = urlencode("Halo Kak {$item->customer_name}, saya dari Rental Motor Jogja. Saya bertugas mengantarkan motor {$item->category->name} (Plat: ".($item->motorcycle?->plate_number ?? 'Unit Siap').") dengan kode booking {$item->booking_code} ke lokasi Kakak ({$item->delivery_address}) hari ini. Apakah lokasinya sudah tepat?");
                $mapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($item->delivery_address);
                
                $isMyDelivery = ($item->delivery_staff_id == Auth::id());
                $isUnassigned = is_null($item->delivery_staff_id);
            @endphp

            <div class="bg-white rounded-2xl border {{ $isMyDelivery ? 'border-emerald-300 ring-2 ring-emerald-100' : ($isUnassigned ? 'border-amber-200' : 'border-slate-200') }} shadow-sm p-4 space-y-3">
                <!-- 1. Header Card: Kode Booking & Status Waktu -->
                <div class="flex items-start justify-between border-b border-slate-100 pb-2.5">
                    <div>
                        <span class="font-extrabold text-xs text-slate-900 block font-mono">{{ $item->booking_code }}</span>
                        <span class="text-[11px] font-bold text-emerald-800 flex items-center gap-1 mt-0.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Waktu Antar: {{ $item->start_time->format('d M, H:i') }} WIB
                        </span>
                    </div>

                    <!-- Badge Status OTW / Siap Antar -->
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $item->status === 'delivering' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200 animate-pulse' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                        {{ $item->status === 'delivering' ? '🚚 Sedang OTW' : '✓ Siap Antar' }}
                    </span>
                </div>

                <!-- 2. Status Penugasan Kurir Antar (Bebas Diambil / Tugas Anda / Staf Lain) -->
                <div>
                    @if($isMyDelivery)
                        <div class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-ping"></span>
                                ✓ Tugas Saya
                            </span>
                            <span class="text-[10px] text-emerald-700 font-semibold">Siap dikerjakan</span>
                        </div>
                    @elseif($isUnassigned)
                        <div class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Belum Ada Petugas (Tersedia)
                            </span>
                            <span class="text-[10px] text-amber-700 font-semibold">Bisa diambil</span>
                        </div>
                    @else
                        <div class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs">
                            <span class="flex items-center gap-1.5 font-medium">
                                👤 Petugas Antar: <strong class="text-slate-900">{{ $item->deliveryStaff?->name }}</strong>
                            </span>
                            <span class="text-[10px] text-slate-500 font-semibold">Ditugaskan</span>
                        </div>
                    @endif
                </div>

                <!-- 3. Informasi Unit Motor & Plat Nomor (Jelas & Menonjol) -->
                <div class="bg-slate-900 text-white p-3 rounded-xl space-y-1.5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider">Unit & Plat Motor:</span>
                        @if($item->motorcycle?->color)
                            <span class="text-[10px] text-slate-300 font-semibold">Warna: {{ $item->motorcycle->color }}</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-sm text-white">{{ $item->category->name }}</span>
                        <div class="px-2.5 py-1 rounded bg-black border border-slate-700 text-white font-mono font-black text-xs tracking-wider shadow-inner">
                            {{ $item->motorcycle?->plate_number ?? 'BELUM ADA PLAT' }}
                        </div>
                    </div>
                </div>

                <!-- 4. Data Pelanggan & Kontak -->
                <div class="space-y-1 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-sm text-slate-900">{{ $item->customer_name }}</span>
                        <span class="text-[10px] text-slate-500 font-semibold">No KTP: {{ $item->customer_id_number }}</span>
                    </div>

                    @if($item->notes)
                        <div class="p-2 rounded-lg bg-slate-50 border border-slate-100 text-slate-600 text-[11px]">
                            <span class="font-bold text-slate-700 block text-[10px]">Catatan Tamu:</span>
                            {{ $item->notes }}
                        </div>
                    @endif
                </div>

                <!-- 5. Titik Antar & Titik Jemput -->
                <div class="space-y-2 text-xs">
                    <!-- Titik Antar (Tujuan Sekarang) -->
                    <div class="bg-emerald-50/60 p-2.5 rounded-xl border border-emerald-100 text-slate-700">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-emerald-900 text-[10px] uppercase flex items-center gap-1">
                                📍 Titik Pengantaran:
                            </span>
                            <span class="font-bold text-emerald-800 text-[11px]">{{ $item->deliveryZone?->name }}</span>
                        </div>
                        <p class="text-slate-800 text-xs font-semibold mt-1">{{ $item->delivery_address }}</p>
                    </div>

                    <!-- Info Penjemputan Nanti (Untuk referensi) -->
                    <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 text-[11px] text-slate-600 flex items-center justify-between">
                        <span>Jemput: <strong>{{ $item->returnZone?->name }}</strong> ({{ $item->end_time->format('d M, H:i') }})</span>
                        <span class="text-[10px] text-slate-500 font-medium">
                            Petugas Jemput: <strong>{{ $item->returnStaff?->name ?? 'Belum Ditugaskan' }}</strong>
                        </span>
                    </div>
                </div>

                <!-- 6. Kontak Darurat Tamu (Wajib Verifikasi) -->
                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-xs text-slate-700">
                    <span class="font-bold text-slate-800 text-[10px] uppercase block">Kontak Darurat (Wajib Verifikasi di Lokasi):</span>
                    <div class="flex items-center justify-between mt-0.5">
                        <span class="font-semibold text-slate-900">{{ $item->emergency_contact_name }} ({{ $item->emergency_contact_relation }})</span>
                        <a href="https://wa.me/{{ $cleanEmergencyPhone }}" target="_blank" class="text-emerald-700 font-bold hover:underline inline-flex items-center gap-0.5 text-[11px]">
                            <span>{{ $item->emergency_contact_phone }}</span>
                            <span class="text-[9px]">↗</span>
                        </a>
                    </div>
                </div>

                <!-- 7. Sisa Tagihan Yang Wajib Ditagih -->
                <div class="flex items-center justify-between text-xs px-3 py-2 rounded-xl {{ $item->remaining_amount > 0 ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-emerald-50 border border-emerald-200 text-emerald-900' }}">
                    <span class="font-semibold">
                        {{ $item->remaining_amount > 0 ? 'Wajib Tagih Sisa Sewa:' : 'Status Pembayaran:' }}
                    </span>
                    <span class="font-extrabold text-sm {{ $item->remaining_amount > 0 ? 'text-amber-800' : 'text-emerald-700' }}">
                        {{ $item->remaining_amount > 0 ? 'Rp ' . number_format($item->remaining_amount, 0, ',', '.') : '✓ LUNAS' }}
                    </span>
                </div>

                <!-- 8. Tombol Komunikasi: WhatsApp Tamu & Buka Google Maps -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank"
                        class="py-2 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs border border-emerald-200 flex items-center justify-center gap-1.5 transition-colors">
                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                        </svg>
                        <span>Chat Tamu</span>
                    </a>

                    <a href="{{ $mapsUrl }}" target="_blank"
                        class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-200 flex items-center justify-center gap-1.5 transition-colors">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        <span>Buka Maps</span>
                    </a>
                </div>

                <!-- 9. Tombol Aksi Lapangan (Claim / Start OTW / Handover Ceklis) -->
                <div class="pt-1">
                    @if($isUnassigned)
                        <!-- Jika Belum Ada Petugas: Tombol Ambil Tugas atau Ambil & Langsung OTW -->
                        <div class="grid grid-cols-2 gap-2">
                            <form action="{{ route('staff.tasks.claim-delivery', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 px-3 rounded-xl font-bold text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 transition-all flex items-center justify-center gap-1.5 shadow-xs">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Ambil Tugas</span>
                                </button>
                            </form>

                            <form action="{{ route('staff.tasks.start', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 px-3 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                    </svg>
                                    <span>Ambil & OTW</span>
                                </button>
                            </form>
                        </div>
                    @elseif($isMyDelivery)
                        <!-- Jika Tugas Milik User Yang Sedang Login -->
                        @if($item->status === 'approved')
                            <form action="{{ route('staff.tasks.start', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 px-4 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                    </svg>
                                    <span>Mulai Berangkat (OTW)</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('staff.tasks.handover', $item->id) }}"
                                class="w-full py-2.5 px-4 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <span>Tiba di Lokasi & Isi Ceklis Antar</span>
                            </a>
                        @endif
                    @else
                        <!-- Jika Tugas Sedang Dipegang Staf Lain: Opsi Ambil Alih Darurat -->
                        <div class="flex items-center justify-between bg-slate-50 p-2 rounded-xl border border-slate-200">
                            <span class="text-[11px] text-slate-500">Dikerjakan oleh {{ $item->deliveryStaff?->name }}</span>
                            <form action="{{ route('staff.tasks.claim-delivery', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengambil alih tugas antar ini dari {{ $item->deliveryStaff?->name }}?');">
                                @csrf
                                <button type="submit" class="py-1 px-2.5 rounded-lg text-[10px] font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition-colors">
                                    Ambil Alih Tugas
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-xs text-slate-500">
                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-lg font-bold">✓</div>
                <p class="font-bold text-slate-700 text-sm">Tidak Ada Tugas Antar</p>
                <p class="mt-1">Semua unit sudah diantarkan ke tamu atau belum ada jadwal pengantaran baru.</p>
            </div>
        @endforelse
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: UNIT SEDANG AKTIF DISEWA (ACTIVE)    -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'active'" class="space-y-3.5" style="display: none;">
        <div class="flex items-center justify-between px-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Unit di Tangan Tamu (Sedang Disewa)</span>
            <span class="text-[11px] text-slate-400 font-semibold">{{ $activeRentals->count() }} unit aktif</span>
        </div>

        @forelse($activeRentals as $item)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $item->customer_phone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
                $isReturnAssignedToMe = ($item->return_staff_id == Auth::id());
                $isReturnUnassigned = is_null($item->return_staff_id);
            @endphp
            <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <div>
                        <span class="font-extrabold text-xs text-slate-900 block font-mono">{{ $item->booking_code }}</span>
                        <span class="text-[10px] text-slate-400">
                            Diserahkan: {{ $item->handoverInspection?->inspected_at?->format('d M, H:i') ?? $item->updated_at->format('d M, H:i') }} WIB
                        </span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        Aktif Disewa Tamu
                    </span>
                </div>

                <!-- Unit & Plat Nomor -->
                <div class="bg-slate-900 text-white p-3 rounded-xl flex items-center justify-between shadow-xs">
                    <div>
                        <span class="text-[10px] text-emerald-400 uppercase font-bold block">Unit Disewa:</span>
                        <span class="font-bold text-sm text-white">{{ $item->category->name }}</span>
                    </div>
                    <div class="px-2.5 py-1 rounded bg-black border border-slate-700 text-white font-mono font-black text-xs tracking-wider">
                        {{ $item->motorcycle?->plate_number ?? '-' }}
                    </div>
                </div>

                <div>
                    <h2 class="font-extrabold text-sm text-slate-900">{{ $item->customer_name }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kontak: {{ $item->customer_phone }} • KTP: {{ $item->customer_id_number }}</p>
                </div>

                <!-- Info Jadwal & Lokasi Penjemputan -->
                <div class="bg-purple-50/60 p-2.5 rounded-xl border border-purple-100 text-xs text-slate-700 space-y-1">
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="text-purple-900 font-bold uppercase">Jadwal Penjemputan:</span>
                        <span class="font-bold text-slate-900">{{ $item->end_time->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <p class="text-slate-700 text-[11px]">Titik Jemput: <strong>{{ $item->returnZone?->name }}</strong> ({{ $item->return_address }})</p>
                </div>

                <!-- Pembagian Petugas Antar vs Jemput -->
                <div class="grid grid-cols-2 gap-2 text-[11px] bg-slate-50 p-2 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-semibold">Petugas Antar:</span>
                        <span class="font-bold text-slate-800">{{ $item->deliveryStaff?->name ?? 'Staf Rental' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block font-semibold">Petugas Jemput:</span>
                        <span class="font-bold {{ $item->returnStaff ? 'text-purple-800' : 'text-slate-500' }}">
                            {{ $item->returnStaff?->name ?? 'Belum Ditugaskan' }}
                        </span>
                    </div>
                </div>

                <!-- Tombol Action: WA & Ceklis / Klaim Jemput -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                        class="py-2 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs border border-emerald-200 flex items-center justify-center gap-1.5 transition-colors">
                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                        </svg>
                        <span>WhatsApp Tamu</span>
                    </a>

                    @if($isReturnUnassigned)
                        <form action="{{ route('staff.tasks.claim-return', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2 px-3 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-900 font-bold text-xs border border-purple-200 flex items-center justify-center gap-1 transition-colors">
                                <span>Klaim Jemput Ini</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('staff.tasks.return', $item->id) }}"
                            class="py-2 px-3 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs flex items-center justify-center gap-1 transition-colors shadow-xs">
                            <span>Form Jemput &rarr;</span>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-xs text-slate-500">
                <p class="font-bold text-slate-700 text-sm">Tidak Ada Unit Aktif</p>
                <p class="mt-1">Belum ada motor yang sedang disewa pelanggan saat ini.</p>
            </div>
        @endforelse
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: DAFTAR TUGAS JEMPUT (RETURNS)        -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'returns'" class="space-y-3.5" style="display: none;">
        <div class="flex items-center justify-between px-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jadwal Tugas Penjemputan</span>
            <span class="text-[11px] text-slate-400 font-semibold">{{ $returns->count() }} penjemputan tersedia</span>
        </div>

        @forelse($returns as $item)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $item->customer_phone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
                $cleanEmergencyPhone = preg_replace('/[^0-9]/', '', $item->emergency_contact_phone);
                if (str_starts_with($cleanEmergencyPhone, '0')) {
                    $cleanEmergencyPhone = '62' . substr($cleanEmergencyPhone, 1);
                }
                $waText = urlencode("Halo Kak {$item->customer_name}, saya dari Rental Motor Jogja. Mengingatkan hari ini jadwal pengembalian motor {$item->category->name} (Plat: ".($item->motorcycle?->plate_number ?? '-').") jam {$item->end_time->format('H:i')} WIB di {$item->return_address}. Apakah waktu dan lokasinya aman?");
                $mapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($item->return_address);

                $isMyReturn = ($item->return_staff_id == Auth::id());
                $isReturnUnassigned = is_null($item->return_staff_id);
            @endphp

            <div class="bg-white rounded-2xl border {{ $isMyReturn ? 'border-purple-300 ring-2 ring-purple-100' : ($isReturnUnassigned ? 'border-amber-200' : 'border-slate-200') }} shadow-sm p-4 space-y-3">
                <!-- 1. Header Card: Kode Booking & Status Waktu Jemput -->
                <div class="flex items-start justify-between border-b border-slate-100 pb-2.5">
                    <div>
                        <span class="font-extrabold text-xs text-purple-900 block font-mono">{{ $item->booking_code }}</span>
                        <span class="text-[11px] font-bold text-purple-800 flex items-center gap-1 mt-0.5">
                            <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Waktu Jemput: {{ $item->end_time->format('d M, H:i') }} WIB
                        </span>
                    </div>

                    <!-- Badge Status OTW / Menunggu Jemput -->
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $item->status === 'returning' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200 animate-pulse' : 'bg-purple-100 text-purple-800 border border-purple-200' }}">
                        {{ $item->status === 'returning' ? '🔄 Sedang OTW Jemput' : 'Jadwal Jemput' }}
                    </span>
                </div>

                <!-- 2. Status Penugasan Petugas Jemput -->
                <div>
                    @if($isMyReturn)
                        <div class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 text-xs font-bold">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-purple-600 animate-ping"></span>
                                ✓ Tugas Saya
                            </span>
                            <span class="text-[10px] text-purple-700 font-semibold">Siap jemput</span>
                        </div>
                    @elseif($isReturnUnassigned)
                        <div class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Belum Ada Petugas Jemput (Tersedia)
                            </span>
                            <span class="text-[10px] text-amber-700 font-semibold">Bisa diambil</span>
                        </div>
                    @else
                        <div class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs">
                            <span class="flex items-center gap-1.5 font-medium">
                                👤 Petugas Jemput: <strong class="text-slate-900">{{ $item->returnStaff?->name }}</strong>
                            </span>
                            <span class="text-[10px] text-slate-500 font-semibold">Ditugaskan</span>
                        </div>
                    @endif
                </div>

                <!-- 3. Informasi Unit Motor & Plat Nomor (Jelas & Menonjol) -->
                <div class="bg-slate-900 text-white p-3 rounded-xl space-y-1.5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-purple-300 tracking-wider">Unit & Plat Motor:</span>
                        @if($item->motorcycle?->color)
                            <span class="text-[10px] text-slate-300 font-semibold">Warna: {{ $item->motorcycle->color }}</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-sm text-white">{{ $item->category->name }}</span>
                        <div class="px-2.5 py-1 rounded bg-black border border-slate-700 text-white font-mono font-black text-xs tracking-wider shadow-inner">
                            {{ $item->motorcycle?->plate_number ?? 'BELUM ADA PLAT' }}
                        </div>
                    </div>
                </div>

                <!-- 4. Data Tamu & Petugas Antar Sebelumnya -->
                <div class="space-y-1 text-xs">
                    <div class="flex items-center justify-between">
                        <h2 class="font-extrabold text-sm text-slate-900">{{ $item->customer_name }}</h2>
                        <span class="text-[10px] text-slate-500 font-semibold">KTP: {{ $item->customer_id_number }}</span>
                    </div>

                    <!-- Keterangan Petugas Antar Sebelumnya (Bisa Berbeda) -->
                    <div class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-100 flex items-center justify-between">
                        <span>Petugas Antar Sebelumnya:</span>
                        <strong class="text-slate-800">{{ $item->deliveryStaff?->name ?? 'Petugas Rental' }}</strong>
                    </div>
                </div>

                <!-- 5. Titik Penjemputan -->
                <div class="bg-purple-50/60 p-2.5 rounded-xl border border-purple-100 text-xs text-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-purple-900 text-[10px] uppercase flex items-center gap-1">
                            📍 Titik Penjemputan:
                        </span>
                        <span class="font-bold text-purple-800 text-[11px]">{{ $item->returnZone?->name }}</span>
                    </div>
                    <p class="text-slate-800 text-xs font-semibold mt-1">{{ $item->return_address }}</p>
                </div>

                <!-- 6. Tombol Komunikasi: WhatsApp Tamu & Buka Maps -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}" target="_blank"
                        class="py-2 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs border border-emerald-200 flex items-center justify-center gap-1.5 transition-colors">
                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                        </svg>
                        <span>Chat Tamu</span>
                    </a>

                    <a href="{{ $mapsUrl }}" target="_blank"
                        class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-200 flex items-center justify-center gap-1.5 transition-colors">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        <span>Buka Maps</span>
                    </a>
                </div>

                <!-- 7. Tombol Aksi Lapangan Jemput (Claim / Start OTW Jemput / Ceklis Jemput) -->
                <div class="pt-1">
                    @if($isReturnUnassigned)
                        <!-- Jika Belum Ada Petugas Jemput: Ambil Tugas atau Ambil & OTW -->
                        <div class="grid grid-cols-2 gap-2">
                            <form action="{{ route('staff.tasks.claim-return', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 px-3 rounded-xl font-bold text-xs bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-300 transition-all flex items-center justify-center gap-1.5 shadow-xs">
                                    <svg class="w-4 h-4 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Ambil Tugas Jemput</span>
                                </button>
                            </form>

                            <form action="{{ route('staff.tasks.start-return', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 px-3 rounded-xl font-bold text-xs bg-purple-700 hover:bg-purple-800 text-white shadow-md shadow-purple-700/20 transition-all flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                    </svg>
                                    <span>Ambil & OTW Jemput</span>
                                </button>
                            </form>
                        </div>
                    @elseif($isMyReturn)
                        <!-- Jika Tugas Jemput Milik User Yang Sedang Login -->
                        @if($item->status === 'active')
                            <div class="grid grid-cols-2 gap-2">
                                <form action="{{ route('staff.tasks.start-return', $item->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 px-3 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                        </svg>
                                        <span>Mulai OTW Jemput</span>
                                    </button>
                                </form>

                                <a href="{{ route('staff.tasks.return', $item->id) }}"
                                    class="py-2.5 px-3 rounded-xl font-bold text-xs bg-purple-700 hover:bg-purple-800 text-white shadow-md shadow-purple-700/20 text-center transition-all flex items-center justify-center gap-1">
                                    <span>Isi Ceklis Jemput &rarr;</span>
                                </a>
                            </div>
                        @else
                            <a href="{{ route('staff.tasks.return', $item->id) }}"
                                class="block w-full py-2.5 px-4 rounded-xl font-bold text-xs bg-purple-700 hover:bg-purple-800 text-white shadow-md shadow-purple-700/20 text-center transition-all">
                                Tiba di Lokasi & Isi Ceklis Pengembalian &rarr;
                            </a>
                        @endif
                    @else
                        <!-- Jika Tugas Jemput Sedang Dipegang Staf Lain -->
                        <div class="flex items-center justify-between bg-slate-50 p-2 rounded-xl border border-slate-200">
                            <span class="text-[11px] text-slate-500">Dikerjakan oleh {{ $item->returnStaff?->name }}</span>
                            <form action="{{ route('staff.tasks.claim-return', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengambil alih tugas jemput ini dari {{ $item->returnStaff?->name }}?');">
                                @csrf
                                <button type="submit" class="py-1 px-2.5 rounded-lg text-[10px] font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 transition-colors">
                                    Ambil Alih Penjemputan
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-xs text-slate-500">
                <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center mx-auto mb-2 text-lg font-bold">✓</div>
                <p class="font-bold text-slate-700 text-sm">Tidak Ada Tugas Jemput</p>
                <p class="mt-1">Belum ada jadwal penjemputan unit motor saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
