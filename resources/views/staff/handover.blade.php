@extends('layouts.staff')

@section('content')
<div class="space-y-4" x-data="{ previewImage: null, previewTitle: '' }">
    <!-- Tombol Kembali -->
    <a href="{{ route('staff.tasks') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800">
        &larr; Kembali ke Daftar Tugas
    </a>

    <!-- Header Task -->
    <div class="bg-gradient-to-r from-emerald-800 to-teal-800 text-white p-4 rounded-2xl shadow-sm flex items-center justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300">Form Serah-Terima (Antar Unit)</span>
                <span class="bg-emerald-950/60 border border-emerald-400/30 text-emerald-200 font-extrabold px-2 py-0.5 rounded text-[10px]">{{ $booking->booking_code }}</span>
            </div>
            <h1 class="text-base font-extrabold text-white mt-1">{{ $booking->customer_name }}</h1>
            <p class="text-xs text-slate-200 mt-0.5">{{ $booking->category->name }} • <strong class="text-emerald-300 font-extrabold">{{ $booking->motorcycle?->plate_number }}</strong></p>
        </div>
        @if($booking->category->image_url)
            <div class="w-16 h-12 bg-white/10 rounded-xl p-1 flex items-center justify-center shrink-0 border border-white/20">
                <img src="{{ $booking->category->image_url }}" alt="{{ $booking->category->name }}" class="max-h-full max-w-full object-contain">
            </div>
        @endif
    </div>

    <!-- Form Ceklis -->
    <form action="{{ route('staff.tasks.handover.submit', $booking->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
        @csrf

        <!-- 1. Penagihan Sisa Sewa -->
        <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-amber-100 pb-2">
                <span class="font-bold text-amber-900 text-xs">Wajib Tagih Sisa Sewa:</span>
                <span class="font-extrabold text-base text-amber-800">Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}</span>
            </div>

            <label class="flex items-start gap-2.5 cursor-pointer bg-amber-50/60 p-2.5 rounded-xl border border-amber-200">
                <input type="checkbox" name="remaining_payment_collected" value="1" required class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-amber-300">
                <span class="text-slate-800 font-bold leading-tight">
                    Saya telah menerima pelunasan sisa biaya sewa dari tamu sebesar Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}
                </span>
            </label>

            <div class="grid grid-cols-2 gap-2 pt-1">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Metode Penerimaan</label>
                    <select name="payment_collected_method" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="qris">Transfer / QRIS</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Nominal Diterima (Rp)</label>
                    <input type="number" name="payment_collected_amount" value="{{ (int)$booking->remaining_amount }}" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
                </div>
            </div>
        </div>

        <!-- 2. Verifikasi Identitas & Jaminan Fisik Tamu -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Verifikasi Identitas & Dokumen Tamu</span>
                <span class="text-[10px] text-slate-500 font-medium">Cocokkan dengan tamu di lokasi</span>
            </div>

            <!-- Info NIK / No Identitas -->
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] text-slate-400 font-semibold block uppercase">NIK / No. {{ strtoupper($booking->customer_id_type ?? 'KTP') }}:</span>
                    <strong class="text-sm font-mono text-slate-900 tracking-wider font-extrabold">{{ $booking->customer_id_number }}</strong>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 font-semibold block uppercase">Nama Penyewa:</span>
                    <span class="text-xs font-bold text-slate-800">{{ $booking->customer_name }}</span>
                </div>
            </div>

            <!-- Berkas KTP & SIM C Unggahan Penyewa (2 Kolom) -->
            <div class="grid grid-cols-2 gap-2.5 pt-1">
                <!-- Foto KTP -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-700 block">KTP Unggahan Tamu:</span>
                    @if($booking->id_card_photo)
                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-50">
                            <button type="button" @click="previewImage = '{{ asset('storage/' . $booking->id_card_photo) }}'; previewTitle = 'KTP - {{ $booking->customer_name }}'" class="block w-full text-left relative group">
                                <img src="{{ asset('storage/' . $booking->id_card_photo) }}" alt="Foto KTP" class="w-full h-32 object-cover">
                                <span class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[10px] font-bold">
                                    🔍 Perbesar
                                </span>
                            </button>
                            <div class="p-1.5 bg-white border-t border-slate-100 text-center">
                                <button type="button" @click="previewImage = '{{ asset('storage/' . $booking->id_card_photo) }}'; previewTitle = 'KTP - {{ $booking->customer_name }}'" class="text-[10px] text-slate-700 font-bold hover:underline">
                                    Lihat Jelas KTP ↗
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="h-32 rounded-xl border border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-center p-2 text-slate-400 text-[10px]">
                            Tamu belum upload KTP
                        </div>
                    @endif
                </div>

                <!-- Foto SIM C -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-700 block">SIM C Unggahan Tamu:</span>
                    @if($booking->sim_c_photo)
                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-50">
                            <button type="button" @click="previewImage = '{{ asset('storage/' . $booking->sim_c_photo) }}'; previewTitle = 'SIM C - {{ $booking->customer_name }}'" class="block w-full text-left relative group">
                                <img src="{{ asset('storage/' . $booking->sim_c_photo) }}" alt="Foto SIM C" class="w-full h-32 object-cover">
                                <span class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[10px] font-bold">
                                    🔍 Perbesar
                                </span>
                            </button>
                            <div class="p-1.5 bg-white border-t border-slate-100 text-center">
                                <button type="button" @click="previewImage = '{{ asset('storage/' . $booking->sim_c_photo) }}'; previewTitle = 'SIM C - {{ $booking->customer_name }}'" class="text-[10px] text-slate-700 font-bold hover:underline">
                                    Lihat Jelas SIM C ↗
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="h-32 rounded-xl border border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-center p-2 text-slate-400 text-[10px]">
                            Tamu belum upload SIM C
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ceklis Jaminan Fisik KTP Asli -->
            <label class="flex items-start gap-2.5 cursor-pointer bg-slate-50 p-2.5 rounded-xl border border-slate-200 mt-2">
                <input type="checkbox" name="physical_id_collected" value="1" required class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                <span class="text-slate-700 font-semibold leading-tight">
                    KTP Asli / Dokumen Jaminan fisik tamu sudah diverifikasi cocok dengan berkas di atas dan disimpan oleh petugas sebagai jaminan sewa.
                </span>
            </label>
        </div>

        <!-- 3. Ceklis Kontak Darurat (Bukti Kenal Penyewa) -->
        <div class="bg-white p-4 rounded-2xl border border-emerald-300 ring-2 ring-emerald-100 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-emerald-100 pb-2">
                <div>
                    <span class="font-extrabold text-emerald-950 text-xs block">Verifikasi Kontak Darurat (WA)</span>
                    <span class="text-[10px] text-emerald-700">Wajib konfirmasi kontak WA kenal dengan penyewa</span>
                </div>
                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Wajib Ceklis</span>
            </div>

            <div class="bg-emerald-50/70 p-3 rounded-xl border border-emerald-200/80 space-y-1.5 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-semibold">Nama Kontak Darurat:</span>
                    <strong class="text-slate-900 font-extrabold">{{ $booking->emergency_contact_name }}</strong>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-semibold">Hubungan / Relasi:</span>
                    <span class="font-bold text-slate-800">{{ $booking->emergency_contact_relation }}</span>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-emerald-200/60">
                    <span class="text-slate-500 font-semibold">No. WhatsApp Darurat:</span>
                    <div class="flex items-center gap-2">
                        <strong class="font-extrabold text-emerald-900">{{ $booking->emergency_contact_phone }}</strong>
                        @php
                            $cleanEmgPhone = preg_replace('/[^0-9]/', '', $booking->emergency_contact_phone);
                            if (str_starts_with($cleanEmgPhone, '0')) {
                                $cleanEmgPhone = '62' . substr($cleanEmgPhone, 1);
                            }
                            $waTextEmg = urlencode("Halo Kak {$booking->emergency_contact_name}, kami dari JogjaMotor konfirmasi terkait penyewaan unit motor oleh rekan/keluarga Kakak, {$booking->customer_name}. Apakah betul Kakak mengenal beliau?");
                        @endphp
                        <a href="https://wa.me/{{ $cleanEmgPhone }}?text={{ $waTextEmg }}" target="_blank"
                            class="px-2 py-0.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] inline-flex items-center gap-1 shadow-sm">
                            Chat WA &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <label class="flex items-start gap-2.5 cursor-pointer bg-emerald-50/50 hover:bg-emerald-100/50 p-2.5 rounded-xl border border-emerald-200 transition-colors">
                <input type="checkbox" name="emergency_contact_verified" value="1" required class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 w-4 h-4">
                <span class="text-slate-800 font-bold leading-tight">
                    Saya menyatakan bukti bahwa kontak WA di atas ({{ $booking->emergency_contact_name }} - {{ $booking->emergency_contact_relation }}) telah dihubungi/dikonfirmasi dan benar-benar kenal dengan penyewa.
                </span>
            </label>

            <div>
                <label class="block text-slate-600 font-semibold mb-1 text-[11px]">Catatan Konfirmasi Kontak Darurat (Opsional)</label>
                <input type="text" name="emergency_verification_note" placeholder="Contoh: Dikonfirmasi via WA, menyatakan benar adik kandung" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
            </div>
        </div>

        <!-- 4. Kelengkapan Unit -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Kelengkapan Unit Motor:</span>
                <span class="text-[10px] text-slate-500 font-medium">Bawaan fisik motor: {{ $defaultHelmets }} Helm • {{ $defaultRaincoats }} Jas Hujan</span>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Jumlah Helm Bersih</label>
                    <select name="helmets_count" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
                        @for($i = 2; $i >= 0; $i--)
                            <option value="{{ $i }}" {{ old('helmets_count', $defaultHelmets) == $i ? 'selected' : '' }}>
                                @if($i == 2)
                                    2 Buah Helm {{ $defaultHelmets == 2 ? '(Standar Unit Ini)' : '' }}
                                @elseif($i == 1)
                                    1 Buah Helm {{ $defaultHelmets == 1 ? '(Standar Unit Ini)' : '' }}
                                @else
                                    Tanpa Helm {{ $defaultHelmets == 0 ? '(Standar Unit Ini)' : '(Tamu Bawa Sendiri)' }}
                                @endif
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Jas Hujan di Bagasi</label>
                    <select name="raincoats_count" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
                        @for($i = 0; $i <= 3; $i++)
                            <option value="{{ $i }}" {{ old('raincoats_count', $defaultRaincoats) == $i ? 'selected' : '' }}>
                                {{ $i }} Jas Hujan {{ $i == $defaultRaincoats ? '(Standar Unit Ini)' : '' }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Indikator Bensin</label>
                    <select name="fuel_level" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
                        <option value="Full" {{ old('fuel_level', 'Full') == 'Full' ? 'selected' : '' }}>Full (Penuh)</option>
                        <option value="3/4" {{ old('fuel_level') == '3/4' ? 'selected' : '' }}>3/4 Tangki</option>
                        <option value="1/2" {{ old('fuel_level') == '1/2' ? 'selected' : '' }}>1/2 Tangki</option>
                        <option value="1/4" {{ old('fuel_level') == '1/4' ? 'selected' : '' }}>1/4 Tangki</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Kunci & STNK Asli</label>
                    <select name="key_and_stnk_included" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
                        <option value="1" {{ old('key_and_stnk_included', $defaultKeyStnk ? 1 : 0) == 1 ? 'selected' : '' }}>Lengkap (Kunci + STNK)</option>
                        <option value="0" {{ old('key_and_stnk_included', $defaultKeyStnk ? 1 : 0) == 0 ? 'selected' : '' }}>Tidak Lengkap</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 4. Dokumentasi & Kondisi Fisik -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Foto Dokumentasi Serah Terima:</span>
            <div>
                <label class="block text-slate-600 font-semibold mb-1">Upload Foto Motor & Penyerahan (Bisa lebih dari 1 foto)</label>
                <input type="file" name="photos[]" multiple accept="image/*" class="block w-full text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-100 file:text-emerald-800">
                <p class="text-[10px] text-slate-400 mt-1">Saran: Foto 4 sisi motor (bodi depan, samping kanan/kiri, knalpot) dan foto serah terima bersama tamu.</p>
            </div>

            <div>
                <label class="block text-slate-600 font-semibold mb-1">Catatan Baret / Lecet Fisik Awal (Opsional)</label>
                <textarea name="damage_notes" rows="2" placeholder="Contoh: Ada baret halus di spakbor depan..." class="w-full p-2 rounded-lg border border-slate-300 text-xs"></textarea>
            </div>
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-bold text-sm bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white shadow-lg shadow-emerald-700/30 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Konfirmasi Serah Terima Selesai</span>
        </button>
    </form>

    <!-- Modal Pratinjau Foto Dokumen (Lightbox) -->
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
        <div @click.away="previewImage = null" class="bg-white rounded-2xl max-w-sm w-full overflow-hidden shadow-2xl border border-slate-700">
            <div class="px-4 py-3 bg-slate-900 text-white flex items-center justify-between">
                <span class="font-bold text-xs" x-text="previewTitle"></span>
                <button type="button" @click="previewImage = null" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-2 bg-slate-950 flex items-center justify-center max-h-[70vh] overflow-auto">
                <img :src="previewImage" alt="Pratinjau Dokumen" class="w-full h-auto object-contain rounded-lg">
            </div>
            <div class="p-2.5 bg-slate-900 text-center border-t border-slate-800">
                <a :href="previewImage" target="_blank" class="text-xs text-emerald-400 hover:underline font-bold inline-flex items-center gap-1">
                    Buka Gambar Penuh di Tab Baru ↗
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
