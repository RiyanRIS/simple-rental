<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Sewa Motor - {{ $startDate }} s/d {{ $endDate }}</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Compiled CSS & Tailwind -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
        }

        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
                font-size: 11px;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
            @page {
                size: A4 landscape;
                margin: 12mm 15mm 15mm 15mm;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8 antialiased">
    <!-- Bar Aksi Tombol Cetak (Tidak ikut tercetak) -->
    <div class="max-w-6xl mx-auto mb-6 p-4 bg-white rounded-xl border border-slate-200 shadow-sm flex items-center justify-between no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.reports.index') }}" class="px-3.5 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                &larr; Kembali ke Panel Laporan
            </a>
            <span class="text-xs text-slate-400">|</span>
            <span class="text-xs text-slate-600 font-medium">Pratinjau Cetak / PDF</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / Simpan PDF (Ctrl+P)</span>
            </button>
        </div>
    </div>

    <!-- Kontainer Lembar Cetak -->
    <div class="max-w-6xl mx-auto bg-white p-6 sm:p-10 rounded-2xl border border-slate-200/80 shadow-xs print:border-0 print:p-0 print:shadow-none">
        <!-- KOP SURAT FORMAL -->
        <div class="border-b-2 border-slate-800 pb-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">JOGJAMOTOR RENTAL</h1>
                    <p class="text-xs font-semibold text-emerald-800">Layanan Sewa Motor Profesional & Terpercaya di Yogyakarta</p>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Jl. Pasar Kembang No. 22, Sosromenduran, Gedong Tengen, Kota Yogyakarta (Depan Stasiun Tugu)<br>
                        Telp / WhatsApp: 0812-3456-7890 • Website: sewamotorjogja.com
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 rounded-md text-xs font-black uppercase tracking-wider bg-slate-100 text-slate-800 border border-slate-300">
                        Dokumen Resmi
                    </span>
                    <p class="text-[11px] text-slate-400 mt-2">
                        Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} WIB<br>
                        Oleh: {{ Auth::user()->name }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Judul & Info Periode Laporan -->
        <div class="text-center mb-6">
            <h2 class="text-lg font-black text-slate-900 uppercase tracking-wide">
                Laporan Rekapitulasi Transaksi Sewa & Keuangan
            </h2>
            <p class="text-xs text-slate-600 font-semibold mt-0.5">
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
                @if($status !== 'all')
                    • Status: <span class="capitalize font-bold">{{ $status }}</span>
                @endif
            </p>
        </div>

        <!-- Kotak Ringkasan Metrik (Finansial Summary) -->
        <div class="grid grid-cols-4 gap-3 mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs">
            <div>
                <span class="text-[10px] text-slate-500 font-bold uppercase block">Total Transaksi Valid</span>
                <span class="text-base font-extrabold text-slate-900">{{ $metrics['valid_count'] }} Reservasi</span>
                <span class="text-[10px] text-slate-400 block">(Total semua: {{ $metrics['total_bookings'] }})</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 font-bold uppercase block">Total Omset Bruto</span>
                <span class="text-base font-extrabold text-emerald-800">Rp {{ number_format($metrics['total_gross'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block">Sewa: Rp {{ number_format($metrics['total_rental'], 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 font-bold uppercase block">Overtime & Ongkir</span>
                <span class="text-base font-extrabold text-slate-800">Rp {{ number_format($metrics['total_overtime'] + $metrics['total_delivery'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block">OT: Rp {{ number_format($metrics['total_overtime'], 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 font-bold uppercase block">Total Kas Diterima</span>
                <span class="text-base font-extrabold text-slate-900">Rp {{ number_format($metrics['total_dp'] + $metrics['total_paid'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block">DP: Rp {{ number_format($metrics['total_dp'], 0, ',', '.') }} • Lunas: Rp {{ number_format($metrics['total_paid'], 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Tabel Rincian Transaksi -->
        <div class="mb-8 overflow-hidden rounded-lg border border-slate-300">
            <table class="w-full text-left text-[11px] border-collapse">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-300">
                    <tr>
                        <th class="p-2 border-r border-slate-300 text-center w-8">No</th>
                        <th class="p-2 border-r border-slate-300">Kode & Tgl</th>
                        <th class="p-2 border-r border-slate-300">Pelanggan</th>
                        <th class="p-2 border-r border-slate-300">Motor / Plat</th>
                        <th class="p-2 border-r border-slate-300">Jadwal Sewa</th>
                        <th class="p-2 border-r border-slate-300 text-center">Durasi</th>
                        <th class="p-2 border-r border-slate-300 text-right">Subtotal Sewa</th>
                        <th class="p-2 border-r border-slate-300 text-right">Total Tagihan</th>
                        <th class="p-2 border-r border-slate-300 text-right">DP Masuk</th>
                        <th class="p-2 border-r border-slate-300 text-right">Sisa Tagihan</th>
                        <th class="p-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($bookings as $idx => $b)
                        <tr class="{{ $idx % 2 === 1 ? 'bg-slate-50/50' : 'bg-white' }}">
                            <td class="p-2 border-r border-slate-200 text-center text-slate-500">{{ $idx + 1 }}</td>
                            <td class="p-2 border-r border-slate-200 whitespace-nowrap">
                                <strong>{{ $b->booking_code }}</strong><br>
                                <span class="text-[10px] text-slate-400">{{ $b->created_at ? $b->created_at->format('d/m/Y') : '-' }}</span>
                            </td>
                            <td class="p-2 border-r border-slate-200">
                                <strong>{{ $b->customer_name }}</strong><br>
                                <span class="text-[10px] text-slate-500">{{ $b->customer_phone }}</span>
                            </td>
                            <td class="p-2 border-r border-slate-200">
                                {{ $b->category?->name ?? '-' }}<br>
                                <span class="text-[10px] font-bold text-slate-700">{{ $b->motorcycle?->plate_number ?? 'Belum dialokasikan' }}</span>
                            </td>
                            <td class="p-2 border-r border-slate-200 whitespace-nowrap text-[10px]">
                                {{ $b->start_time->format('d/m/y H:i') }}<br>
                                s/d {{ $b->end_time->format('d/m/y H:i') }}
                            </td>
                            <td class="p-2 border-r border-slate-200 text-center font-bold">
                                {{ $b->total_days }} Hari
                                @if($b->overtime_hours > 0)
                                    <span class="block text-[9px] text-amber-700">+{{ $b->overtime_hours }}j OT</span>
                                @endif
                            </td>
                            <td class="p-2 border-r border-slate-200 text-right">
                                Rp {{ number_format($b->rental_subtotal, 0, ',', '.') }}
                            </td>
                            <td class="p-2 border-r border-slate-200 text-right font-bold text-slate-900">
                                Rp {{ number_format($b->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="p-2 border-r border-slate-200 text-right text-emerald-700 font-semibold">
                                Rp {{ number_format($b->down_payment_amount, 0, ',', '.') }}
                            </td>
                            <td class="p-2 border-r border-slate-200 text-right font-semibold {{ $b->remaining_amount > 0 && $b->status !== 'completed' && $b->payment_status !== 'paid_full' ? 'text-amber-800' : 'text-slate-400' }}">
                                {{ $b->remaining_amount > 0 && $b->status !== 'completed' && $b->payment_status !== 'paid_full' ? 'Rp ' . number_format($b->remaining_amount, 0, ',', '.') : 'Lunas' }}
                            </td>
                            <td class="p-2 text-center text-[10px] font-bold">
                                {{ $b->formatted_status }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-6 text-center text-slate-400">
                                Tidak ada data reservasi untuk periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($bookings->isNotEmpty())
                    <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                        <tr>
                            <td colspan="6" class="p-2 text-right uppercase text-slate-700">Total Keseluruhan:</td>
                            <td class="p-2 text-right">Rp {{ number_format($metrics['total_rental'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right text-emerald-800">Rp {{ number_format($metrics['total_gross'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right text-emerald-700">Rp {{ number_format($metrics['total_dp'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right text-amber-800">-</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <!-- Kolom Tanda Tangan -->
        <div class="grid grid-cols-2 gap-8 text-xs text-center mt-10">
            <div>
                <p class="text-slate-500 mb-16">Mengetahui,<br><strong>Penanggung Jawab Operasional</strong></p>
                <p class="font-extrabold text-slate-900 border-t border-slate-400 inline-block pt-1 px-8">
                    {{ Auth::user()->name }}
                </p>
            </div>
            <div>
                <p class="text-slate-500 mb-16">Yogyakarta, {{ \Carbon\Carbon::now()->format('d F Y') }}<br><strong>Pimpinan / Pemilik Rental</strong></p>
                <p class="font-extrabold text-slate-900 border-t border-slate-400 inline-block pt-1 px-8">
                    ( .................................................... )
                </p>
            </div>
        </div>
    </div>
</body>
</html>
