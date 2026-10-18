@extends('layouts.admin')

@section('content')
<div class="space-y-4">
    <!-- Header Controls: Search & Total -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <!-- Search Input -->
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="relative flex-1 max-w-md">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode booking, nama penyewa, atau no HP..."
                class="w-full pl-9 pr-8 py-2 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            @if(request('q'))
                <a href="{{ route('admin.bookings.index', $status ? ['status' => $status] : []) }}" 
                   title="Hapus pencarian" 
                   class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            @endif
        </form>

        <div class="text-xs text-slate-500 shrink-0 font-medium">
            Total: <span class="font-bold text-slate-900">{{ $bookings->total() }}</span> Reservasi
        </div>
    </div>

    <!-- Filter Tabs (Scrollable on small mobile only) -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
        @php
            $currentQ = request('q');
            $buildUrl = fn($tabStatus) => route('admin.bookings.index', array_filter(['status' => $tabStatus, 'q' => $currentQ]));
        @endphp

        <a href="{{ $buildUrl(null) }}"
            class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ !$status ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Semua ({{ $counts['all'] }})
        </a>
        <a href="{{ $buildUrl('pending') }}"
            class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ $status === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Menunggu Review ({{ $counts['pending'] }})
        </a>
        <a href="{{ $buildUrl('approved') }}"
            class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ $status === 'approved' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Disetujui ({{ $counts['approved'] }})
        </a>
        <a href="{{ $buildUrl('delivering') }}"
            class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ $status === 'delivering' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Sedang Jalan ({{ $counts['delivering'] }})
        </a>
        <a href="{{ $buildUrl('active') }}"
            class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ $status === 'active' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Aktif Disewa ({{ $counts['active'] }})
        </a>
        <a href="{{ $buildUrl('completed') }}"
            class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ $status === 'completed' ? 'bg-slate-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Selesai ({{ $counts['completed'] }})
        </a>
        <a href="{{ $buildUrl('rejected') }}"
            class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            Ditolak ({{ $counts['rejected'] }})
        </a>
    </div>

    <!-- DESKTOP / TABLET: Table View (No horizontal scrolling, fluid 100%) -->
    <div class="hidden md:block bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <table class="w-full text-left text-xs divide-y divide-slate-100">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider font-bold">
                <tr>
                    <th class="py-3 px-4 w-[28%]">Kode & Pelanggan</th>
                    <th class="py-3 px-4 w-[30%]">Motor & Jadwal Sewa</th>
                    <th class="py-3 px-4 w-[24%]">Lokasi & Biaya</th>
                    <th class="py-3 px-4 w-[18%]">Status Reservasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($bookings as $b)
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $b->customer_phone);
                        $waUrl = str_starts_with($cleanPhone, '0') 
                            ? '62' . substr($cleanPhone, 1) 
                            : (str_starts_with($cleanPhone, '62') ? $cleanPhone : '62' . $cleanPhone);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <!-- Col 1: Kode & Pelanggan -->
                        <td class="py-3 px-4">
                            <a href="{{ route('admin.bookings.show', $b->id) }}" 
                               title="Lihat detail reservasi {{ $b->booking_code }}"
                               class="inline-block font-mono font-extrabold text-xs text-emerald-700 hover:text-emerald-900 underline decoration-emerald-300 hover:decoration-emerald-700 decoration-2 underline-offset-2 transition-colors">
                                {{ $b->booking_code }}
                            </a>
                            <span class="font-bold text-slate-800 block truncate mt-1" title="{{ $b->customer_name }}">{{ $b->customer_name }}</span>
                            <a href="https://wa.me/{{ $waUrl }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-slate-500 hover:text-emerald-700 mt-0.5">
                                <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                <span>{{ $b->customer_phone }}</span>
                            </a>
                        </td>

                        <!-- Col 2: Motor & Jadwal Sewa -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-slate-900 text-xs">{{ $b->category->name }}</span>
                                @if($b->motorcycle)
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono text-[10px] font-bold">
                                        {{ $b->motorcycle->plate_number }}
                                    </span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-semibold">
                                        Belum diplot
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="font-medium text-slate-700">{{ $b->start_time->format('d M H:i') }} - {{ $b->end_time->format('d M H:i') }}</span>
                                <span class="text-slate-400">({{ $b->total_days }}hr)</span>
                            </div>
                        </td>

                        <!-- Col 3: Lokasi & Biaya -->
                        <td class="py-3 px-4">
                            <div class="text-slate-800 font-medium truncate max-w-[200px]" title="{{ $b->deliveryZone?->name }}">
                                {{ $b->deliveryZone?->name ?? 'Ambil Sendiri' }}
                            </div>
                            <div class="mt-1 flex items-center gap-1.5 text-xs">
                                <span class="font-extrabold text-slate-900">Rp {{ number_format($b->total_amount, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-emerald-700 font-bold">(DP: Rp {{ number_format($b->down_payment_amount, 0, ',', '.') }})</span>
                            </div>
                        </td>

                        <!-- Col 4: Status -->
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-flex items-center gap-1.5 {{ $b->status_badge_class }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ $b->formatted_status }}
                            </span>
                            @if($b->status === 'pending')
                                <span class="text-[10px] text-slate-500 font-medium block mt-0.5">Menunggu Konfirmasi DP</span>
                            @elseif($b->status === 'rejected')
                                <span class="text-[10px] text-rose-600 font-medium block mt-0.5">Ditolak</span>
                            @elseif($b->status === 'cancelled')
                                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Dibatalkan</span>
                            @elseif($b->remaining_amount > 0)
                                <span class="text-[10px] text-slate-600 font-medium block mt-0.5">Sisa Tagih: Rp {{ number_format($b->remaining_amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-[10px] text-slate-600 font-medium block mt-0.5">✓ Lunas (Serah Terima)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400">
                            <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="font-medium text-slate-500">Tidak ada data reservasi untuk filter atau pencarian ini.</p>
                            @if(request('q') || $status)
                                <a href="{{ route('admin.bookings.index') }}" class="text-xs text-emerald-600 hover:underline font-semibold mt-1 inline-block">Reset Filter & Pencarian</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($bookings->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

    <!-- MOBILE: Card View (Touch-friendly, zero horizontal scrolling) -->
    <div class="md:hidden space-y-3">
        @forelse($bookings as $b)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $b->customer_phone);
                $waUrl = str_starts_with($cleanPhone, '0') 
                    ? '62' . substr($cleanPhone, 1) 
                    : (str_starts_with($cleanPhone, '62') ? $cleanPhone : '62' . $cleanPhone);
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 space-y-3">
                <!-- Header Card: Kode & Status -->
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <a href="{{ route('admin.bookings.show', $b->id) }}" 
                           title="Lihat detail reservasi {{ $b->booking_code }}"
                           class="inline-block font-mono font-extrabold text-xs text-emerald-700 hover:text-emerald-900 underline decoration-emerald-300 hover:decoration-emerald-700 decoration-2 underline-offset-2 transition-colors py-0.5">
                            {{ $b->booking_code }}
                        </a>
                        <span class="text-[10px] text-slate-400 block mt-0.5">{{ $b->created_at->diffForHumans() }}</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border shrink-0 {{ $b->status_badge_class }}">
                        {{ $b->formatted_status }}
                    </span>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-2 text-xs border-y border-slate-100 py-2.5">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pelanggan</span>
                        <span class="font-bold text-slate-900 block truncate">{{ $b->customer_name }}</span>
                        <a href="https://wa.me/{{ $waUrl }}" target="_blank" class="text-emerald-700 text-[11px] font-semibold flex items-center gap-1 mt-0.5">
                            <span>WA: {{ $b->customer_phone }}</span>
                        </a>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Motor</span>
                        <span class="font-bold text-slate-900 block truncate">{{ $b->category->name }}</span>
                        @if($b->motorcycle)
                            <span class="text-[11px] font-mono font-bold text-emerald-700">{{ $b->motorcycle->plate_number }}</span>
                        @else
                            <span class="text-[11px] text-amber-600 italic">Belum diplot</span>
                        @endif
                    </div>
                </div>

                <!-- Detail Jadwal & Biaya -->
                <div class="text-[11px] text-slate-600 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Jadwal Sewa:</span>
                        <span class="font-medium text-slate-800">{{ $b->start_time->format('d M H:i') }} - {{ $b->end_time->format('d M H:i') }} ({{ $b->total_days }}hr)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Titik Antar:</span>
                        <span class="font-medium text-slate-800 truncate max-w-[180px]">{{ $b->deliveryZone?->name ?? 'Ambil Sendiri' }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                        <span class="text-slate-400">Total Biaya:</span>
                        <span class="font-bold text-slate-900">
                            Rp {{ number_format($b->total_amount, 0, ',', '.') }}
                            <span class="text-emerald-700 font-semibold">(DP: Rp {{ number_format($b->down_payment_amount, 0, ',', '.') }})</span>
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400">
                <p class="font-medium text-slate-500 text-xs">Tidak ada data reservasi untuk filter atau pencarian ini.</p>
                @if(request('q') || $status)
                    <a href="{{ route('admin.bookings.index') }}" class="text-xs text-emerald-600 hover:underline font-semibold mt-2 inline-block">Reset Filter & Pencarian</a>
                @endif
            </div>
        @endforelse

        @if($bookings->hasPages())
            <div class="pt-2">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
