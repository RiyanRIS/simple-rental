@extends('layouts.app')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>
<div x-data="bookingApp()" x-init="init()" class="pb-16">
    <!-- Hero Banner (Eye-pleasing green gradient) -->
    <section class="relative overflow-hidden bg-gradient-to-b from-emerald-800 via-teal-800 to-slate-900 text-white pt-12 pb-20 px-4 sm:px-6">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
        <div class="max-w-4xl mx-auto text-center relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-200 text-xs font-semibold mb-4">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Sistem Sewa Otomatis • Rental Motor Jogja
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white mb-4">
                Jelajahi Jogja Lebih Bebas, <br class="hidden sm:block">
                <span class="bg-gradient-to-r from-emerald-300 to-teal-200 bg-clip-text text-transparent">Pesan Motor Langsung Siap Antar</span>
            </h1>
            <p class="text-slate-300 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
                Pilihan motor matic siap pakai, bersih, dan berhelm ganda. Gratis antar-jemput di Stasiun Tugu & Lempuyangan langsung ke lokasi Anda.
            </p>
        </div>
    </section>

    <!-- Main Container -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 -mt-10 relative z-20">
        <form action="{{ route('booking.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs text-rose-800 space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-rose-900">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Mohon periksa kembali formulir reservasi Anda:</span>
                    </div>
                    <ul class="list-disc list-inside pl-1 text-[11px] space-y-0.5 text-rose-700">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- STEP 1: PILIH WAKTU SEWA (USER-FRIENDLY & MOBILE-FRIENDLY) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 sm:p-7 space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm">1</span>
                    <div>
                        <h2 class="font-bold text-slate-900 text-base sm:text-lg">Jadwal & Durasi Sewa</h2>
                        <p class="text-xs text-slate-500">Pilih tanggal dan jam sewa (tarif dihitung kelipatan 24 jam)</p>
                    </div>
                </div>

                <!-- Hidden inputs for backend compatibility -->
                <input type="hidden" name="start_time" :value="startTime">
                <input type="hidden" name="end_time" :value="endTime">

                <!-- 2 Kartu: Mulai Sewa & Selesai Sewa -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <!-- Kartu Mulai Sewa (Ambil Motor) -->
                    <div class="bg-slate-50/80 p-3.5 sm:p-4 rounded-xl border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                                Ambil Motor (Mulai)
                            </span>
                            <span class="text-[11px] text-emerald-800 font-semibold" x-text="formatIndoDate(startDate)"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tanggal Ambil</label>
                                <input type="date" x-model="startDate" min="{{ date('Y-m-d') }}" @change="onStartDateChange()" required
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Jam Ambil</label>
                                <select x-model="startTimeHour" @change="onStartTimeChange()" required
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow font-medium">
                                    <template x-for="time in timeSlots" :key="'start-' + time">
                                        <option :value="time" x-text="time + ' WIB'"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Selesai Sewa (Kembalikan Motor) -->
                    <div class="bg-slate-50/80 p-3.5 sm:p-4 rounded-xl border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
                                Kembalikan Motor (Selesai)
                            </span>
                            <span class="text-[11px] text-slate-700 font-semibold" x-text="formatIndoDate(endDate)"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tanggal Kembali</label>
                                <input type="date" x-model="endDate" :min="startDate" @change="onEndDateChange()" required
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Jam Kembali</label>
                                <select x-model="endTimeHour" @change="onEndTimeChange()" required
                                    class="w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow font-medium">
                                    <template x-for="time in timeSlots" :key="'end-' + time">
                                        <option :value="time" x-text="time + ' WIB'"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual Timeline & Durasi -->
                <div class="bg-emerald-50/70 border border-emerald-100 rounded-xl p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs">
                    <div class="flex items-center gap-2.5 text-emerald-950">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span>Durasi: <strong class="font-bold text-emerald-900" x-text="calculation.days + ' Hari (x24 Jam)'"></strong></span>
                                <template x-if="calculation.overtime_hours > 0">
                                    <span class="bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded text-[10px]" x-text="'+ ' + calculation.overtime_hours + ' Jam Tambahan'"></span>
                                </template>
                            </div>
                            <p class="text-[11px] text-emerald-800 font-medium mt-0.5" x-text="formatShortTimeline()"></p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right" x-show="loadingAvailability">
                        <span class="text-slate-500 text-[11px]">Mengecek ketersediaan motor...</span>
                    </div>
                </div>
            </div>

            <!-- STEP 2: PILIH KATEGORI MOTOR -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 sm:p-7">
                <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm">2</span>
                    <div>
                        <h2 class="font-bold text-slate-800 text-base sm:text-lg">Pilih Tipe Motor</h2>
                        <p class="text-xs text-slate-400">Pilih motor yang sesuai dengan kebutuhan perjalanan Anda</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <template x-for="cat in categories" :key="cat.id">
                        <label 
                            :class="{
                                'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-600/20 shadow-xs': selectedCategoryId == cat.id,
                                'border-slate-200 bg-white hover:border-emerald-300': selectedCategoryId != cat.id && cat.is_available,
                                'border-slate-200 bg-slate-50 opacity-60 cursor-not-allowed': !cat.is_available
                            }"
                            class="group relative flex flex-col p-3.5 rounded-xl border transition-all cursor-pointer">
                            <input type="radio" name="category_id" :value="cat.id" x-model="selectedCategoryId" :disabled="!cat.is_available" class="sr-only">
                            
                            <div class="flex items-start gap-3">
                                <!-- Thumbnail Motor Ringkas Seperti di Tiket -->
                                <div class="w-20 h-16 sm:w-22 sm:h-16 bg-slate-50 border border-slate-100 rounded-lg p-1 flex items-center justify-center shrink-0 overflow-hidden group-hover:bg-slate-100/80 transition-colors">
                                    <template x-if="cat.image_thumbnail_url || cat.image_url">
                                        <img :src="cat.image_thumbnail_url || cat.image_url" :alt="cat.name" loading="lazy" class="max-h-full max-w-full object-contain filter drop-shadow-xs transition-transform duration-200 group-hover:scale-105">
                                    </template>
                                    <template x-if="!cat.image_thumbnail_url && !cat.image_url">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4" />
                                        </svg>
                                    </template>
                                </div>

                                <!-- Detail Info Motor -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-1 mb-0.5">
                                        <h3 class="font-bold text-sm text-slate-900 truncate" x-text="cat.name"></h3>

                                        <!-- Status Badge -->
                                        <template x-if="cat.is_available">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                <span x-text="cat.available_count + ' unit'"></span>
                                            </span>
                                        </template>
                                        <template x-if="!cat.is_available">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 shrink-0">
                                                Habis
                                            </span>
                                        </template>
                                    </div>

                                    <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                        <span class="text-[11px] text-slate-500 font-semibold" x-text="cat.cc_capacity + ' cc'"></span>
                                        <template x-if="cat.has_seasonal">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Musim Libur
                                            </span>
                                        </template>
                                        <template x-if="!cat.has_seasonal && cat.has_weekend">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                Weekend
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Deskripsi Singkat jika ada -->
                                    <template x-if="cat.description">
                                        <p class="text-[11px] text-slate-500 line-clamp-1" x-text="cat.description"></p>
                                    </template>
                                </div>
                            </div>

                            <!-- Footer Tarif -->
                            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-400 text-[10px]">Tarif Reguler (24 jam):</span>
                                    <div class="font-bold text-emerald-700 text-xs sm:text-sm" x-text="formatRupiah(cat.daily_price)"></div>
                                    <template x-if="cat.weekend_price && cat.weekend_price > cat.daily_price">
                                        <div class="text-[10px] text-indigo-700 font-medium">
                                            Weekend: <span x-text="formatRupiah(cat.weekend_price)"></span>
                                        </div>
                                    </template>
                                </div>
                                <div class="text-right">
                                    <template x-if="cat.rental_subtotal">
                                        <div>
                                            <span class="text-slate-400 text-[10px]">Sewa <span x-text="calculation.days"></span> hari:</span>
                                            <div class="font-bold text-slate-900 text-xs sm:text-sm" x-text="formatRupiah(cat.rental_subtotal)"></div>
                                        </div>
                                    </template>
                                    <template x-if="calculation.overtime_hours > 0">
                                        <div class="text-[10px] text-amber-700">
                                            <span>+<span x-text="calculation.overtime_hours"></span> jam overtime</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            <!-- STEP 3: LOKASI ANTAR & JEMPUT -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 sm:p-7">
                <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm">3</span>
                    <div>
                        <h2 class="font-bold text-slate-800 text-base sm:text-lg">Lokasi Antar & Pengembalian</h2>
                        <p class="text-xs text-slate-400">Gratis antar ke stasiun atau pilih hotel/homestay tempat menginap</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Titik Antar -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700">Lokasi Antar (Tempat Terima Motor)</label>
                        <select name="delivery_zone_id" x-model="deliveryZoneId" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-sm focus:ring-2 focus:ring-emerald-500">
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" data-fee="{{ (float)$zone->fee }}">
                                    {{ $zone->name }} {{ $zone->fee > 0 ? '(+ Rp '.number_format($zone->fee, 0, ',', '.').')' : '(GRATIS)' }}
                                </option>
                            @endforeach
                        </select>
                        <textarea name="delivery_address" rows="2" required placeholder="Detail lokasi antar (contoh: Pintu Keluar Stasiun Tugu, nama hotel & nomor kamar, dll.)..."
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <!-- Titik Jemput -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700">Lokasi Jemput (Tempat Pengembalian Motor)</label>
                        <select name="return_zone_id" x-model="returnZoneId" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-sm focus:ring-2 focus:ring-emerald-500">
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" data-fee="{{ (float)$zone->fee }}">
                                    {{ $zone->name }} {{ $zone->fee > 0 ? '(+ Rp '.number_format($zone->fee, 0, ',', '.').')' : '(GRATIS)' }}
                                </option>
                            @endforeach
                        </select>
                        <textarea name="return_address" rows="2" required placeholder="Detail lokasi pengembalian motor (contoh: Lobi hotel, area parkir stasiun, dll.)..."
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>
                </div>
            </div>

            <!-- STEP 4: DATA DIRI & BERKAS PERSYARATAN -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 sm:p-7">
                <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm">4</span>
                    <div>
                        <h2 class="font-bold text-slate-800 text-base sm:text-lg">Data Penyewa & Dokumen Persyaratan</h2>
                        <p class="text-xs text-slate-400">Data Anda aman dan hanya digunakan untuk keperluan verifikasi sewa motor</p>
                    </div>
                </div>

                <!-- Data Diri Penyewa -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_name" required placeholder="Contoh: Dimas Surya"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-sm focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Aktif <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_phone" required placeholder="0812xxxxxxxx"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-sm focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor NIK KTP <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_id_number" required placeholder="16 digit NIK"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-sm focus:ring-2 focus:ring-emerald-500">
                        <input type="hidden" name="customer_id_type" value="KTP">
                    </div>
                </div>

                <!-- Kontak Darurat (Keluarga / Kerabat) -->
                <div class="bg-emerald-50/70 border border-emerald-200/90 p-4 rounded-xl mb-5 space-y-3">
                    <div>
                        <span class="text-xs font-extrabold text-emerald-950 uppercase tracking-wider block">Kontak Darurat</span>
                        <p class="text-[11px] text-emerald-800 mt-0.5">
                            Kontak keluarga atau teman dekat yang bisa dihubungi saat serah-terima unit motor atau keperluan darurat.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-emerald-950 mb-1">Nama Kontak Darurat <span class="text-rose-500">*</span></label>
                            <input type="text" name="emergency_contact_name" required placeholder="Contoh: Ibu Siti / Rian"
                                class="w-full px-3 py-2 rounded-lg border border-emerald-300 bg-white text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-emerald-950 mb-1">Hubungan / Relasi <span class="text-rose-500">*</span></label>
                            <select name="emergency_contact_relation" required
                                class="w-full px-3 py-2 rounded-lg border border-emerald-300 bg-white text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500">
                                <option value="">-- Pilih Hubungan --</option>
                                <option value="Orang Tua">Orang Tua (Ayah / Ibu)</option>
                                <option value="Pasangan">Pasangan (Suami / Istri)</option>
                                <option value="Saudara Kandung">Saudara Kandung (Kakak / Adik)</option>
                                <option value="Teman / Rekan Kerja">Teman / Rekan Kerja</option>
                                <option value="Keluarga Lainnya">Keluarga Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-emerald-950 mb-1">No. WhatsApp Darurat <span class="text-rose-500">*</span></label>
                            <input type="text" name="emergency_contact_phone" required placeholder="0813xxxxxxxx"
                                class="w-full px-3 py-2 rounded-lg border border-emerald-300 bg-white text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- Upload Dokumen dengan Preview -->
                <div class="bg-slate-50 p-4 sm:p-5 rounded-xl border border-slate-200/80 space-y-4">
                    <div>
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <p class="text-xs font-bold text-slate-800 uppercase tracking-wider">Unggah Dokumen Persyaratan:</p>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Maks. 5 MB / foto • Format: JPG, PNG, WEBP
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Pastikan foto terlihat jelas dan tulisan dapat terbaca. Berkas akan dikompres otomatis oleh sistem.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- 1. KTP -->
                        <div x-data="fileUploadPreview()" class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-slate-700">1. Foto KTP Asli <span class="text-rose-500">*</span></label>
                                <span class="text-[10px] text-slate-400">Harus jelas & terbaca</span>
                            </div>

                            <template x-if="errorMessage">
                                <div class="flex items-start gap-1.5 p-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[11px]">
                                    <svg class="w-3.5 h-3.5 shrink-0 mt-0.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="errorMessage"></span>
                                </div>
                            </template>

                            <div x-show="preview" x-cloak class="rounded-xl border border-emerald-300 overflow-hidden bg-slate-50">
                                <div class="h-32 sm:h-36 w-full flex items-center justify-center p-2 bg-slate-100/70">
                                    <img :src="preview" alt="Preview KTP" class="max-h-full max-w-full object-contain rounded-lg shadow-xs">
                                </div>
                                <div class="p-2 bg-white border-t border-emerald-100 flex items-center justify-between gap-2">
                                    <div class="min-w-0 flex items-center gap-1.5">
                                        <span class="truncate font-medium text-slate-700 text-[11px]" x-text="fileName"></span>
                                        <span class="text-[10px] text-slate-400 shrink-0" x-text="'(' + fileSizeFormatted + ')'"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="$refs.fileInput.click()" class="text-emerald-700 hover:text-emerald-800 text-[11px] font-semibold underline">Ganti</button>
                                        <button type="button" @click="clearFile()" class="text-rose-600 hover:text-rose-700 text-[11px] font-semibold underline">Hapus</button>
                                    </div>
                                </div>
                            </div>

                            <input type="file" x-ref="fileInput" name="id_card_photo" accept="image/*" required @change="handleFile($event)"
                                :class="preview ? 'hidden' : 'block w-full text-xs text-slate-500 file:mr-2.5 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200 cursor-pointer border border-slate-300 rounded-xl p-1 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500'">
                        </div>

                        <!-- 2. SIM C -->
                        <div x-data="fileUploadPreview()" class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-slate-700">2. Foto SIM C Asli <span class="text-rose-500">*</span></label>
                                <span class="text-[10px] text-slate-400">Masih berlaku</span>
                            </div>

                            <template x-if="errorMessage">
                                <div class="flex items-start gap-1.5 p-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[11px]">
                                    <svg class="w-3.5 h-3.5 shrink-0 mt-0.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="errorMessage"></span>
                                </div>
                            </template>

                            <div x-show="preview" x-cloak class="rounded-xl border border-emerald-300 overflow-hidden bg-slate-50">
                                <div class="h-32 sm:h-36 w-full flex items-center justify-center p-2 bg-slate-100/70">
                                    <img :src="preview" alt="Preview SIM C" class="max-h-full max-w-full object-contain rounded-lg shadow-xs">
                                </div>
                                <div class="p-2 bg-white border-t border-emerald-100 flex items-center justify-between gap-2">
                                    <div class="min-w-0 flex items-center gap-1.5">
                                        <span class="truncate font-medium text-slate-700 text-[11px]" x-text="fileName"></span>
                                        <span class="text-[10px] text-slate-400 shrink-0" x-text="'(' + fileSizeFormatted + ')'"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="$refs.fileInput.click()" class="text-emerald-700 hover:text-emerald-800 text-[11px] font-semibold underline">Ganti</button>
                                        <button type="button" @click="clearFile()" class="text-rose-600 hover:text-rose-700 text-[11px] font-semibold underline">Hapus</button>
                                    </div>
                                </div>
                            </div>

                            <input type="file" x-ref="fileInput" name="sim_c_photo" accept="image/*" required @change="handleFile($event)"
                                :class="preview ? 'hidden' : 'block w-full text-xs text-slate-500 file:mr-2.5 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200 cursor-pointer border border-slate-300 rounded-xl p-1 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500'">
                        </div>

                        <!-- 3. Tiket PP -->
                        <div x-data="fileUploadPreview()" class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-slate-700">3. Tiket Kereta / Pesawat PP</label>
                                <span class="text-[10px] text-slate-400">(Opsional jika ada)</span>
                            </div>

                            <template x-if="errorMessage">
                                <div class="flex items-start gap-1.5 p-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[11px]">
                                    <svg class="w-3.5 h-3.5 shrink-0 mt-0.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="errorMessage"></span>
                                </div>
                            </template>

                            <div x-show="preview" x-cloak class="rounded-xl border border-emerald-300 overflow-hidden bg-slate-50">
                                <div class="h-32 sm:h-36 w-full flex items-center justify-center p-2 bg-slate-100/70">
                                    <img :src="preview" alt="Preview Tiket Perjalanan" class="max-h-full max-w-full object-contain rounded-lg shadow-xs">
                                </div>
                                <div class="p-2 bg-white border-t border-emerald-100 flex items-center justify-between gap-2">
                                    <div class="min-w-0 flex items-center gap-1.5">
                                        <span class="truncate font-medium text-slate-700 text-[11px]" x-text="fileName"></span>
                                        <span class="text-[10px] text-slate-400 shrink-0" x-text="'(' + fileSizeFormatted + ')'"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="$refs.fileInput.click()" class="text-emerald-700 hover:text-emerald-800 text-[11px] font-semibold underline">Ganti</button>
                                        <button type="button" @click="clearFile()" class="text-rose-600 hover:text-rose-700 text-[11px] font-semibold underline">Hapus</button>
                                    </div>
                                </div>
                            </div>

                            <input type="file" x-ref="fileInput" name="travel_ticket_photo" accept="image/*" @change="handleFile($event)"
                                :class="preview ? 'hidden' : 'block w-full text-xs text-slate-500 file:mr-2.5 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer border border-slate-300 rounded-xl p-1 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500'">
                        </div>

                        <!-- 4. Bukti Hotel -->
                        <div x-data="fileUploadPreview()" class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-slate-700">4. Bukti Penginapan / Hotel</label>
                                <span class="text-[10px] text-slate-400">(Opsional jika ada)</span>
                            </div>

                            <template x-if="errorMessage">
                                <div class="flex items-start gap-1.5 p-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[11px]">
                                    <svg class="w-3.5 h-3.5 shrink-0 mt-0.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="errorMessage"></span>
                                </div>
                            </template>

                            <div x-show="preview" x-cloak class="rounded-xl border border-emerald-300 overflow-hidden bg-slate-50">
                                <div class="h-32 sm:h-36 w-full flex items-center justify-center p-2 bg-slate-100/70">
                                    <img :src="preview" alt="Preview Bukti Penginapan" class="max-h-full max-w-full object-contain rounded-lg shadow-xs">
                                </div>
                                <div class="p-2 bg-white border-t border-emerald-100 flex items-center justify-between gap-2">
                                    <div class="min-w-0 flex items-center gap-1.5">
                                        <span class="truncate font-medium text-slate-700 text-[11px]" x-text="fileName"></span>
                                        <span class="text-[10px] text-slate-400 shrink-0" x-text="'(' + fileSizeFormatted + ')'"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="$refs.fileInput.click()" class="text-emerald-700 hover:text-emerald-800 text-[11px] font-semibold underline">Ganti</button>
                                        <button type="button" @click="clearFile()" class="text-rose-600 hover:text-rose-700 text-[11px] font-semibold underline">Hapus</button>
                                    </div>
                                </div>
                            </div>

                            <input type="file" x-ref="fileInput" name="hotel_booking_photo" accept="image/*" @change="handleFile($event)"
                                :class="preview ? 'hidden' : 'block w-full text-xs text-slate-500 file:mr-2.5 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer border border-slate-300 rounded-xl p-1 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500'">
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 5: PEMBAYARAN DP & BUKTI TRANSFER -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 sm:p-7">
                <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm">5</span>
                    <div>
                        <h2 class="font-bold text-slate-800 text-base sm:text-lg">Pembayaran Uang Muka (DP)</h2>
                        <p class="text-xs text-slate-400">Minimal DP Rp 50.000 untuk mengamankan motor di jadwal pilihan Anda</p>
                    </div>
                </div>

                <!-- Info Rekening -->
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl p-4 mb-4 text-xs text-slate-700">
                    <p class="font-bold text-emerald-900 mb-2">Silakan transfer DP ke salah satu rekening resmi kami:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="bg-white p-2.5 rounded-lg border border-emerald-100">
                            <span class="font-bold text-slate-900">BCA:</span> <strong>8020-9912-33</strong><br>
                            <span class="text-slate-500">a.n. Rental Motor Jogja</span>
                        </div>
                        <div class="bg-white p-2.5 rounded-lg border border-emerald-100">
                            <span class="font-bold text-slate-900">Mandiri:</span> <strong>137-00-1928-4421</strong><br>
                            <span class="text-slate-500">a.n. Rental Motor Jogja</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nominal DP yang Ditransfer (Rp)</label>
                        <input type="number" name="down_payment_amount" x-model="dpAmount" min="50000" step="10000" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-sm focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[11px] text-slate-400 mt-1">Sisa sewa dibayarkan saat serah-terima motor di lokasi.</p>
                    </div>

                    <!-- Bukti Transfer with Preview -->
                    <div x-data="fileUploadPreview()" class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700">Unggah Bukti Transfer <span class="text-rose-500">*</span></label>
                            <span class="text-[10px] text-slate-400">Maks. 5 MB (Struk ATM / m-Banking)</span>
                        </div>

                        <template x-if="errorMessage">
                            <div class="flex items-start gap-1.5 p-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[11px]">
                                <svg class="w-3.5 h-3.5 shrink-0 mt-0.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span x-text="errorMessage"></span>
                            </div>
                        </template>

                        <div x-show="preview" x-cloak class="rounded-xl border border-emerald-300 overflow-hidden bg-slate-50">
                            <div class="h-32 sm:h-36 w-full flex items-center justify-center p-2 bg-slate-100/70">
                                <img :src="preview" alt="Preview Bukti Transfer" class="max-h-full max-w-full object-contain rounded-lg shadow-xs">
                            </div>
                            <div class="p-2 bg-white border-t border-emerald-100 flex items-center justify-between gap-2">
                                <div class="min-w-0 flex items-center gap-1.5">
                                    <span class="truncate font-medium text-slate-700 text-[11px]" x-text="fileName"></span>
                                    <span class="text-[10px] text-slate-400 shrink-0" x-text="'(' + fileSizeFormatted + ')'"></span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="$refs.fileInput.click()" class="text-emerald-700 hover:text-emerald-800 text-[11px] font-semibold underline">Ganti</button>
                                    <button type="button" @click="clearFile()" class="text-rose-600 hover:text-rose-700 text-[11px] font-semibold underline">Hapus</button>
                                </div>
                            </div>
                        </div>

                        <input type="file" x-ref="fileInput" name="payment_proof" accept="image/*" required @change="handleFile($event)"
                            :class="preview ? 'hidden' : 'block w-full text-xs text-slate-500 file:mr-2.5 file:py-2.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer border border-slate-300 rounded-xl p-1 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500'">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Mohon siapkan helm ukuran XL, tiba di Stasiun Tugu jam 09.30"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- RINGKASAN BIAYA & SUBMIT BUTTON -->
            <div class="bg-slate-50 rounded-2xl p-5 sm:p-7 border border-slate-200/90 shadow-xs">
                <h3 class="font-bold text-sm tracking-wide uppercase text-slate-800 mb-3">Ringkasan Biaya Sewa</h3>
                
                <!-- Unit Terpilih Preview -->
                <template x-if="selectedCategory">
                    <div class="flex items-center gap-3.5 p-3 mb-4 rounded-xl bg-white border border-slate-200 shadow-2xs">
                        <div class="w-16 h-12 flex items-center justify-center bg-slate-50 rounded-lg p-1 shrink-0 overflow-hidden">
                            <template x-if="selectedCategory.image_thumbnail_url || selectedCategory.image_url">
                                <img :src="selectedCategory.image_thumbnail_url || selectedCategory.image_url" :alt="selectedCategory.name" class="max-h-full max-w-full object-contain">
                            </template>
                            <template x-if="!selectedCategory.image_thumbnail_url && !selectedCategory.image_url">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </template>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Unit Pilihan Anda</span>
                            <h4 class="font-bold text-xs text-slate-900 truncate" x-text="selectedCategory.name"></h4>
                            <span class="text-[11px] text-slate-500" x-text="selectedCategory.cc_capacity + ' cc'"></span>
                        </div>
                    </div>
                </template>
                
                <div class="space-y-2 text-xs border-b border-slate-200 pb-4 mb-4">
                    <div class="flex justify-between text-slate-600">
                        <span>Sewa Motor (<span x-text="calculation.days"></span> hari):</span>
                        <span class="font-bold text-slate-800" x-text="formatRupiah(rentalSubtotal)"></span>
                    </div>

                    <!-- Breakdown Kalender Harian Transparan -->
                    <template x-if="selectedCategory && selectedCategory.daily_breakdown && selectedCategory.daily_breakdown.length > 0">
                        <div x-data="{ showDailyBreakdown: false }" class="bg-white/90 rounded-xl p-2.5 border border-slate-200 shadow-2xs">
                            <button type="button" @click="showDailyBreakdown = !showDailyBreakdown" class="w-full flex items-center justify-between text-[11px] text-slate-700 hover:text-emerald-700 font-semibold focus:outline-none">
                                <span class="flex items-center gap-1.5 flex-wrap">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Rincian Tarif Kalender (<span x-text="calculation.days"></span> hari)</span>
                                    <template x-if="selectedCategory?.has_seasonal">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-amber-100 text-amber-800 font-bold">Peak Season</span>
                                    </template>
                                    <template x-if="!selectedCategory?.has_seasonal && selectedCategory?.has_weekend">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-indigo-50 text-indigo-700 font-bold">Weekend</span>
                                    </template>
                                </span>
                                <span class="text-[10px] text-emerald-700 flex items-center gap-0.5 shrink-0">
                                    <span x-text="showDailyBreakdown ? 'Tutup' : 'Lihat Rincian'"></span>
                                    <svg class="w-3 h-3 transition-transform duration-200" :class="showDailyBreakdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </button>
                            <div x-show="showDailyBreakdown" x-cloak class="mt-2 pt-2 border-t border-slate-100 space-y-1.5 text-[11px]">
                                <template x-for="item in selectedCategory.daily_breakdown" :key="item.day_number">
                                    <div class="flex items-center justify-between text-slate-600">
                                        <div>
                                            <span class="font-medium text-slate-800" x-text="item.day_name + ', ' + item.date"></span>
                                            <span class="text-[10px] text-slate-400 block sm:inline sm:ml-1" x-text="'(' + item.reason + ')'"></span>
                                        </div>
                                        <span class="font-semibold text-slate-800" x-text="formatRupiah(item.price)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template x-if="calculation.overtime_hours > 0">
                        <div class="flex justify-between text-amber-700">
                            <span>Kelebihan Jam (<span x-text="calculation.overtime_hours"></span> jam x <span x-text="formatRupiah(selectedOvertimePrice)"></span>):</span>
                            <span class="font-bold" x-text="formatRupiah(overtimeSubtotal)"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Antar:</span>
                        <span class="font-bold text-slate-800" x-text="deliveryFee == 0 ? 'GRATIS' : formatRupiah(deliveryFee)"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Jemput:</span>
                        <span class="font-bold text-slate-800" x-text="returnFee == 0 ? 'GRATIS' : formatRupiah(returnFee)"></span>
                    </div>
                    <div class="flex justify-between text-slate-900 pt-2.5 border-t border-slate-200 font-extrabold text-sm">
                        <span>Total Biaya Sewa:</span>
                        <span class="text-emerald-700" x-text="formatRupiah(totalAmount)"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Uang Muka (DP):</span>
                        <span class="font-bold text-emerald-700" x-text="formatRupiah(dpAmount)"></span>
                    </div>
                    <div class="flex justify-between text-amber-900 font-extrabold pt-2 border-t border-slate-200 text-sm">
                        <span>Sisa Pembayaran Saat Serah Terima:</span>
                        <span class="text-amber-800" x-text="formatRupiah(remainingAmount)"></span>
                    </div>
                </div>

                <button type="submit" 
                    :disabled="!selectedCategoryId"
                    class="w-full py-3.5 px-6 rounded-xl font-bold text-sm bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Kirim Reservasi Sekarang</span>
                </button>
                <p class="text-[11px] text-center text-slate-400 mt-2.5">
                    Setelah pemesanan dikirim, tim kami akan memverifikasi bukti transfer dan menyiapkan motor Anda.
                </p>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function fileUploadPreview() {
    return {
        preview: null,
        fileName: '',
        fileSizeFormatted: '',
        errorMessage: null,

        handleFile(e) {
            const file = e.target.files && e.target.files[0];
            this.errorMessage = null;

            if (this.preview) {
                URL.revokeObjectURL(this.preview);
                this.preview = null;
            }

            if (!file) {
                this.clearFile();
                return;
            }

            // Validasi client-side: Harus file gambar
            if (!file.type.startsWith('image/')) {
                this.errorMessage = 'Format file tidak didukung. Harap pilih foto (JPG, PNG, atau WEBP).';
                this.clearFileInputOnly();
                return;
            }

            // Validasi client-side: Maksimal 5 MB (5 * 1024 * 1024 bytes)
            const maxBytes = 5 * 1024 * 1024;
            if (file.size > maxBytes) {
                const currentMb = (file.size / (1024 * 1024)).toFixed(1);
                this.errorMessage = `Ukuran foto terlalu besar (${currentMb} MB). Maksimal ukuran yang diizinkan adalah 5 MB.`;
                this.clearFileInputOnly();
                return;
            }

            // Format ukuran foto yang dipilih
            if (file.size < 1024 * 1024) {
                this.fileSizeFormatted = Math.round(file.size / 1024) + ' KB';
            } else {
                this.fileSizeFormatted = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
            }

            this.fileName = file.name;
            this.preview = URL.createObjectURL(file);
        },

        clearFileInputOnly() {
            this.preview = null;
            this.fileName = '';
            this.fileSizeFormatted = '';
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        clearFile() {
            if (this.preview) {
                URL.revokeObjectURL(this.preview);
            }
            this.preview = null;
            this.fileName = '';
            this.fileSizeFormatted = '';
            this.errorMessage = null;
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        }
    };
}

function bookingApp() {
    const rawDefaultStart = '{{ $defaultStart }}';
    const rawDefaultEnd = '{{ $defaultEnd }}';
    const initialStartDate = rawDefaultStart.includes('T') ? rawDefaultStart.split('T')[0] : '{{ date('Y-m-d') }}';
    const initialStartTimeHour = rawDefaultStart.includes('T') ? rawDefaultStart.split('T')[1].substring(0, 5) : '09:00';
    const initialEndDate = rawDefaultEnd.includes('T') ? rawDefaultEnd.split('T')[0] : initialStartDate;
    const initialEndTimeHour = rawDefaultEnd.includes('T') ? rawDefaultEnd.split('T')[1].substring(0, 5) : initialStartTimeHour;

    return {
        startDate: initialStartDate,
        startTimeHour: initialStartTimeHour,
        endDate: initialEndDate,
        endTimeHour: initialEndTimeHour,
        timeSlots: [
            '06:00', '06:30', '07:00', '07:30', '08:00', '08:30',
            '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
            '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
            '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
            '18:00', '18:30', '19:00', '19:30', '20:00', '20:30',
            '21:00', '21:30', '22:00'
        ],
        selectedCategoryId: {{ $categories->first()?->id ?? 'null' }},
        deliveryZoneId: {{ $zones->first()?->id ?? '1' }},
        returnZoneId: {{ $zones->first()?->id ?? '1' }},
        dpAmount: 50000,
        loadingAvailability: false,
        calculation: @json($initialCalculation ?? ['days' => 1, 'overtime_hours' => 0]),
        categories: @json($categoriesJson),
        zones: @json($zones),

        get startTime() {
            return `${this.startDate}T${this.startTimeHour}`;
        },

        get endTime() {
            return `${this.endDate}T${this.endTimeHour}`;
        },

        init() {
            this.fetchAvailability();
        },

        onStartDateChange() {
            if (!this.startDate) return;
            // Jika tanggal selesai lebih kecil dari tanggal mulai, otomatis jadikan H+1
            const s = new Date(this.startDate + 'T00:00:00');
            const e = new Date(this.endDate + 'T00:00:00');
            if (isNaN(e.getTime()) || e < s) {
                const newEnd = new Date(s);
                newEnd.setDate(newEnd.getDate() + 1);
                this.endDate = this.formatDateIso(newEnd);
            }
            this.fetchAvailability();
        },

        onStartTimeChange() {
            this.fetchAvailability();
        },

        onEndDateChange() {
            if (!this.endDate) return;
            const s = new Date(this.startDate + 'T00:00:00');
            const e = new Date(this.endDate + 'T00:00:00');
            if (e < s) {
                this.endDate = this.startDate;
            }
            this.fetchAvailability();
        },

        onEndTimeChange() {
            this.fetchAvailability();
        },

        formatDateIso(dateObj) {
            const pad = (n) => String(n).padStart(2, '0');
            return `${dateObj.getFullYear()}-${pad(dateObj.getMonth() + 1)}-${pad(dateObj.getDate())}`;
        },

        formatIndoDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr + 'T00:00:00');
            if (isNaN(d.getTime())) return '';
            const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        },

        formatShortTimeline() {
            if (!this.startDate || !this.endDate) return '';
            const s = new Date(this.startDate + 'T00:00:00');
            const e = new Date(this.endDate + 'T00:00:00');
            if (isNaN(s.getTime()) || isNaN(e.getTime())) return '';
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${s.getDate()} ${months[s.getMonth()]} ${this.startTimeHour} WIB ➔ ${e.getDate()} ${months[e.getMonth()]} ${this.endTimeHour} WIB`;
        },

        get selectedCategory() {
            return this.categories.find(c => c.id == this.selectedCategoryId) || null;
        },

        get selectedDailyPrice() {
            return this.selectedCategory ? this.selectedCategory.daily_price : 0;
        },

        get selectedOvertimePrice() {
            return this.selectedCategory ? this.selectedCategory.hourly_overtime_price : 0;
        },

        get deliveryFee() {
            const z = this.zones.find(x => x.id == this.deliveryZoneId);
            return z ? parseFloat(z.fee) : 0;
        },

        get returnFee() {
            const z = this.zones.find(x => x.id == this.returnZoneId);
            return z ? parseFloat(z.fee) : 0;
        },

        get rentalSubtotal() {
            if (this.selectedCategory && this.selectedCategory.rental_subtotal !== undefined) {
                return parseFloat(this.selectedCategory.rental_subtotal);
            }
            return this.selectedDailyPrice * this.calculation.days;
        },

        get overtimeSubtotal() {
            if (this.selectedCategory && this.selectedCategory.overtime_subtotal !== undefined) {
                return parseFloat(this.selectedCategory.overtime_subtotal);
            }
            return this.selectedOvertimePrice * this.calculation.overtime_hours;
        },

        get totalAmount() {
            return this.rentalSubtotal + this.overtimeSubtotal + this.deliveryFee + this.returnFee;
        },

        get remainingAmount() {
            return Math.max(0, this.totalAmount - (parseFloat(this.dpAmount) || 0));
        },

        async fetchAvailability() {
            if (!this.startTime || !this.endTime) return;
            const s = new Date(this.startTime);
            const e = new Date(this.endTime);
            if (isNaN(s.getTime()) || isNaN(e.getTime()) || e <= s) return;

            this.loadingAvailability = true;

            try {
                const res = await fetch('{{ route('booking.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        start_time: this.startTime,
                        end_time: this.endTime
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.calculation = data.calculation;
                    this.categories = data.categories;

                    // Jika kategori yang dipilih saat ini menjadi tidak tersedia, pilih yang pertama yang tersedia
                    if (this.selectedCategory && !this.selectedCategory.is_available) {
                        const availableFirst = this.categories.find(c => c.is_available);
                        if (availableFirst) {
                            this.selectedCategoryId = availableFirst.id;
                        }
                    }
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.loadingAvailability = false;
            }
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    };
}
</script>
@endpush
@endsection
