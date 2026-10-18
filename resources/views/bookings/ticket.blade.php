@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <!-- Card Tiket Reservasi -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <!-- Header Gradasi Hijau -->
        <div class="bg-gradient-to-r from-emerald-800 to-teal-800 text-white p-6 sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-xs uppercase tracking-wider text-emerald-200 font-semibold">Bukti Reservasi Motor</span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1">{{ $booking->booking_code }}</h1>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3.5 py-1.5 rounded-full text-xs font-bold {{ $booking->status_badge_class }}">
                        {{ $booking->formatted_status }}
                    </span>
                    <p class="text-[11px] text-emerald-200 mt-1">Dibuat: {{ $booking->created_at->format('d M Y, H:i') }} WIB</p>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <!-- Progress Tracker / Status Pemesanan -->
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Status Pemesanan:</p>
                    <span class="text-[11px] text-slate-500 font-semibold">Tahapan Sewa</span>
                </div>

                @if($booking->status === 'rejected')
                    <!-- Box Notifikasi Penolakan Simpel & Bersih -->
                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-xs space-y-2.5">
                        <div class="flex items-center gap-2 text-rose-800 font-bold text-sm">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Reservasi Ditolak oleh Admin</span>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-rose-200">
                            <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Alasan Penolakan:</span>
                            <p class="text-slate-900 font-bold text-sm mt-0.5">{{ $booking->rejection_reason ?: 'Berkas atau bukti transfer DP tidak sesuai ketentuan verifikasi.' }}</p>
                        </div>
                        <p class="text-slate-600 text-[11px] leading-relaxed">
                            <strong>Informasi Pengembalian Dana (DP):</strong> Jika Anda telah mentransfer DP, dana Anda aman dan akan diproses refund. Silakan hubungi admin via WhatsApp untuk konfirmasi rekening pengembalian dana atau reservasi ulang.
                        </p>
                    </div>
                @else
                    <!-- 6 Steps Grid (Menyertakan Proses Penjemputan) -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-center text-xs">
                        <!-- Step 1: DP -->
                        <div class="p-2.5 sm:p-3 rounded-xl border flex flex-col justify-between {{ in_array($booking->status, ['pending', 'approved', 'delivering', 'active', 'returning', 'completed']) ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-slate-100 border-slate-200 text-slate-400' }}">
                            <div>
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] font-bold inline-flex items-center justify-center mb-1">1</span>
                                <p class="font-bold text-[11px]">Verifikasi DP</p>
                            </div>
                            <p class="text-[10px] text-emerald-700 font-semibold mt-2 pt-1 border-t border-emerald-200/60">
                                {{ $booking->created_at->format('d M, H:i') }} WIB
                            </p>
                        </div>

                        <!-- Step 2: Disetujui -->
                        <div class="p-2.5 sm:p-3 rounded-xl border flex flex-col justify-between {{ in_array($booking->status, ['approved', 'delivering', 'active', 'returning', 'completed']) ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-slate-100 border-slate-200 text-slate-400' }}">
                            <div>
                                <span class="w-5 h-5 rounded-full {{ in_array($booking->status, ['approved', 'delivering', 'active', 'returning', 'completed']) ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600' }} text-[10px] font-bold inline-flex items-center justify-center mb-1">2</span>
                                <p class="font-bold text-[11px]">Disetujui</p>
                            </div>
                            <p class="text-[10px] {{ in_array($booking->status, ['approved', 'delivering', 'active', 'returning', 'completed']) ? 'text-emerald-700 font-semibold' : 'text-slate-400' }} mt-2 pt-1 border-t border-slate-200">
                                @if(in_array($booking->status, ['approved', 'delivering', 'active', 'returning', 'completed']))
                                    {{ $booking->handoverInspection ? $booking->created_at->format('d M, H:i') : $booking->updated_at->format('d M, H:i') }} WIB
                                @else
                                    Menunggu
                                @endif
                            </p>
                        </div>

                        <!-- Step 3: Diantar -->
                        <div class="p-2.5 sm:p-3 rounded-xl border flex flex-col justify-between {{ in_array($booking->status, ['delivering', 'active', 'returning', 'completed']) ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-slate-100 border-slate-200 text-slate-400' }}">
                            <div>
                                <span class="w-5 h-5 rounded-full {{ in_array($booking->status, ['delivering', 'active', 'returning', 'completed']) ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600' }} text-[10px] font-bold inline-flex items-center justify-center mb-1">3</span>
                                <p class="font-bold text-[11px]">Diantar (OTW)</p>
                            </div>
                            <p class="text-[10px] {{ in_array($booking->status, ['delivering', 'active', 'returning', 'completed']) ? 'text-emerald-700 font-semibold' : 'text-slate-400' }} mt-2 pt-1 border-t border-slate-200">
                                @if(in_array($booking->status, ['delivering', 'active', 'returning', 'completed']))
                                    {{ $booking->start_time->format('d M, H:i') }} WIB
                                @else
                                    Menunggu
                                @endif
                            </p>
                        </div>

                        <!-- Step 4: Aktif Digunakan -->
                        <div class="p-2.5 sm:p-3 rounded-xl border flex flex-col justify-between {{ in_array($booking->status, ['active', 'returning', 'completed']) ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-slate-100 border-slate-200 text-slate-400' }}">
                            <div>
                                <span class="w-5 h-5 rounded-full {{ in_array($booking->status, ['active', 'returning', 'completed']) ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600' }} text-[10px] font-bold inline-flex items-center justify-center mb-1">4</span>
                                <p class="font-bold text-[11px]">Aktif Digunakan</p>
                            </div>
                            <p class="text-[10px] {{ in_array($booking->status, ['active', 'returning', 'completed']) ? 'text-emerald-700 font-semibold' : 'text-slate-400' }} mt-2 pt-1 border-t border-slate-200">
                                @if($booking->handoverInspection)
                                    {{ $booking->handoverInspection->inspected_at->format('d M, H:i') }} WIB
                                @elseif(in_array($booking->status, ['active', 'returning', 'completed']))
                                    {{ $booking->start_time->format('d M, H:i') }} WIB
                                @else
                                    Menunggu
                                @endif
                            </p>
                        </div>

                        <!-- Step 5: Proses Penjemputan -->
                        <div class="p-2.5 sm:p-3 rounded-xl border flex flex-col justify-between {{ in_array($booking->status, ['returning', 'completed']) ? ($booking->status === 'returning' ? 'bg-purple-50 border-purple-400 text-purple-950 ring-2 ring-purple-400/30 shadow-xs' : 'bg-emerald-50 border-emerald-300 text-emerald-900') : 'bg-slate-100 border-slate-200 text-slate-400' }}">
                            <div>
                                <span class="w-5 h-5 rounded-full {{ $booking->status === 'returning' ? 'bg-purple-600 text-white animate-pulse' : (in_array($booking->status, ['completed']) ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600') }} text-[10px] font-bold inline-flex items-center justify-center mb-1">5</span>
                                <p class="font-bold text-[11px]">Penjemputan</p>
                            </div>
                            <p class="text-[10px] {{ $booking->status === 'returning' ? 'text-purple-700 font-bold' : (in_array($booking->status, ['completed']) ? 'text-emerald-700 font-semibold' : 'text-slate-400') }} mt-2 pt-1 border-t border-slate-200">
                                @if($booking->status === 'returning')
                                    Kurir OTW Jemput
                                @elseif($booking->returnInspection)
                                    {{ $booking->returnInspection->inspected_at->format('d M, H:i') }} WIB
                                @elseif($booking->status === 'completed')
                                    Telah Dijemput
                                @else
                                    {{ $booking->end_time->format('d M, H:i') }} WIB
                                @endif
                            </p>
                        </div>

                        <!-- Step 6: Selesai / Dikembalikan -->
                        <div class="p-2.5 sm:p-3 rounded-xl border flex flex-col justify-between {{ $booking->status === 'completed' ? 'bg-emerald-600 border-emerald-700 text-white shadow-sm' : 'bg-slate-100 border-slate-200 text-slate-400' }}">
                            <div>
                                <span class="w-5 h-5 rounded-full {{ $booking->status === 'completed' ? 'bg-white text-emerald-800' : 'bg-slate-300 text-slate-600' }} text-[10px] font-bold inline-flex items-center justify-center mb-1">6</span>
                                <p class="font-bold text-[11px]">Selesai (Kembali)</p>
                            </div>
                            <p class="text-[10px] {{ $booking->status === 'completed' ? 'text-emerald-100 font-bold' : 'text-slate-400' }} mt-2 pt-1 border-t border-slate-200/60">
                                @if($booking->status === 'completed')
                                    Tuntas
                                @else
                                    Menunggu
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Banner Keterangan Status Real-Time -->
                    <div class="mt-4 pt-3 border-t border-slate-200/80">
                        @if($booking->status === 'completed')
                            <div class="bg-emerald-100/90 border border-emerald-300 text-emerald-950 p-3.5 rounded-xl text-xs space-y-1">
                                <p class="font-black flex items-center gap-1.5 text-sm text-emerald-900">
                                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Sewa Telah Selesai & Unit Telah Kembali
                                </p>
                                <p class="text-[11px] leading-relaxed text-emerald-800">
                                    Unit motor telah diserah-terimakan kembali oleh tamu pada 
                                    <strong>{{ $booking->returnInspection?->inspected_at?->format('d M Y, H:i') ?? $booking->updated_at->format('d M Y, H:i') }} WIB</strong>. 
                                    Berkas identitas/jaminan fisik telah diserahkan kembali dan seluruh proses sewa telah tuntas. Terima kasih telah menyewa di JogjaMotor!
                                </p>
                            </div>
                        @elseif($booking->status === 'returning')
                            <!-- BANNER PROSES PENJEMPUTAN AKTIF -->
                            <div class="bg-purple-50 border border-purple-200 text-purple-900 p-4 rounded-xl text-xs space-y-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="relative flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-purple-600"></span>
                                    </span>
                                    <p class="font-black text-sm text-purple-950">
                                        Petugas Kurir Sedang Menuju Lokasi Anda untuk Penjemputan Unit
                                    </p>
                                </div>
                                <p class="text-[11px] text-purple-800 leading-relaxed">
                                    Kurir kami (<strong>{{ $booking->returnStaff?->name ?? 'Petugas Lapangan' }}</strong>) sedang dalam perjalanan menuju lokasi penjemputan di <strong>{{ $booking->returnZone?->name }}</strong> ({{ $booking->return_address }}).
                                </p>
                                <div class="bg-white/80 p-3 rounded-lg border border-purple-200 text-[11px] text-purple-900 space-y-1">
                                    <p class="font-bold flex items-center gap-1.5 text-purple-950">
                                        <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Persiapan Pengembalian Unit:
                                    </p>
                                    <ul class="list-disc list-inside space-y-0.5 text-purple-800 pl-1">
                                        <li>Siapkan unit motor, kunci kontak, dan STNK asli.</li>
                                        <li>Siapkan helm ({{ $booking->handoverInspection?->helmets_count ?? 2 }} buah) & jas hujan.</li>
                                        <li><strong>KTP fisik asli jaminan Anda</strong> akan diserahkan kembali oleh kurir setelah pemeriksaan unit selesai.</li>
                                    </ul>
                                </div>
                            </div>
                        @elseif($booking->status === 'active')
                            <div class="bg-blue-50 border border-blue-200 text-blue-900 p-3.5 rounded-xl text-xs space-y-2">
                                <p class="font-black flex items-center gap-1.5 text-sm text-blue-900">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-pulse"></span>
                                    Unit Motor Sedang Aktif Digunakan Tamu
                                </p>
                                <p class="text-[11px] text-blue-800">
                                    Serah terima unit telah selesai dilakukan pada 
                                    <strong>{{ $booking->handoverInspection?->inspected_at?->format('d M Y, H:i') ?? $booking->start_time->format('d M Y, H:i') }} WIB</strong>.
                                </p>
                                <div class="bg-white/80 p-2.5 rounded-lg border border-blue-200 text-[11px] text-blue-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
                                    <div>
                                        <span class="font-bold block">Jadwal Pengembalian / Penjemputan:</span>
                                        <span><strong>{{ $booking->end_time->format('d M Y, H:i') }} WIB</strong> di <strong>{{ $booking->returnZone?->name }}</strong></span>
                                    </div>
                                    @if($booking->returnStaff)
                                        <span class="text-xs text-blue-800 font-semibold bg-blue-100/70 px-2.5 py-1 rounded-md shrink-0">
                                            Kurir Penjemput: {{ $booking->returnStaff->name }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-500 italic shrink-0">
                                            Kurir penjemput akan menghubungi saat waktu sewa habis
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @elseif($booking->status === 'delivering')
                            <div class="bg-indigo-50 border border-indigo-200 text-indigo-900 p-3.5 rounded-xl text-xs space-y-1">
                                <p class="font-black flex items-center gap-1.5 text-sm text-indigo-900">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-ping"></span>
                                    Kurir Sedang Dalam Perjalanan Mengantar Unit
                                </p>
                                <p class="text-[11px] text-indigo-800">
                                    Petugas kami (<strong>{{ $booking->deliveryStaff?->name ?? 'Kurir' }}</strong>) sedang dalam perjalanan mengantar motor ke lokasi Anda.
                                </p>
                            </div>
                        @elseif($booking->status === 'approved')
                            <div class="bg-blue-50 border border-blue-200 text-blue-900 p-3.5 rounded-xl text-xs space-y-1">
                                <p class="font-black text-sm text-blue-900">✓ Reservasi Telah Disetujui</p>
                                <p class="text-[11px] text-blue-800">
                                    Motor plat <strong>{{ $booking->motorcycle?->plate_number }}</strong> telah disiapkan dan akan diantar sesuai jadwal pada <strong>{{ $booking->start_time->format('d M Y, H:i') }} WIB</strong>. Mohon siapkan berkas identitas/jaminan fisik untuk diserahkan kepada kurir saat serah terima unit.
                                </p>
                            </div>
                        @elseif($booking->status === 'pending')
                            <div class="bg-amber-50 border border-amber-200 text-amber-900 p-3.5 rounded-xl text-xs space-y-1">
                                <p class="font-black text-sm text-amber-900">⏳ Pembayaran DP Sedang Diverifikasi</p>
                                <p class="text-[11px] text-amber-800">
                                    Bukti pembayaran DP Anda sedang diperiksa oleh tim kami. Halaman ini akan diperbarui otomatis setelah pesanan disetujui.
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Detail Motor & Kurir Pengantar / Penjemput -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl border border-slate-200 flex items-start gap-3.5">
                    @if($booking->category->image_thumbnail_url ?? $booking->category->image_url)
                        <div class="w-20 h-16 bg-slate-50 border border-slate-100 rounded-lg p-1 flex items-center justify-center shrink-0">
                            <img src="{{ $booking->category->image_thumbnail_url ?? $booking->category->image_url }}" alt="{{ $booking->category->name }}" class="max-h-full max-w-full object-contain">
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pilihan Motor:</span>
                        <h3 class="font-extrabold text-base text-slate-900 mt-0.5 truncate">{{ $booking->category->name }}</h3>
                        @if($booking->motorcycle)
                            <div class="mt-2 text-xs text-slate-600 space-y-1">
                                <span class="text-emerald-800 font-bold text-xs bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md inline-block">Plat: {{ $booking->motorcycle->plate_number }}</span>
                                <p class="text-slate-500">Warna: {{ $booking->motorcycle->color ?? '-' }}</p>
                            </div>
                        @else
                            <p class="text-[11px] text-slate-400 mt-1">{{ $booking->category->cc_capacity }} cc • Unit fisik dialokasikan saat disetujui</p>
                        @endif
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-slate-200 space-y-3">
                    <!-- Kurir Pengantar -->
                    <div class="{{ in_array($booking->status, ['returning', 'completed']) ? 'opacity-75 text-xs' : '' }}">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Kurir Pengantar:</span>
                        @if($booking->deliveryStaff)
                            <div class="flex items-center justify-between gap-2 mt-1">
                                <h3 class="font-extrabold {{ in_array($booking->status, ['returning', 'completed']) ? 'text-xs' : 'text-base' }} text-slate-900">{{ $booking->deliveryStaff->name }}</h3>
                                @if(!in_array($booking->status, ['returning', 'completed']))
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->deliveryStaff->phone) }}" target="_blank" class="text-xs text-emerald-700 font-bold hover:underline shrink-0">
                                        WhatsApp &rarr;
                                    </a>
                                @endif
                            </div>
                        @elseif($booking->status === 'rejected')
                            <h3 class="font-bold text-xs text-slate-400 mt-1">-</h3>
                        @else
                            <h3 class="font-semibold text-xs text-slate-500 mt-1">Akan diinfokan saat pengantaran</h3>
                        @endif
                    </div>

                    <!-- Kurir Penjemput (Tampil saat Aktif, Penjemputan, atau Selesai) -->
                    @if(in_array($booking->status, ['active', 'returning', 'completed']))
                        <div class="pt-2.5 border-t border-slate-100 {{ $booking->status === 'returning' ? 'bg-purple-50/60 -mx-2 px-2.5 py-2 rounded-lg border border-purple-200' : '' }}">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[11px] font-bold {{ $booking->status === 'returning' ? 'text-purple-800' : 'text-slate-400' }} uppercase tracking-wider">
                                    Kurir Penjemput:
                                </span>
                                @if($booking->status === 'returning')
                                    <span class="text-[10px] font-extrabold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full animate-pulse">Sedang Menuju Lokasi</span>
                                @endif
                            </div>
                            @if($booking->returnStaff)
                                <div class="flex items-center justify-between gap-2 mt-1">
                                    <h3 class="font-extrabold text-slate-900 text-sm">{{ $booking->returnStaff->name }}</h3>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->returnStaff->phone) }}?text=Halo%20{{ urlencode($booking->returnStaff->name) }},%20saya%20tamu%20booking%20{{ $booking->booking_code }}%20terkait%20penjemputan%20motor." target="_blank" class="text-xs text-purple-700 hover:text-purple-900 font-bold hover:underline shrink-0">
                                        WhatsApp &rarr;
                                    </a>
                                </div>
                            @elseif($booking->status === 'returning')
                                <h3 class="font-semibold text-xs text-purple-800 mt-1">Kurir sedang dialokasikan oleh tim operasional</h3>
                            @else
                                <h3 class="font-semibold text-xs text-slate-500 mt-1">Akan dihubungi saat masa sewa berakhir</h3>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Jadwal Antar & Jemput -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-xl border {{ $booking->status === 'delivering' ? 'bg-indigo-50/60 border-indigo-200 ring-2 ring-indigo-500/20' : 'bg-slate-50 border-slate-100' }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold text-emerald-800 uppercase text-[11px]">Jadwal & Lokasi Antar:</span>
                        @if($booking->status === 'delivering')
                            <span class="text-[10px] font-bold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded-full">Dalam Pengantaran</span>
                        @endif
                    </div>
                    <p class="font-extrabold text-sm text-slate-900 mt-1">{{ $booking->start_time->format('d M Y, H:i') }} WIB</p>
                    <p class="text-slate-600 mt-1"><strong>{{ $booking->deliveryZone?->name }}</strong></p>
                    <p class="text-slate-500 mt-0.5">{{ $booking->delivery_address }}</p>
                </div>

                <div class="p-4 rounded-xl border {{ $booking->status === 'returning' ? 'bg-purple-50/70 border-purple-300 ring-2 ring-purple-500/20' : 'bg-slate-50 border-slate-100' }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold {{ $booking->status === 'returning' ? 'text-purple-900' : 'text-emerald-800' }} uppercase text-[11px]">Jadwal & Lokasi Jemput:</span>
                        @if($booking->status === 'returning')
                            <span class="text-[10px] font-extrabold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full animate-pulse">Titik Jemput Aktif</span>
                        @endif
                    </div>
                    <p class="font-extrabold text-sm text-slate-900 mt-1">{{ $booking->end_time->format('d M Y, H:i') }} WIB</p>
                    <p class="text-slate-600 mt-1"><strong>{{ $booking->returnZone?->name }}</strong></p>
                    <p class="text-slate-500 mt-0.5">{{ $booking->return_address }}</p>
                </div>
            </div>

            <!-- Riwayat Ceklis Serah-Terima & Pengembalian Fisik -->
            @if($booking->handoverInspection || $booking->returnInspection)
                <div class="bg-white p-4 rounded-xl border border-slate-200 text-xs space-y-3">
                    <span class="font-extrabold text-slate-800 uppercase text-[11px] block">Catatan Pemeriksaan Unit:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if($booking->handoverInspection)
                            <div class="bg-emerald-50/70 p-3 rounded-lg border border-emerald-100 space-y-1">
                                <span class="font-bold text-emerald-900 text-[11px] block">Waktu Serah Terima (Antar):</span>
                                <p class="text-slate-700 font-semibold">{{ $booking->handoverInspection->inspected_at->format('d M Y, H:i') }} WIB</p>
                                <p class="text-slate-500 text-[11px]">Helm: {{ $booking->handoverInspection->helmets_count }} • Bensin: {{ $booking->handoverInspection->fuel_level }} • STNK: Lengkap</p>
                                <p class="text-emerald-800 font-bold text-[11px]">✓ Jaminan KTP Fisik Ditahan</p>
                                @if($booking->handoverInspection->emergency_contact_verified)
                                    <p class="text-emerald-800 font-bold text-[11px]">✓ Kontak Darurat Terkonfirmasi</p>
                                @endif
                            </div>
                        @endif

                        @if($booking->returnInspection)
                            <div class="bg-purple-50/70 p-3 rounded-lg border border-purple-100 space-y-1">
                                <span class="font-bold text-purple-900 text-[11px] block">Waktu Pengembalian (Jemput):</span>
                                <p class="text-slate-700 font-semibold">{{ $booking->returnInspection->inspected_at->format('d M Y, H:i') }} WIB</p>
                                <p class="text-slate-500 text-[11px]">Helm: {{ $booking->returnInspection->helmets_count }} • Bensin: {{ $booking->returnInspection->fuel_level }}</p>
                                <p class="text-purple-800 font-bold text-[11px]">✓ KTP Fisik Telah Dikembalikan</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Rincian Biaya -->
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 space-y-2.5 text-xs">
                <span class="text-[11px] font-extrabold text-slate-800 uppercase tracking-wider block border-b border-slate-200 pb-2">
                    Rincian Biaya Sewa
                </span>
                <div class="flex justify-between text-slate-600">
                    <span>Durasi Sewa:</span>
                    <span class="font-bold text-slate-800">{{ $booking->total_days }} Hari {{ $booking->overtime_hours > 0 ? '(+ '.$booking->overtime_hours.' Jam Overtime)' : '' }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Sewa Motor:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($booking->rental_subtotal + $booking->overtime_fee, 0, ',', '.') }}</span>
                </div>

                @if(!empty($booking->pricing_breakdown) && is_array($booking->pricing_breakdown) && count($booking->pricing_breakdown) > 0)
                    <div x-data="{ openBreakdown: false }" class="bg-white rounded-xl p-2.5 border border-slate-200 shadow-2xs">
                        <button type="button" @click="openBreakdown = !openBreakdown" class="w-full flex items-center justify-between text-[11px] font-semibold text-slate-700 hover:text-emerald-700 focus:outline-none">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Rincian Tarif Kalender ({{ count($booking->pricing_breakdown) }} Hari)</span>
                            </span>
                            <span class="text-[10px] text-emerald-700 flex items-center gap-0.5 shrink-0">
                                <span x-text="openBreakdown ? 'Tutup' : 'Lihat Rincian'"></span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="openBreakdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                        <div x-show="openBreakdown" x-cloak class="mt-2 pt-2 border-t border-slate-100 space-y-1.5 text-[11px]">
                            @foreach($booking->pricing_breakdown as $item)
                                <div class="flex items-center justify-between text-slate-600">
                                    <div>
                                        <span class="font-medium text-slate-800">{{ $item['day_name'] ?? '' }}, {{ $item['date'] ?? '' }}</span>
                                        <span class="text-[10px] text-slate-400 block sm:inline sm:ml-1">({{ $item['reason'] ?? '' }})</span>
                                    </div>
                                    <span class="font-semibold text-slate-800">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                            @if($booking->overtime_hours > 0)
                                <div class="flex items-center justify-between text-amber-700 pt-1 border-t border-slate-100">
                                    <span>Overtime ({{ $booking->overtime_hours }} jam)</span>
                                    <span class="font-semibold">Rp {{ number_format($booking->overtime_fee, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="flex justify-between text-slate-600">
                    <span>Biaya Antar & Jemput:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($booking->delivery_fee + $booking->return_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-900 font-extrabold pt-2.5 border-t border-slate-200 text-sm">
                    <span>Total Biaya:</span>
                    <span class="text-emerald-700">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>DP yang Sudah Ditransfer:</span>
                    <span class="text-emerald-700 font-bold">- Rp {{ number_format($booking->down_payment_amount, 0, ',', '.') }}</span>
                </div>
                @if($booking->status === 'completed' || $booking->payment_status === 'paid_full' || $booking->remaining_amount == 0)
                    @php
                        $pelunasanAmount = $booking->handoverInspection?->payment_collected_amount ?? max(0, $booking->total_amount - $booking->down_payment_amount);
                    @endphp
                    @if($pelunasanAmount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Pelunasan (Serah Terima):</span>
                            <span class="text-emerald-700 font-bold">- Rp {{ number_format($pelunasanAmount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                @endif
                <div class="flex justify-between items-center pt-2.5 border-t border-slate-200 font-extrabold text-xs sm:text-sm">
                    <span class="text-slate-800">Status Pelunasan Sisa:</span>
                    <span>
                        @if($booking->status === 'rejected')
                            <span class="text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-md text-xs font-bold inline-block">
                                ✗ DIBATALKAN (Ditolak)
                            </span>
                        @elseif($booking->status === 'completed' || $booking->payment_status === 'paid_full' || $booking->remaining_amount == 0)
                            <span class="text-emerald-800 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-md text-xs font-bold inline-block">
                                ✓ LUNAS
                            </span>
                        @else
                            <span class="text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-md text-xs font-bold inline-block">
                                Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }} (Saat Serah Terima)
                            </span>
                        @endif
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-2.5 pt-2">
                <a href="{{ route('home') }}" class="flex-1 py-3 px-4 text-center rounded-xl font-bold text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                    &larr; {{ $booking->status === 'rejected' ? 'Reservasi Ulang' : 'Kembali ke Beranda' }}
                </a>

                @if($booking->status === 'returning' && $booking->returnStaff)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->returnStaff->phone) }}?text=Halo%20{{ urlencode($booking->returnStaff->name) }},%20saya%20tamu%20booking%20{{ $booking->booking_code }}%20terkait%20penjemputan%20motor." target="_blank"
                        class="flex-1 py-3 px-4 text-center rounded-xl font-bold text-xs bg-purple-600 hover:bg-purple-700 text-white shadow-md shadow-purple-600/20 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                        </svg>
                        <span>Hubungi Kurir Penjemput (WA)</span>
                    </a>
                @elseif($booking->status === 'delivering' && $booking->deliveryStaff)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->deliveryStaff->phone) }}?text=Halo%20{{ urlencode($booking->deliveryStaff->name) }},%20saya%20tamu%20booking%20{{ $booking->booking_code }}%20terkait%20pengantaran%20motor." target="_blank"
                        class="flex-1 py-3 px-4 text-center rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                        </svg>
                        <span>Hubungi Kurir Pengantar (WA)</span>
                    </a>
                @endif

                <a href="https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20konfirmasi%20terkait%20pesanan%20dengan%20kode%20booking%20{{ $booking->booking_code }}{{ $booking->status === 'rejected' ? '%20yang%20ditolak' : '' }}" target="_blank"
                    class="flex-1 py-3 px-4 text-center rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                    </svg>
                    <span>Hubungi CS / Admin (WA)</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    try {
        localStorage.setItem('jogja_recent_booking', JSON.stringify({
            code: '{{ $booking->booking_code }}',
            unit: '{{ $booking->category->name }}'
        }));
    } catch (e) {}
</script>
@endsection
