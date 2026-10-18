<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sewa Motor Jogja - Rental Motor Mudah & Cepat di Yogyakarta' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Compiled CSS & Tailwind -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white/90 backdrop-blur-md border-b border-emerald-100/60 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white shadow-md shadow-emerald-600/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-emerald-700 to-teal-800 bg-clip-text text-transparent">JogjaMotor</span>
                        <span class="block text-[10px] font-semibold tracking-wider uppercase text-slate-400 -mt-1">Rental Motor Praktis</span>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <a href="{{ route('booking.track') }}" class="text-xs sm:text-sm font-semibold text-slate-600 hover:text-emerald-700 px-3 py-2 rounded-lg transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Cek Pesanan</span>
                    </a>

                    {{-- Tombol Hubungi WhatsApp Admin (Desain identik dengan tombol sebelumnya) --}}
                    <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin JogjaMotor, saya ingin menanyakan informasi seputar sewa motor.') }}" 
                       target="_blank" 
                       class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-2 rounded-lg border border-emerald-200 hover:bg-emerald-100 transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z" />
                        </svg>
                        <span>Hubungi WA Admin</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-4xl mx-auto px-4 mt-4 w-full">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-4xl mx-auto px-4 mt-4 w-full">
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm shadow-sm">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Content -->
    <main class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 mt-16 py-8 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            <p class="font-semibold text-slate-700">JogjaMotor - Layanan Rental Motor Wisata Yogyakarta</p>
            <p class="mt-1 text-slate-400">Gratis Antar-Jemput Stasiun Tugu & Lempuyangan • Unit Prima & Helm Bersih • Cek Ketersediaan Real-Time</p>
            <div class="mt-3 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-slate-400">
                <span>&copy; {{ date('Y') }} JogjaMotor. Semua Hak Dilindungi.</span>
                <span>•</span>
                <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin JogjaMotor, saya ingin menanyakan informasi seputar sewa motor.') }}" target="_blank" class="hover:text-emerald-700 font-semibold transition-colors">WhatsApp CS Admin</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
