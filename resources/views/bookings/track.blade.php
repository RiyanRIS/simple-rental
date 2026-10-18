@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-4 py-12">
    <div class="text-center mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Cek Status Pemesanan</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-2">Cukup masukkan nomor WhatsApp pemesan atau kode booking Anda</p>
    </div>

    <!-- Shortcut Tiket Terakhir di Perangkat Ini (Tanpa Perlu Ketik) -->
    <div x-data="{ recent: null }" x-init="try { recent = JSON.parse(localStorage.getItem('jogja_recent_booking')) } catch(e) {}" x-show="recent" class="bg-emerald-50 border border-emerald-200 p-4 rounded-2xl mb-6 text-xs flex items-center justify-between gap-3 shadow-xs">
        <div>
            <span class="text-[10px] uppercase font-bold text-emerald-800 tracking-wider block">Pemesanan Terakhir di Perangkat Ini:</span>
            <p class="font-extrabold text-slate-900 mt-0.5" x-text="recent ? recent.code + ' (' + recent.unit + ')' : ''"></p>
        </div>
        <a :href="recent ? '/booking/tiket/' + recent.code : '#'" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors shrink-0">
            Buka Tiket &rarr;
        </a>
    </div>

    <!-- Form Pencarian -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-6">
        <form action="{{ route('booking.track') }}" method="GET" class="space-y-3">
            <div class="flex gap-2">
                <input type="text" name="q" value="{{ $search }}" required placeholder="Nomor WA (misal: 0812xxxx) atau Kode Booking"
                    class="flex-1 px-4 py-3 rounded-xl border border-slate-300 text-slate-800 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                <button type="submit" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Cari</span>
                </button>
            </div>
            <p class="text-[11px] text-slate-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Bisa ketik <strong>nomor WhatsApp</strong> langsung atau <strong>kode booking tanpa tanda strip (-)</strong>.</span>
            </p>
        </form>
    </div>

    <!-- Hasil Pencarian -->
    @if($search)
        @if($bookings->count() > 1)
            <!-- Multi-hasil jika mencari dengan nomor HP yang memiliki beberapa pesanan -->
            <div class="space-y-3 mb-6">
                <p class="text-xs font-bold text-slate-700">Ditemukan {{ $bookings->count() }} reservasi untuk pencarian ini:</p>
                @foreach($bookings as $item)
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-emerald-300 transition-colors">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-slate-900 text-sm">{{ $item->booking_code }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $item->status_badge_class }}">
                                    {{ $item->formatted_status }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 font-semibold mt-1">
                                {{ $item->category->name }} &bull; {{ $item->start_time->format('d M Y, H:i') }} WIB
                            </p>
                        </div>
                        <a href="{{ route('booking.ticket', $item->booking_code) }}"
                            class="px-4 py-2 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-700 text-white transition-colors text-center shrink-0 shadow-xs">
                            Buka Tiket &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        @elseif($booking)
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-200">
                <div class="flex items-center gap-3.5 mb-4 pb-3 border-b border-slate-100">
                    @if($booking->category->image_url)
                        <div class="w-16 h-12 bg-slate-50 border border-slate-200 rounded-lg p-1 flex items-center justify-center shrink-0">
                            <img src="{{ $booking->category->image_url }}" alt="{{ $booking->category->name }}" class="max-h-full max-w-full object-contain">
                        </div>
                    @endif
                    <div>
                        <span class="text-xs font-bold text-slate-400">Kode Booking</span>
                        <h2 class="text-lg font-extrabold text-slate-900">{{ $booking->booking_code }}</h2>
                    </div>
                    <span class="ml-auto px-3 py-1 rounded-full text-xs font-bold {{ $booking->status_badge_class }}">
                        {{ $booking->formatted_status }}
                    </span>
                </div>

                <div class="space-y-2 text-xs text-slate-600 mb-5">
                    <p><strong>Nama Pemesan:</strong> {{ $booking->customer_name }}</p>
                    <p><strong>Pilihan Motor:</strong> {{ $booking->category->name }}</p>
                    @if($booking->motorcycle)
                        <p><strong>Plat Nomor:</strong> <span class="font-bold text-emerald-700">{{ $booking->motorcycle->plate_number }}</span></p>
                    @endif
                    <p><strong>Jadwal Antar:</strong> {{ $booking->start_time->format('d M Y, H:i') }} WIB ({{ $booking->deliveryZone?->name }})</p>
                    <p><strong>Kurir Pengantar:</strong> {{ $booking->deliveryStaff?->name ?? 'Akan diinfokan saat pengantaran' }}</p>
                    @if(in_array($booking->status, ['active', 'returning', 'completed']))
                        <p><strong>Jadwal Jemput:</strong> {{ $booking->end_time->format('d M Y, H:i') }} WIB ({{ $booking->returnZone?->name }})</p>
                        <p><strong>Kurir Penjemput:</strong> 
                            @if($booking->returnStaff)
                                <span class="font-semibold text-slate-800">{{ $booking->returnStaff->name }}</span>
                                @if($booking->status === 'returning')
                                    <span class="text-[10px] text-purple-700 font-bold bg-purple-50 px-2 py-0.5 rounded ml-1 animate-pulse">Menuju Lokasi</span>
                                @endif
                            @elseif($booking->status === 'returning')
                                <span class="text-purple-700 font-semibold">Sedang dialokasikan tim operasional</span>
                            @else
                                <span class="text-slate-500">Akan dihubungi saat waktu sewa habis</span>
                            @endif
                        </p>
                    @endif
                </div>

                <a href="{{ route('booking.ticket', $booking->booking_code) }}"
                    class="block w-full text-center py-2.5 px-4 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-sm">
                    Buka Nota / Tiket Lengkap &rarr;
                </a>
            </div>
        @else
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-6 rounded-2xl text-center text-xs space-y-1">
                <p class="font-bold text-sm">Pesanan Tidak Ditemukan</p>
                <p class="text-rose-600">Pastikan nomor WhatsApp atau kode booking yang Anda masukkan sudah benar.</p>
            </div>
        @endif
    @endif
</div>
@endsection
