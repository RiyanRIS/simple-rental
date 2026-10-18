@extends('layouts.staff')

@section('content')
<div class="space-y-4">
    <!-- Tombol Kembali -->
    <a href="{{ route('staff.tasks') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800">
        &larr; Kembali ke Daftar Tugas
    </a>

    <!-- Header Task -->
    <div class="bg-gradient-to-r from-purple-800 to-slate-900 text-white p-4 rounded-2xl shadow-sm flex items-center justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-300">Form Pengembalian Unit (Jemput)</span>
                <span class="bg-purple-950/60 border border-purple-400/30 text-purple-200 font-extrabold px-2 py-0.5 rounded text-[10px]">{{ $booking->booking_code }}</span>
            </div>
            <h1 class="text-base font-extrabold text-white mt-1">{{ $booking->customer_name }}</h1>
            <p class="text-xs text-slate-200 mt-0.5">{{ $booking->category->name }} • <strong class="text-purple-300 font-extrabold">{{ $booking->motorcycle?->plate_number }}</strong></p>
        </div>
        @if($booking->category->image_url)
            <div class="w-16 h-12 bg-white/10 rounded-xl p-1 flex items-center justify-center shrink-0 border border-white/20">
                <img src="{{ $booking->category->image_url }}" alt="{{ $booking->category->name }}" class="max-h-full max-w-full object-contain">
            </div>
        @endif
    </div>

    <!-- Form Ceklis Pengembalian -->
    <form action="{{ route('staff.tasks.return.submit', $booking->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
        @csrf

        <!-- 1. Pengembalian Jaminan Fisik -->
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-sm space-y-2">
            <span class="font-bold text-emerald-900 uppercase tracking-wider text-[11px]">Pengembalian Berkas Jaminan:</span>
            <label class="flex items-start gap-2.5 cursor-pointer bg-emerald-50/70 p-2.5 rounded-xl border border-emerald-200">
                <input type="checkbox" name="physical_id_returned" value="1" required class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                <span class="text-emerald-900 font-bold leading-tight">
                    KTP Asli / Berkas Jaminan fisik telah diserahkan kembali kepada tamu dalam keadaan baik.
                </span>
            </label>
        </div>

        <!-- Rekap Serah Terima Saat Pengantaran (Acuan Pengembalian) -->
        <div class="bg-purple-50/80 border border-purple-200 p-3.5 rounded-2xl text-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="font-bold text-purple-900 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-purple-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Acuan Kelengkapan Saat Pengantaran:
                </span>
                @if($handover)
                    <span class="text-[10px] text-purple-800 font-semibold bg-purple-100/80 px-2 py-0.5 rounded-md">
                        Oleh: {{ $handover->staff?->name ?? 'Petugas Antar' }} ({{ $handover->inspected_at->format('d/m H:i') }})
                    </span>
                @else
                    <span class="text-[10px] text-slate-500 italic bg-white px-2 py-0.5 rounded">Standar Unit Fisik Motor</span>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                <div class="bg-white/80 p-2 rounded-xl border border-purple-100 text-center">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Helm Diserahkan</span>
                    <strong class="text-slate-900 text-xs">{{ $expectedHelmets }} Buah</strong>
                </div>
                <div class="bg-white/80 p-2 rounded-xl border border-purple-100 text-center">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Jas Hujan Diserahkan</span>
                    <strong class="text-slate-900 text-xs">{{ $expectedRaincoats }} Buah</strong>
                </div>
                <div class="bg-white/80 p-2 rounded-xl border border-purple-100 text-center">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Kunci & STNK</span>
                    <strong class="text-xs {{ $expectedKeyStnk ? 'text-emerald-700' : 'text-amber-700' }}">{{ $expectedKeyStnk ? 'Lengkap' : 'Tidak Lengkap' }}</strong>
                </div>
                <div class="bg-white/80 p-2 rounded-xl border border-purple-100 text-center">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Bensin Awal</span>
                    <strong class="text-slate-900 text-xs">{{ $expectedFuel }}</strong>
                </div>
            </div>
            <p class="text-[10px] text-purple-700 italic">
                *Standar lengkap pengembalian otomatis disesuaikan dengan barang yang benar-benar dibawa tamu saat serah terima pengantaran.
            </p>
        </div>

        <!-- 2. Pemeriksaan Kelengkapan Saat Kembali -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Kondisi Unit & Aksesoris Kembali:</span>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Jumlah Helm Kembali</label>
                    <select name="helmets_count" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
                        @for($i = 2; $i >= 0; $i--)
                            <option value="{{ $i }}" {{ old('helmets_count', $expectedHelmets) == $i ? 'selected' : '' }}>
                                @if($i == $expectedHelmets)
                                    {{ $i }} Buah Helm (Lengkap - Sesuai Serah Terima)
                                @elseif($i < $expectedHelmets)
                                    {{ $i }} Buah Helm (Kurang {{ $expectedHelmets - $i }} Buah / Hilang)
                                @else
                                    {{ $i }} Buah Helm (Lebih {{ $i - $expectedHelmets }} Buah)
                                @endif
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Jas Hujan di Bagasi</label>
                    <select name="raincoats_count" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
                        @for($i = 0; $i <= 3; $i++)
                            <option value="{{ $i }}" {{ old('raincoats_count', $expectedRaincoats) == $i ? 'selected' : '' }}>
                                @if($i == $expectedRaincoats)
                                    {{ $i }} Jas Hujan (Lengkap - Sesuai Serah Terima)
                                @elseif($i < $expectedRaincoats)
                                    {{ $i }} Jas Hujan (Kurang {{ $expectedRaincoats - $i }} / Hilang)
                                @else
                                    {{ $i }} Jas Hujan (Lebih {{ $i - $expectedRaincoats }})
                                @endif
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Indikator Bensin Kembali</label>
                    <select name="fuel_level" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
                        <option value="Full" {{ old('fuel_level', $expectedFuel) == 'Full' ? 'selected' : '' }}>Full (Penuh)</option>
                        <option value="3/4" {{ old('fuel_level', $expectedFuel) == '3/4' ? 'selected' : '' }}>3/4 Tangki</option>
                        <option value="1/2" {{ old('fuel_level', $expectedFuel) == '1/2' ? 'selected' : '' }}>1/2 Tangki</option>
                        <option value="1/4" {{ old('fuel_level', $expectedFuel) == '1/4' ? 'selected' : '' }}>1/4 Tangki</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Kunci & STNK Asli</label>
                    <select name="key_and_stnk_included" class="w-full p-2 rounded-lg border border-slate-300 bg-white text-xs">
                        @if($expectedKeyStnk)
                            <option value="1" {{ old('key_and_stnk_included', 1) == 1 ? 'selected' : '' }}>Lengkap Diterima (Kunci + STNK)</option>
                            <option value="0" {{ old('key_and_stnk_included', 1) == 0 ? 'selected' : '' }}>Kunci / STNK Hilang (Denda)</option>
                        @else
                            <option value="0" {{ old('key_and_stnk_included', 0) == 0 ? 'selected' : '' }}>Sesuai Saat Antar (Tanpa Kunci/STNK Lengkap)</option>
                            <option value="1" {{ old('key_and_stnk_included', 0) == 1 ? 'selected' : '' }}>Lengkap Diterima</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. Foto Dokumentasi Motor Kembali -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Foto Dokumentasi Pengembalian:</span>
            <div>
                <label class="block text-slate-600 font-semibold mb-1">Upload Foto Kondisi Motor Terkini</label>
                <input type="file" name="photos[]" multiple accept="image/*" class="block w-full text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-100 file:text-purple-800">
            </div>

            <div>
                <label class="block text-slate-600 font-semibold mb-1">Catatan Kerusakan Baru / Denda (Jika Ada)</label>
                <textarea name="damage_notes" rows="2" placeholder="Tulis aman jika tidak ada kerusakan. Contoh: Bodi aman, tidak ada baret baru..." class="w-full p-2 rounded-lg border border-slate-300 text-xs">Unit aman & lengkap.</textarea>
            </div>
        </div>

        <!-- Tombol Selesai Sewa -->
        <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-bold text-sm bg-gradient-to-r from-purple-700 to-indigo-800 hover:from-purple-800 hover:to-indigo-900 text-white shadow-lg shadow-purple-800/30 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Selesaikan Sewa & Kembalikan Motor ke Stok</span>
        </button>
        <p class="text-[10px] text-center text-slate-400">
            Setelah disubmit, unit motor akan otomatis berstatus Tersedia di sistem untuk disewa pelanggan berikutnya.
        </p>
    </form>
</div>
@endsection
