@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    previewImage: null,
    previewTitle: '',
    showRejectModal: false,
    showCancelModal: false,
    showRescheduleModal: false,
    showDpModal: false,
    inputDpAmount: {{ (float) $booking->down_payment_amount }},
    totalBookingAmount: {{ (float) $booking->total_amount }},
    calculatePendingRemaining() {
        let dp = parseFloat(this.inputDpAmount) || 0;
        return Math.max(0, this.totalBookingAmount - dp);
    },
    rescheduleStartTime: '{{ $booking->start_time->format('Y-m-d\TH:i') }}',
    rescheduleEndTime: '{{ $booking->end_time->format('Y-m-d\TH:i') }}',
    rescheduleCategoryId: '{{ $booking->category_id }}',
    rescheduleMotorId: '{{ $booking->motorcycle_id }}',
    categories: {{ Js::from($categoriesJson) }},
    deliveryFee: {{ (float) $booking->delivery_fee }},
    returnFee: {{ (float) $booking->return_fee }},
    dpAmount: {{ (float) $booking->down_payment_amount }},
    rescheduleCalculator() {
        let start = new Date(this.rescheduleStartTime);
        let end = new Date(this.rescheduleEndTime);
        if (isNaN(start) || isNaN(end) || end <= start) {
            return { isValid: false, days: 0, overtimeHours: 0, rentalSubtotal: 0, overtimeFee: 0, totalAmount: 0, remainingAmount: 0 };
        }
        let diffMinutes = (end - start) / (1000 * 60);
        let totalHours = diffMinutes / 60;
        let days = 1;
        let overtimeHours = 0;
        if (totalHours <= 24) {
            days = 1;
            overtimeHours = 0;
        } else {
            days = Math.floor(totalHours / 24);
            let remainingHours = totalHours - (days * 24);
            if (remainingHours <= 0.5) {
                overtimeHours = 0;
            } else if (remainingHours <= 4) {
                overtimeHours = Math.ceil(remainingHours);
            } else {
                days += 1;
                overtimeHours = 0;
            }
        }
        days = Math.max(1, days);
        let cat = this.categories.find(c => c.id == this.rescheduleCategoryId) || this.categories[0];
        let dailyPrice = cat ? cat.daily_price : 0;
        let hourlyPrice = cat ? cat.hourly_overtime_price : 0;
        let rentalSubtotal = dailyPrice * days;
        let overtimeFee = hourlyPrice * overtimeHours;
        let totalAmount = rentalSubtotal + overtimeFee + this.deliveryFee + this.returnFee;
        let remainingAmount = Math.max(0, totalAmount - this.dpAmount);
        return {
            isValid: true,
            days: days,
            overtimeHours: overtimeHours,
            dailyPrice: dailyPrice,
            rentalSubtotal: rentalSubtotal,
            overtimeFee: overtimeFee,
            totalAmount: totalAmount,
            remainingAmount: remainingAmount
        };
    }
}">
    <!-- Header Details -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.bookings.index') }}" class="text-xs text-slate-400 hover:text-slate-700 font-bold">&larr; Kembali</a>
                <span class="text-slate-300">|</span>
                <span class="text-xs font-bold text-slate-500">Detail Reservasi</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-1">{{ $booking->booking_code }}</h1>
            <p class="text-xs text-slate-400">Masuk pada: {{ $booking->created_at->format('d M Y, H:i') }} WIB</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($booking->category->image_url)
                <div class="w-14 h-11 bg-slate-50 border border-slate-200 rounded-lg p-1 flex items-center justify-center shrink-0">
                    <img src="{{ $booking->category->image_url }}" alt="{{ $booking->category->name }}" class="max-h-full max-w-full object-contain">
                </div>
            @endif
            <div class="text-left sm:text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Tipe Motor Dipesan:</span>
                <span class="text-xs font-black text-slate-800">{{ $booking->category->name }}</span>
            </div>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold border {{ $booking->status_badge_class }}">
                Status: {{ $booking->formatted_status }}
            </span>
        </div>
    </div>

    <!-- Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Verifikasi & Aksi Admin (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- PANEL KELOLA RESERVASI (SIMPEL & ELEGAN) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold text-slate-900">
                        Kelola Status & Tindakan
                    </h2>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $booking->status_badge_class }}">
                        {{ $booking->formatted_status }}
                    </span>
                </div>

                @if($booking->status === 'pending')
                    <!-- KASUS 1: RESERVASI BARU (PENDING VERIFIKASI) -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                        <div>
                            <span class="text-xs font-bold text-slate-900 block">Reservasi Baru Menunggu Keputusan Admin</span>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Periksa bukti transfer DP di bawah. Tentukan unit motor lalu setujui reservasi.
                            </p>
                        </div>

                        @if($availableUnitsCount <= 0)
                            <div class="p-3 bg-amber-50 border border-amber-300 rounded-xl text-amber-950 text-xs flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="space-y-0.5">
                                    <strong class="font-bold block text-amber-900">Perhatian: Semua Unit Motor Sudah Penuh!</strong>
                                    <p class="text-amber-800">
                                        Seluruh unit {{ $booking->category->name }} ({{ $motorcycles->count() }} unit) sudah memiliki jadwal sewa aktif pada rentang tanggal/jam ini. Anda dapat menolak reservasi ini atau koordinasi via WA untuk ganti jadwal/tipe motor.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('admin.bookings.approve', $booking->id) }}" method="POST" class="space-y-3 pt-2 border-t border-slate-200">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">
                                        Pilih Unit Motor:
                                    </label>
                                    <select name="motorcycle_id" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                                        <option value="">-- Pilih Unit Motor --</option>
                                        @foreach($motorcycles as $m)
                                            @if($m->has_conflict)
                                                <option value="{{ $m->id }}" disabled class="text-slate-400 bg-slate-100">
                                                    ⛔ {{ $m->plate_number }} ({{ $m->brand }} {{ $m->color ?? '' }}) — Penuh (Booking {{ $m->conflict_booking?->booking_code }})
                                                </option>
                                            @else
                                                <option value="{{ $m->id }}" {{ $booking->motorcycle_id == $m->id ? 'selected' : '' }}>
                                                    ✓ {{ $m->plate_number }} ({{ $m->brand }} {{ $m->color ?? '' }}) — Tersedia
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block font-semibold text-slate-700">
                                            Nominal DP Masuk (Rp):
                                        </label>
                                        <span class="text-[10px] text-slate-400">Total: Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                                    </div>
                                    <input type="number" name="down_payment_amount" value="{{ (float)$booking->down_payment_amount }}" min="0" max="{{ (float)$booking->total_amount }}" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white font-bold text-slate-900">
                                </div>
                            </div>

                            <div>
                                <input type="text" name="dp_correction_note" placeholder="Catatan kesepakatan DP (opsional, contoh: Tamu transfer 10rb dan setuju lanjut via WA)" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs bg-white text-slate-700">
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-2">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="showRejectModal = true" class="py-2 px-3.5 rounded-xl border border-slate-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition-colors">
                                        Tolak Reservasi
                                    </button>
                                    @if($availableUnitsCount <= 0)
                                        <button type="button" @click="showRescheduleModal = true" class="py-2 px-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors">
                                            Ganti Motor / Hari Lain
                                        </button>
                                    @endif
                                </div>

                                @if($availableUnitsCount <= 0)
                                    <button type="button" disabled class="py-2.5 px-5 rounded-xl bg-slate-300 text-slate-500 font-bold text-xs cursor-not-allowed" title="Unit motor pada jadwal ini sudah penuh">
                                        Unit Penuh (Tidak Bisa Konfirmasi)
                                    </button>
                                @else
                                    <button type="submit" class="py-2.5 px-5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-colors">
                                        Konfirmasi Reservasi
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>

                @elseif($booking->status === 'rejected')
                    <!-- KASUS 2: STATUS DITOLAK (REJECTED) -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">Reservasi Sedang Dalam Status Ditolak</span>
                                <p class="text-xs text-slate-600 mt-1">
                                    Alasan: <span class="font-semibold text-slate-800">"{{ $booking->rejection_reason ?: 'Tidak ada alasan spesifik tercatat' }}"</span>
                                </p>
                            </div>
                            <button type="button" @click="showRejectModal = true" class="text-xs text-slate-500 hover:text-slate-800 underline">
                                Ubah Alasan
                            </button>
                        </div>

                        <p class="text-xs text-slate-500">
                            Jika penyewa minta jadwal atau motor pengganti via WhatsApp, Anda dapat menjadwalkan ulang dan menyetujui kembali reservasi ini.
                        </p>

                        <div class="pt-1">
                            <button type="button" @click="showRescheduleModal = true" class="py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-colors">
                                Revisi Jadwal & Buka Kembali (Setujui)
                            </button>
                        </div>
                    </div>

                @elseif($booking->status === 'approved')
                    <!-- KASUS 3: STATUS DISETUJUI (APPROVED / SIAP ANTAR) -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                            <div>
                                <span class="font-bold text-slate-900 block">Reservasi Siap Diantar Petugas</span>
                                <span class="text-slate-500">Unit Terpasang: <strong class="text-slate-800">{{ $booking->motorcycle?->plate_number ?? '-' }}</strong></span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-500">Sisa Tagihan Petugas:</span>
                                <span class="font-bold text-slate-900 ml-1">Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-200 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-600">DP Terkonfirmasi: <strong class="text-slate-900">Rp {{ number_format($booking->down_payment_amount, 0, ',', '.') }}</strong></span>
                                <button type="button" @click="showDpModal = true" class="text-xs text-slate-600 hover:text-slate-900 underline font-medium">
                                    Koreksi DP
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="showCancelModal = true" class="text-xs text-rose-600 hover:text-rose-700 font-medium">
                                    Batalkan
                                </button>
                                <button type="button" @click="showRescheduleModal = true" class="py-1.5 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-colors">
                                    Ubah Hari / Ganti Unit Motor
                                </button>
                            </div>
                        </div>
                    </div>

                @elseif(in_array($booking->status, ['delivering', 'active', 'returning']))
                    <!-- KASUS 4: OPERASIONAL LAPANGAN SEDANG BERJALAN -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                            <div>
                                <span class="font-bold text-slate-900 block">Sewa Sedang Berjalan ({{ $booking->formatted_status }})</span>
                                <span class="text-slate-500">Unit: <strong class="text-slate-800">{{ $booking->motorcycle?->plate_number ?? '-' }}</strong></span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-500">Pelunasan:</span>
                                @if($booking->remaining_amount <= 0)
                                    <span class="font-bold text-slate-900 ml-1">✓ Lunas Penuh (Serah Terima)</span>
                                @else
                                    <span class="font-bold text-slate-900 ml-1">Sisa Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-200 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-600">DP Awal: <strong class="text-slate-900">Rp {{ number_format($booking->down_payment_amount, 0, ',', '.') }}</strong></span>
                                @if($booking->status === 'delivering')
                                    <button type="button" @click="showDpModal = true" class="text-xs text-slate-600 hover:text-slate-900 underline font-medium">
                                        Koreksi DP
                                    </button>
                                @endif
                            </div>

                            <button type="button" @click="showRescheduleModal = true" class="py-1.5 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-colors">
                                Perpanjang Sewa / Ubah Jadwal
                            </button>
                        </div>
                    </div>

                @else
                    <!-- KASUS 5: SELESAI ATAU DIBATALKAN -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                        <span class="text-slate-700 font-medium">Status reservasi: <strong class="text-slate-900">{{ $booking->formatted_status }}</strong></span>
                        @if($booking->status === 'cancelled')
                            <button type="button" @click="showRescheduleModal = true" class="text-xs text-slate-700 hover:text-slate-900 font-semibold underline">
                                Buka Kembali
                            </button>
                        @endif
                    </div>
                @endif

                <!-- MODE MANUAL / OVERRIDE STATUS (ACCORDION COLLAPSIBLE) -->
                <details class="group pt-2 border-t border-slate-100">
                    <summary class="cursor-pointer text-xs font-medium text-slate-400 hover:text-slate-600 flex items-center justify-between py-1 select-none">
                        <span>Pengaturan Manual Status & Unit</span>
                        <span class="text-[10px] text-slate-400 group-open:rotate-180 transition-transform">▼</span>
                    </summary>

                    <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="pt-3 space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Status Sewa:</label>
                                <select name="status" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                                    <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                    <option value="approved" {{ $booking->status === 'approved' ? 'selected' : '' }}>Disetujui (Siap Antar)</option>
                                    <option value="delivering" {{ $booking->status === 'delivering' ? 'selected' : '' }}>Sedang Diantar</option>
                                    <option value="active" {{ $booking->status === 'active' ? 'selected' : '' }}>Sedang Disewa (Aktif)</option>
                                    <option value="returning" {{ $booking->status === 'returning' ? 'selected' : '' }}>Proses Penjemputan</option>
                                    <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="rejected" {{ $booking->status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                    <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Unit Motor:</label>
                                <select name="motorcycle_id" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                                    <option value="">-- Pilih Unit Motor --</option>
                                    @foreach($motorcycles as $m)
                                        <option value="{{ $m->id }}" {{ $booking->motorcycle_id == $m->id ? 'selected' : '' }} {{ $m->has_conflict && $booking->motorcycle_id != $m->id ? 'class=text-slate-400' : '' }}>
                                            {{ $m->plate_number }} ({{ $m->color ?? '' }}) {{ $m->has_conflict && $booking->motorcycle_id != $m->id ? '— ⛔ Konflik' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end pt-1">
                            <button type="submit" class="py-2 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs transition-colors">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </details>
            </div>

            <!-- DOKUMEN & BUKTI TRANSFER UNGGAHAN TAMU -->
            <div x-data="{ showUploadForm: false }" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                            Berkas Identitas & Dokumen Pendukung Tamu
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pratinjau dokumen verifikasi identitas, pembayaran, dan perjalanan wisatawan.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showUploadForm = !showUploadForm" class="text-xs font-bold px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span>+ Update Berkas Utama</span>
                        </button>
                    </div>
                </div>

                <!-- Form Upload Berkas Utama (Collapsible) -->
                <div x-show="showUploadForm" x-transition class="bg-emerald-50/70 border border-emerald-200 p-4 rounded-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-emerald-950 text-xs flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Perbarui Berkas Utama (DP, KTP, dan SIM C)
                        </span>
                        <span class="text-[10px] text-emerald-700">Maks. 5MB per file (JPG / PNG)</span>
                    </div>
                    <form action="{{ route('admin.bookings.update-documents', $booking->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Ganti Bukti Transfer DP:</label>
                                <input type="file" name="payment_proof" accept="image/*" class="w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-white file:text-emerald-700 file:font-bold file:border file:border-slate-300">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Ganti Foto KTP:</label>
                                <input type="file" name="id_card_photo" accept="image/*" class="w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-white file:text-emerald-700 file:font-bold file:border file:border-slate-300">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Ganti Foto SIM C:</label>
                                <input type="file" name="sim_c_photo" accept="image/*" class="w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-white file:text-emerald-700 file:font-bold file:border file:border-slate-300">
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t border-emerald-200/60">
                            <button type="button" @click="showUploadForm = false" class="py-1.5 px-3 rounded-lg bg-white border border-slate-300 text-slate-700 font-bold text-xs">Batal</button>
                            <button type="submit" class="py-1.5 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs">Simpan Perubahan Berkas</button>
                        </div>
                    </form>
                </div>

                <!-- KELOMPOK 1: BERKAS UTAMA & PEMBAYARAN (WAJIB) - 3 Kolom Lega -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">
                            1. Berkas Utama & Verifikasi Pembayaran (Wajib)
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <!-- 1. Bukti Transfer DP -->
                        <div class="bg-slate-50/80 border border-slate-200 rounded-xl p-3.5 flex flex-col justify-between hover:border-emerald-300 transition-colors">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-slate-900 text-xs">Bukti Transfer DP</span>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md border border-emerald-200">
                                        Wajib • DP: Rp {{ number_format($booking->down_payment_amount, 0, ',', '.') }}
                                    </span>
                                </div>

                                @if($booking->payment_proof)
                                    <div class="relative group rounded-lg overflow-hidden border border-slate-200 bg-white">
                                        <img src="{{ asset('storage/' . $booking->payment_proof) }}" alt="Bukti Transfer DP" class="w-full h-48 object-contain bg-slate-50 p-1">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                            <button type="button" @click="previewImage = '{{ asset('storage/' . $booking->payment_proof) }}'; previewTitle = 'Bukti Transfer DP - {{ $booking->booking_code }}'" class="p-2 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                                <span>Perbesar</span>
                                            </button>
                                            <a href="{{ asset('storage/' . $booking->payment_proof) }}" target="_blank" class="p-2 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                <span>Tab Baru</span>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="h-48 bg-slate-100 border border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center text-slate-400 text-center p-4">
                                        <svg class="w-8 h-8 text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="font-semibold text-xs">Belum ada bukti transfer</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Tamu belum mengunggah struk/bukti DP</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- 2. Foto KTP Tamu -->
                        <div class="bg-slate-50/80 border border-slate-200 rounded-xl p-3.5 flex flex-col justify-between hover:border-emerald-300 transition-colors">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-slate-900 text-xs">Foto KTP Tamu</span>
                                    <span class="text-[10px] font-bold text-slate-700 bg-slate-200 px-2 py-0.5 rounded-md border border-slate-300">
                                        Wajib Identitas
                                    </span>
                                </div>

                                @if($booking->id_card_photo)
                                    <div class="relative group rounded-lg overflow-hidden border border-slate-200 bg-white">
                                        <img src="{{ asset('storage/' . $booking->id_card_photo) }}" alt="Foto KTP" class="w-full h-48 object-contain bg-slate-50 p-1">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                            <button type="button" @click="previewImage = '{{ asset('storage/' . $booking->id_card_photo) }}'; previewTitle = 'Foto KTP - {{ $booking->customer_name }}'" class="p-2 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                                <span>Perbesar</span>
                                            </button>
                                            <a href="{{ asset('storage/' . $booking->id_card_photo) }}" target="_blank" class="p-2 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                <span>Tab Baru</span>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="h-48 bg-slate-100 border border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center text-slate-400 text-center p-4">
                                        <svg class="w-8 h-8 text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                        </svg>
                                        <span class="font-semibold text-xs">Belum ada foto KTP</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Tamu belum mengunggah foto identitas</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- 3. Foto SIM C Tamu -->
                        <div class="bg-slate-50/80 border border-slate-200 rounded-xl p-3.5 flex flex-col justify-between hover:border-emerald-300 transition-colors">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-slate-900 text-xs">Foto SIM C Tamu</span>
                                    <span class="text-[10px] font-bold text-slate-700 bg-slate-200 px-2 py-0.5 rounded-md border border-slate-300">
                                        Wajib Mengemudi
                                    </span>
                                </div>

                                @if($booking->sim_c_photo)
                                    <div class="relative group rounded-lg overflow-hidden border border-slate-200 bg-white">
                                        <img src="{{ asset('storage/' . $booking->sim_c_photo) }}" alt="Foto SIM C" class="w-full h-48 object-contain bg-slate-50 p-1">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                            <button type="button" @click="previewImage = '{{ asset('storage/' . $booking->sim_c_photo) }}'; previewTitle = 'Foto SIM C - {{ $booking->customer_name }}'" class="p-2 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                                <span>Perbesar</span>
                                            </button>
                                            <a href="{{ asset('storage/' . $booking->sim_c_photo) }}" target="_blank" class="p-2 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                <span>Tab Baru</span>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="h-48 bg-slate-100 border border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center text-slate-400 text-center p-4">
                                        <svg class="w-8 h-8 text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="font-semibold text-xs">Belum ada foto SIM C</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Tamu belum mengunggah foto SIM C</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KELOMPOK 2: DOKUMEN PENDUKUNG WISATAWAN (OPSIONAL) - 2 Kolom Luas -->
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">
                                2. Dokumen Pendukung Perjalanan & Penginapan (Opsional)
                            </h3>
                        </div>
                        <span class="text-[11px] text-slate-500">Jaminan ekstra identitas wisatawan</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <!-- 4. Tiket Kereta / Pesawat PP -->
                        <div class="bg-slate-50/80 border border-slate-200 rounded-xl p-3.5 flex flex-col justify-between hover:border-blue-300 transition-colors">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-slate-900 text-xs">Tiket Kereta / Pesawat PP</span>
                                    <span class="text-[10px] font-semibold text-slate-600 bg-slate-200/70 px-2 py-0.5 rounded-md border border-slate-300">
                                        Opsional Wisatawan
                                    </span>
                                </div>

                                @if($booking->travel_ticket_photo)
                                    <div class="relative group rounded-lg overflow-hidden border border-slate-200 bg-white">
                                        <img src="{{ asset('storage/' . $booking->travel_ticket_photo) }}" alt="Tiket Kereta / Pesawat PP" class="w-full h-48 object-contain bg-slate-50 p-1">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                            <button type="button" @click="previewImage = '{{ asset('storage/' . $booking->travel_ticket_photo) }}'; previewTitle = 'Tiket Kereta/Pesawat PP - {{ $booking->customer_name }}'" class="p-2 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                                <span>Perbesar</span>
                                            </button>
                                            <a href="{{ asset('storage/' . $booking->travel_ticket_photo) }}" target="_blank" class="p-2 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                <span>Tab Baru</span>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="h-48 bg-slate-100 border border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center text-slate-400 text-center p-4">
                                        <svg class="w-8 h-8 text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                        </svg>
                                        <span class="font-semibold text-xs">Tanpa Tiket Kereta / Pesawat</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Tamu tidak melampirkan tiket perjalanan</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- 5. Bukti Booking Hotel Jogja -->
                        <div class="bg-slate-50/80 border border-slate-200 rounded-xl p-3.5 flex flex-col justify-between hover:border-blue-300 transition-colors">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-slate-900 text-xs">Bukti Booking Hotel / Villa</span>
                                    <span class="text-[10px] font-semibold text-slate-600 bg-slate-200/70 px-2 py-0.5 rounded-md border border-slate-300">
                                        Opsional Wisatawan
                                    </span>
                                </div>

                                @if($booking->hotel_booking_photo)
                                    <div class="relative group rounded-lg overflow-hidden border border-slate-200 bg-white">
                                        <img src="{{ asset('storage/' . $booking->hotel_booking_photo) }}" alt="Bukti Booking Hotel" class="w-full h-48 object-contain bg-slate-50 p-1">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                            <button type="button" @click="previewImage = '{{ asset('storage/' . $booking->hotel_booking_photo) }}'; previewTitle = 'Bukti Booking Hotel - {{ $booking->customer_name }}'" class="p-2 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                                <span>Perbesar</span>
                                            </button>
                                            <a href="{{ asset('storage/' . $booking->hotel_booking_photo) }}" target="_blank" class="p-2 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1">
                                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                <span>Tab Baru</span>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="h-48 bg-slate-100 border border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center text-slate-400 text-center p-4">
                                        <svg class="w-8 h-8 text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span class="font-semibold text-xs">Tanpa Bukti Reservasi Hotel</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Tamu tidak melampirkan voucher hotel</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Rincian Tamu & Biaya (1 Col) -->
        <div class="space-y-6">
            <!-- Info Tamu -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 text-xs">
                <h3 class="font-extrabold text-slate-900 uppercase text-[11px] tracking-wider border-b border-slate-100 pb-2">Data Pelanggan</h3>
                <p><strong>Nama:</strong> {{ $booking->customer_name }}</p>
                @php
                    $waCustomer = preg_replace('/[^0-9]/', '', $booking->customer_phone);
                    if (str_starts_with($waCustomer, '0')) {
                        $waCustomer = '62' . substr($waCustomer, 1);
                    }
                    $waEmergency = preg_replace('/[^0-9]/', '', $booking->emergency_contact_phone);
                    if (str_starts_with($waEmergency, '0')) {
                        $waEmergency = '62' . substr($waEmergency, 1);
                    }
                @endphp
                <p><strong>WhatsApp:</strong> 
                    <a href="https://wa.me/{{ $waCustomer }}" target="_blank" class="text-emerald-700 font-bold hover:underline inline-flex items-center gap-1">
                        {{ $booking->customer_phone }} ↗
                    </a>
                </p>
                <p><strong>No KTP:</strong> {{ $booking->customer_id_number }}</p>

                <!-- Kontak Darurat -->
                <div class="bg-emerald-50/70 p-3 rounded-xl border border-emerald-100 space-y-1 mt-2">
                    <span class="font-extrabold text-emerald-950 text-[11px] uppercase tracking-wider block">Kontak Darurat:</span>
                    <p class="text-slate-800"><strong>Nama:</strong> {{ $booking->emergency_contact_name }}</p>
                    <p class="text-slate-800"><strong>Hubungan:</strong> {{ $booking->emergency_contact_relation }}</p>
                    <p class="text-slate-800"><strong>No. WA:</strong> 
                        <a href="https://wa.me/{{ $waEmergency }}" target="_blank" class="text-emerald-700 font-bold hover:underline inline-flex items-center gap-1">
                            {{ $booking->emergency_contact_phone }} ↗
                        </a>
                    </p>
                </div>

                @if($booking->notes)
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100 text-slate-600">
                        <span class="font-bold block text-slate-700 text-[10px]">Catatan Tamu:</span>
                        {{ $booking->notes }}
                    </div>
                @endif
            </div>

            <!-- Lokasi & Jam Antar/Jemput -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 text-xs">
                <h3 class="font-extrabold text-slate-900 uppercase text-[11px] tracking-wider border-b border-slate-100 pb-2">Titik Antar & Jemput</h3>
                <div>
                    <span class="font-bold text-emerald-800 text-[10px] uppercase block">Pengantaran:</span>
                    <p class="font-extrabold text-slate-900">{{ $booking->start_time->format('d M Y, H:i') }} WIB</p>
                    <p class="font-semibold text-slate-800">{{ $booking->deliveryZone?->name }}</p>
                    <p class="text-slate-500 text-[11px]">{{ $booking->delivery_address }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="font-bold text-purple-800 text-[10px] uppercase block">Pengembalian:</span>
                    <p class="font-extrabold text-slate-900">{{ $booking->end_time->format('d M Y, H:i') }} WIB</p>
                    <p class="font-semibold text-slate-800">{{ $booking->returnZone?->name }}</p>
                    <p class="text-slate-500 text-[11px]">{{ $booking->return_address }}</p>
                </div>
            </div>

            <!-- Petugas Lapangan -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 text-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="font-extrabold text-slate-900 uppercase text-[11px] tracking-wider">Petugas Lapangan</h3>
                </div>
                <div class="space-y-2.5">
                    <div>
                        <span class="text-slate-400 block text-[10px] font-semibold uppercase">Petugas Antar:</span>
                        @if($booking->deliveryStaff)
                            <div class="flex items-center justify-between mt-0.5 bg-emerald-50/80 border border-emerald-200 p-2 rounded-xl text-emerald-950 font-bold">
                                <span>{{ $booking->deliveryStaff->name }}</span>
                                <span class="text-[10px] font-semibold text-emerald-700">{{ $booking->deliveryStaff->phone }}</span>
                            </div>
                        @else
                            <div class="mt-0.5 bg-slate-50 border border-slate-200 p-2 rounded-xl text-slate-500 text-[11px]">
                                Belum ditugaskan
                            </div>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-semibold uppercase">Petugas Jemput:</span>
                        @if($booking->returnStaff)
                            <div class="flex items-center justify-between mt-0.5 bg-purple-50/80 border border-purple-200 p-2 rounded-xl text-purple-950 font-bold">
                                <span>{{ $booking->returnStaff->name }}</span>
                                <span class="text-[10px] font-semibold text-purple-700">{{ $booking->returnStaff->phone }}</span>
                            </div>
                        @else
                            <div class="mt-0.5 bg-slate-50 border border-slate-200 p-2 rounded-xl text-slate-500 text-[11px]">
                                Belum ditugaskan
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rincian Keuangan -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2 text-xs">
                <h3 class="font-extrabold text-slate-800 uppercase text-[11px] tracking-wider border-b border-slate-200 pb-2">Rincian Finansial</h3>
                <div class="flex justify-between text-slate-600">
                    <span>Durasi Sewa:</span>
                    <span class="font-bold text-slate-800">{{ $booking->total_days }} Hari {{ $booking->overtime_hours > 0 ? '(+ '.$booking->overtime_hours.' Jam Overtime)' : '' }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Tarif Pokok Sewa:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($booking->rental_subtotal + $booking->overtime_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Ongkir Antar & Jemput:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($booking->delivery_fee + $booking->return_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-900 font-extrabold pt-2.5 border-t border-slate-200">
                    <span>Total Tagihan:</span>
                    <span class="text-emerald-700 text-sm">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <span>Uang Muka (DP):</span>
                        <button type="button" @click="showDpModal = true" class="text-[10px] text-slate-400 hover:text-slate-800 underline font-medium">Koreksi</button>
                    </span>
                    <span class="text-slate-900 font-bold">- Rp {{ number_format($booking->down_payment_amount, 0, ',', '.') }}</span>
                </div>
                @if($booking->remaining_amount <= 0 && ($booking->total_amount - $booking->down_payment_amount) > 0)
                    @php
                        $adminPelunasan = $booking->handoverInspection?->payment_collected_amount ?? ($booking->total_amount - $booking->down_payment_amount);
                    @endphp
                    <div class="flex justify-between text-slate-600">
                        <span>Pelunasan (Serah Terima):</span>
                        <span class="text-slate-900 font-bold">- Rp {{ number_format($adminPelunasan, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-900 font-extrabold text-sm pt-2.5 border-t border-slate-200">
                    <div>
                        <span class="block">Sisa Tagihan:</span>
                        <span class="text-[10px] text-slate-400 font-normal">Ditagih saat serah terima</span>
                    </div>
                    @if($booking->remaining_amount <= 0)
                        <span class="text-slate-900 font-bold">✓ Lunas</span>
                    @else
                        <span class="text-slate-900">Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- LOG INSPEKSI CEKLIS SERAH TERIMA & PENGEMBALIAN (FULL WIDTH) -->
    @if($booking->handoverInspection || $booking->returnInspection)
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Hasil Ceklis Lapangan & Dokumen Petugas Kurir
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Catatan fisik, status kelengkapan unit, dan foto dokumentasi yang diunggah staf lapangan</p>
                </div>
            </div>

            <div class="grid grid-cols-1 {{ ($booking->handoverInspection && $booking->returnInspection) ? 'lg:grid-cols-2' : '' }} gap-6 items-start text-xs">
                <!-- CEKLIS SERAH TERIMA (ANTAR) -->
                @if($booking->handoverInspection)
                    @php
                        $handoverPhotos = is_array($booking->handoverInspection->photo_paths) 
                            ? $booking->handoverInspection->photo_paths 
                            : (is_string($booking->handoverInspection->photo_paths) ? json_decode($booking->handoverInspection->photo_paths, true) ?? [] : []);
                    @endphp
                    <div class="bg-emerald-50/50 border border-emerald-200/80 p-5 rounded-xl space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between border-b border-emerald-200/60 pb-2.5">
                                <span class="font-extrabold text-emerald-900 uppercase text-[11px] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    Ceklis Saat Antar (Serah Terima)
                                </span>
                                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-md border border-emerald-200">
                                    {{ $booking->handoverInspection->inspected_at->format('d M Y, H:i') }} WIB
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 {{ ($booking->handoverInspection && $booking->returnInspection) ? 'xl:grid-cols-3' : 'md:grid-cols-3' }} gap-3 text-[11px] text-slate-700">
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Petugas Lapangan</span>
                                    <span class="font-bold text-slate-900">{{ $booking->handoverInspection->staff->name }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Indikator Bensin</span>
                                    <span class="font-bold text-slate-900">{{ $booking->handoverInspection->fuel_level }} Tangki</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Helm Diserahkan</span>
                                    <span class="font-bold text-slate-900">{{ $booking->handoverInspection->helmets_count }} Buah Helm</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Jas Hujan di Bagasi</span>
                                    <span class="font-bold text-slate-900">{{ $booking->handoverInspection->raincoats_count }} Buah Jas Hujan</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Kunci & STNK Asli</span>
                                    <span class="font-bold {{ $booking->handoverInspection->key_and_stnk_included ? 'text-emerald-700' : 'text-rose-600' }}">
                                        {{ $booking->handoverInspection->key_and_stnk_included ? '✓ Lengkap Diserahkan' : '✗ Tidak Ada' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">KTP Jaminan Fisik</span>
                                    <span class="font-bold {{ $booking->handoverInspection->physical_id_collected ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ $booking->handoverInspection->physical_id_collected ? '✓ Ditahan Kurir' : 'Tidak Ditahan' }}
                                    </span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-emerald-200/60 space-y-1.5 text-[11px]">
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Konfirmasi Kontak Darurat</span>
                                    <span class="font-bold {{ $booking->handoverInspection->emergency_contact_verified ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ $booking->handoverInspection->emergency_contact_verified ? '✓ Terverifikasi Kenal Penyewa' : 'Belum Terverifikasi' }}
                                    </span>
                                    @if($booking->handoverInspection->emergency_verification_note)
                                        <span class="text-slate-500 text-[10px] block italic">Catatan: {{ $booking->handoverInspection->emergency_verification_note }}</span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Pelunasan di Tempat:</span>
                                    <span class="font-bold text-slate-900">
                                        Rp {{ number_format($booking->handoverInspection->payment_collected_amount, 0, ',', '.') }}
                                        <span class="text-[10px] text-emerald-700 font-semibold">({{ ucfirst($booking->handoverInspection->payment_collected_method) }})</span>
                                    </span>
                                </div>

                                @if($booking->handoverInspection->damage_notes)
                                    <div class="bg-white/80 p-2.5 rounded-lg border border-emerald-200 text-slate-700 text-[11px]">
                                        <span class="font-bold text-slate-800 text-[10px] block uppercase">Catatan Kondisi Awal Motor:</span>
                                        {{ $booking->handoverInspection->damage_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- FOTO DOKUMENTASI SERAH TERIMA -->
                        <div class="pt-3 border-t border-emerald-200/80 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Foto Dokumentasi Petugas (Serah Terima)
                                </span>
                                <span class="text-[10px] text-emerald-800 font-bold bg-emerald-100 px-1.5 py-0.5 rounded">
                                    {{ count($handoverPhotos) }} Foto
                                </span>
                            </div>

                            @if(count($handoverPhotos) > 0)
                                <div class="grid grid-cols-2 sm:grid-cols-3 {{ ($booking->handoverInspection && $booking->returnInspection) ? 'xl:grid-cols-4' : 'md:grid-cols-4' }} gap-3">
                                    @foreach($handoverPhotos as $idx => $photoPath)
                                        <div class="relative group rounded-xl overflow-hidden border border-emerald-200 bg-white shadow-xs">
                                            <span class="absolute top-2 left-2 z-10 px-2 py-0.5 rounded-md bg-slate-900/70 text-white font-mono text-[10px] font-bold backdrop-blur-xs">
                                                #{{ $idx + 1 }}
                                            </span>
                                            <img src="{{ asset('storage/' . $photoPath) }}" alt="Dokumentasi Antar {{ $idx + 1 }}" class="w-full h-30 sm:h-30 object-cover bg-slate-100 group-hover:scale-105 transition-transform duration-200">
                                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 p-2">
                                                <button type="button" @click="previewImage = '{{ asset('storage/' . $photoPath) }}'; previewTitle = 'Dokumentasi Serah Terima Foto #{{ $idx + 1 }} - {{ $booking->booking_code }}'" 
                                                    class="py-1.5 px-3 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                    </svg>
                                                </button>
                                                <a href="{{ asset('storage/' . $photoPath) }}" target="_blank" 
                                                    class="p-1.5 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center justify-center"
                                                    title="Buka gambar di tab baru">
                                                    <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 bg-white/60 border border-dashed border-emerald-200 rounded-lg text-center text-slate-400 text-[11px]">
                                    Tidak ada foto dokumentasi yang diunggah saat serah terima.
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- CEKLIS PENGEMBALIAN (JEMPUT) -->
                @if($booking->returnInspection)
                    @php
                        $returnPhotos = is_array($booking->returnInspection->photo_paths) 
                            ? $booking->returnInspection->photo_paths 
                            : (is_string($booking->returnInspection->photo_paths) ? json_decode($booking->returnInspection->photo_paths, true) ?? [] : []);
                    @endphp
                    <div class="bg-purple-50/50 border border-purple-200/80 p-5 rounded-xl space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between border-b border-purple-200/60 pb-2.5">
                                <span class="font-extrabold text-purple-900 uppercase text-[11px] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                                    Ceklis Saat Jemput (Pengembalian)
                                </span>
                                <span class="text-[10px] font-semibold text-purple-700 bg-purple-100/80 px-2 py-0.5 rounded-md border border-purple-200">
                                    {{ $booking->returnInspection->inspected_at->format('d M Y, H:i') }} WIB
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 {{ ($booking->handoverInspection && $booking->returnInspection) ? 'xl:grid-cols-3' : 'md:grid-cols-3' }} gap-3 text-[11px] text-slate-700">
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Petugas Penjemput</span>
                                    <span class="font-bold text-slate-900">{{ $booking->returnInspection->staff->name }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Indikator Bensin Kembali</span>
                                    <span class="font-bold text-slate-900">{{ $booking->returnInspection->fuel_level }} Tangki</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Helm Kembali</span>
                                    <span class="font-bold text-slate-900">{{ $booking->returnInspection->helmets_count }} Buah Helm</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Jas Hujan Kembali</span>
                                    <span class="font-bold text-slate-900">{{ $booking->returnInspection->raincoats_count }} Buah Jas Hujan</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Kunci & STNK Asli</span>
                                    <span class="font-bold {{ $booking->returnInspection->key_and_stnk_included ? 'text-emerald-700' : 'text-rose-600' }}">
                                        {{ $booking->returnInspection->key_and_stnk_included ? '✓ Lengkap Diterima' : '✗ Kunci/STNK Hilang' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">KTP Jaminan Fisik</span>
                                    <span class="font-bold text-emerald-700">
                                        ✓ Dikembalikan ke Tamu
                                    </span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-purple-200/60 space-y-1.5 text-[11px]">
                                @if($booking->returnInspection->damage_notes)
                                    <div class="bg-white/80 p-2.5 rounded-lg border border-purple-200 text-slate-700 text-[11px]">
                                        <span class="font-bold text-slate-800 text-[10px] block uppercase">Catatan Kondisi Akhir Motor:</span>
                                        {{ $booking->returnInspection->damage_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- FOTO DOKUMENTASI PENGEMBALIAN -->
                        <div class="pt-3 border-t border-purple-200/80 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Foto Dokumentasi Petugas (Pengembalian)
                                </span>
                                <span class="text-[10px] text-purple-800 font-bold bg-purple-100 px-1.5 py-0.5 rounded">
                                    {{ count($returnPhotos) }} Foto
                                </span>
                            </div>

                            @if(count($returnPhotos) > 0)
                                <div class="grid grid-cols-2 sm:grid-cols-3 {{ ($booking->handoverInspection && $booking->returnInspection) ? 'xl:grid-cols-4' : 'md:grid-cols-4' }} gap-3">
                                    @foreach($returnPhotos as $idx => $photoPath)
                                        <div class="relative group rounded-xl overflow-hidden border border-purple-200 bg-white shadow-xs">
                                            <span class="absolute top-2 left-2 z-10 px-2 py-0.5 rounded-md bg-slate-900/70 text-white font-mono text-[10px] font-bold backdrop-blur-xs">
                                                #{{ $idx + 1 }}
                                            </span>
                                            <img src="{{ asset('storage/' . $photoPath) }}" alt="Dokumentasi Kembali {{ $idx + 1 }}" class="w-full h-30 sm:h-30 object-cover bg-slate-100 group-hover:scale-105 transition-transform duration-200">
                                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 p-2">
                                                <button type="button" @click="previewImage = '{{ asset('storage/' . $photoPath) }}'; previewTitle = 'Dokumentasi Pengembalian Foto #{{ $idx + 1 }} - {{ $booking->booking_code }}'" 
                                                    class="py-1.5 px-3 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                    </svg>
                                                </button>
                                                <a href="{{ asset('storage/' . $photoPath) }}" target="_blank" 
                                                    class="p-1.5 rounded-lg bg-white/95 hover:bg-white text-slate-900 font-bold text-xs shadow-md transition-transform active:scale-95 flex items-center justify-center"
                                                    title="Buka gambar di tab baru">
                                                    <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 bg-white/60 border border-dashed border-purple-200 rounded-lg text-center text-slate-400 text-[11px]">
                                    Tidak ada foto dokumentasi yang diunggah saat pengembalian.
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Global Lightbox Modal untuk Preview Gambar (Dokumen Tamu & Dokumentasi Petugas) -->
    <div x-show="previewImage" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="previewImage = null"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs" 
         style="display: none;">
        <div @click.away="previewImage = null" class="bg-white rounded-2xl max-w-4xl w-full max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-slate-700">
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <span class="font-bold text-sm tracking-wide" x-text="previewTitle"></span>
                <div class="flex items-center gap-3">
                    <a :href="previewImage" target="_blank" class="text-xs text-emerald-400 hover:text-emerald-300 font-bold underline flex items-center gap-1">
                        Buka Tab Penuh ↗
                    </a>
                    <button type="button" @click="previewImage = null" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-4 bg-slate-950/20 flex-1 overflow-auto flex items-center justify-center min-h-[350px]">
                <img :src="previewImage" :alt="previewTitle" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-md bg-white">
            </div>
        </div>
    </div>

    <!-- Modal Tolak Reservasi -->
    <div x-show="showRejectModal" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         @keydown.escape.window="showRejectModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" 
         style="display: none;">
        <div @click.away="showRejectModal = false" class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-xl border border-slate-200">
            <div class="px-6 py-4 bg-white border-b border-slate-100 flex items-center justify-between">
                <span class="font-bold text-sm text-slate-900">Tolak Reservasi {{ $booking->booking_code }}</span>
                <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.bookings.reject', $booking->id) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <p class="text-xs text-slate-500">
                    Alasan penolakan akan ditampilkan kepada penyewa di halaman tiket pemesanan.
                </p>

                <div class="space-y-1.5 text-xs">
                    <label class="block font-medium text-slate-700">Alasan Penolakan <span class="text-rose-500">*</span>:</label>
                    <textarea name="rejection_reason" x-ref="rejectReasonInput" required rows="3" placeholder="Contoh: Unit motor tidak tersedia pada tanggal tersebut..." class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-1 focus:ring-slate-900 focus:border-slate-900">{{ $booking->rejection_reason }}</textarea>

                    <!-- Template Alasan Cepat -->
                    <div class="pt-1.5">
                        <span class="text-[10px] text-slate-400 font-medium block mb-1">Pilih cepat:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="$refs.rejectReasonInput.value = 'Unit motor tidak tersedia pada tanggal yang diminta.'" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg border border-slate-200 transition-colors">
                                Unit Penuh / Tidak Tersedia
                            </button>
                            <button type="button" @click="$refs.rejectReasonInput.value = 'Foto KTP atau SIM C buram dan tidak terbaca jelas. Mohon unggah ulang.'" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg border border-slate-200 transition-colors">
                                KTP / SIM C Buram
                            </button>
                            <button type="button" @click="$refs.rejectReasonInput.value = 'Bukti transfer DP belum masuk atau nominal tidak sesuai.'" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg border border-slate-200 transition-colors">
                                Bukti DP Belum Masuk
                            </button>
                            <button type="button" @click="$refs.rejectReasonInput.value = 'Titik lokasi pengantaran berada di luar area jangkauan operasional kami.'" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg border border-slate-200 transition-colors">
                                Luar Jangkauan Wilayah
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showRejectModal = false" class="py-2 px-4 rounded-xl border border-slate-200 text-slate-600 font-medium text-xs hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="py-2 px-5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors">
                        Tolak Reservasi Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Batalkan Reservasi (Approved -> Cancelled) -->
    <div x-show="showCancelModal" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         @keydown.escape.window="showCancelModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" 
         style="display: none;">
        <div @click.away="showCancelModal = false" class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-xl border border-slate-200">
            <div class="px-6 py-4 bg-white border-b border-slate-100 flex items-center justify-between">
                <span class="font-bold text-sm text-slate-900">Batalkan Reservasi {{ $booking->booking_code }}</span>
                <button type="button" @click="showCancelModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <input type="hidden" name="payment_status" value="rejected">

                <p class="text-xs text-slate-500">
                    Unit motor yang dialokasikan akan dikembalikan ke status tersedia di sistem.
                </p>

                <div class="space-y-1.5 text-xs">
                    <label class="block font-medium text-slate-700">Alasan Pembatalan <span class="text-rose-500">*</span>:</label>
                    <textarea name="rejection_reason" required rows="2" placeholder="Contoh: Tamu membatalkan sewa..." class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:ring-1 focus:ring-slate-900 focus:border-slate-900"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCancelModal = false" class="py-2 px-4 rounded-xl border border-slate-200 text-slate-600 font-medium text-xs hover:bg-slate-50 transition-colors">
                        Kembali
                    </button>
                    <button type="submit" class="py-2 px-5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors">
                        Ya, Batalkan Reservasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Revisi Jadwal & Unit Motor (Reschedule / Ganti Unit / Aktifkan Kembali) -->
    <div x-show="showRescheduleModal" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         @keydown.escape.window="showRescheduleModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" 
         style="display: none;">
        <div @click.away="showRescheduleModal = false" class="bg-white rounded-2xl max-w-2xl w-full max-h-[92vh] flex flex-col overflow-hidden shadow-xl border border-slate-200">
            <div class="px-6 py-4 bg-white border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-base text-slate-900">Revisi Jadwal & Motor Reservasi {{ $booking->booking_code }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Ubah tanggal sewa atau unit motor sewaan.</p>
                </div>
                <button type="button" @click="showRescheduleModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.bookings.reschedule', $booking->id) }}" method="POST" class="p-6 overflow-y-auto space-y-4">
                @csrf

                <!-- 1. Jadwal Sewa Baru -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Mulai Baru:</label>
                        <input type="datetime-local" name="start_time" x-model="rescheduleStartTime" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Selesai Baru:</label>
                        <input type="datetime-local" name="end_time" x-model="rescheduleEndTime" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                    </div>
                </div>

                <!-- 2. Kategori & Unit Motor Baru -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Kategori Motor:</label>
                        <select name="category_id" x-model="rescheduleCategoryId" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->name }} (Rp {{ number_format($cat->daily_price, 0, ',', '.') }}/hari)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Pilih Unit Motor:</label>
                        <select name="motorcycle_id" x-model="rescheduleMotorId" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                            <option value="">-- Pilih Nanti / Bebas --</option>
                            @foreach($allMotorcycles as $m)
                                <option value="{{ $m->id }}" x-show="rescheduleCategoryId == '{{ $m->category_id }}'">
                                    {{ $m->plate_number }} ({{ $m->brand }} {{ $m->color ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 3. Status Tujuan & Catatan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Ubah Status Menjadi:</label>
                        <select name="target_status" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white font-medium">
                            <option value="approved" selected>Disetujui / Diterima (Siap Antar)</option>
                            <option value="pending">Menunggu Verifikasi Kembali</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Catatan Kesepakatan (Opsional):</label>
                        <input type="text" name="reschedule_note" placeholder="Contoh: Tamu minta undur 1 hari via WA..." class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                    </div>
                </div>

                <!-- 4. Kalkulator Estimasi Finansial Realtime -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs space-y-2">
                    <span class="text-xs font-semibold text-slate-700 block">
                        Rincian Biaya Otomatis:
                    </span>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Durasi Sewa:</span>
                        <span class="font-semibold text-slate-900" x-text="rescheduleCalculator().days + ' Hari ' + (rescheduleCalculator().overtimeHours > 0 ? '(+ ' + rescheduleCalculator().overtimeHours + ' Jam Overtime)' : '')"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Tarif Sewa Pokok:</span>
                        <span class="font-semibold text-slate-900" x-text="'Rp ' + Number(rescheduleCalculator().rentalSubtotal + rescheduleCalculator().overtimeFee).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Ongkir Antar & Jemput:</span>
                        <span class="font-semibold text-slate-900" x-text="'Rp ' + Number(deliveryFee + returnFee).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-900 font-bold pt-2 border-t border-slate-200">
                        <span>Total Biaya Baru:</span>
                        <span class="font-bold text-slate-900" x-text="'Rp ' + Number(rescheduleCalculator().totalAmount).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>DP Telah Disetor:</span>
                        <span class="font-semibold text-slate-700" x-text="'- Rp ' + Number(dpAmount).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-900 font-bold pt-1.5 border-t border-slate-200">
                        <span>Sisa Tagihan:</span>
                        <span class="font-bold text-slate-900" x-text="'Rp ' + Number(rescheduleCalculator().remainingAmount).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showRescheduleModal = false" class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 font-medium text-xs hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 px-6 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Koreksi Nominal DP Masuk -->
    <div x-show="showDpModal" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         @keydown.escape.window="showDpModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" 
         style="display: none;">
        <div @click.away="showDpModal = false" class="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-xl border border-slate-200">
            <div class="px-6 py-4 bg-white border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Koreksi Nominal DP</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $booking->booking_code }} • {{ $booking->customer_name }}</p>
                </div>
                <button type="button" @click="showDpModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.bookings.update-dp', $booking->id) }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block font-semibold text-slate-700">Nominal DP Sebenarnya Masuk (Rp) <span class="text-rose-500">*</span>:</label>
                        <span class="text-[10px] text-slate-400">Total: Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <input type="number" name="down_payment_amount" x-model.number="inputDpAmount" min="0" max="{{ (float)$booking->total_amount }}" required class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white font-bold text-slate-900">
                </div>

                <!-- Preview Realtime -->
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1.5 text-slate-600">
                    <div class="flex items-center justify-between">
                        <span>Total Tagihan Sewa:</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>DP Masuk yang Dikonfirmasi:</span>
                        <span class="font-bold text-slate-900" x-text="'Rp ' + Number(inputDpAmount || 0).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex items-center justify-between font-bold text-slate-900 pt-1.5 border-t border-slate-200">
                        <span>Sisa Tagihan untuk Petugas:</span>
                        <span class="text-sm font-extrabold text-slate-900" x-text="'Rp ' + Number(calculatePendingRemaining()).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block font-medium text-slate-700">Catatan Kesepakatan (Opsional):</label>
                    <input type="text" name="dp_correction_note" placeholder="Contoh: Tamu transfer 10rb, dikonfirmasi via WA setuju lanjut" class="w-full p-2.5 rounded-xl border border-slate-300 text-xs bg-white">
                </div>

                <p class="text-[11px] text-slate-500">
                    * Penyesuaian DP akan langsung memperbarui sisa tagihan di aplikasi petugas lapangan untuk ditagihkan saat serah terima.
                </p>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showDpModal = false" class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 font-medium text-xs hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 px-5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                        Simpan Koreksi DP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
