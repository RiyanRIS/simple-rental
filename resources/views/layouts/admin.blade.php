<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard - Rental Motor Jogja' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Compiled CSS & Tailwind -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body x-data="{ mobileSidebarOpen: false }" class="bg-slate-100 text-slate-800 antialiased min-h-screen md:h-screen md:overflow-hidden flex flex-col md:flex-row">
    <!-- Sidebar -->
    <aside class="w-full md:w-64 md:h-screen md:shrink-0 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 z-30">
        <!-- Brand & Mobile Toggle -->
        <div class="h-16 shrink-0 flex items-center justify-between px-6 border-b border-slate-800 bg-slate-950/40">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white font-black text-sm shadow-sm">
                    JM
                </div>
                <div>
                    <span class="font-extrabold text-white text-base tracking-tight">JogjaMotor</span>
                    <span class="block text-[10px] text-emerald-400 font-semibold uppercase -mt-0.5">Admin Workspace</span>
                </div>
            </div>

            <!-- Mobile Hamburger Toggle (Hanya di layar HP) -->
            <button type="button" @click="mobileSidebarOpen = !mobileSidebarOpen" class="md:hidden p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors" aria-label="Toggle Menu">
                <svg x-show="!mobileSidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileSidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Content: Collapsible on Mobile, Always Visible on Desktop -->
        <div :class="mobileSidebarOpen ? 'flex' : 'hidden md:flex'" class="flex-1 flex-col min-h-0">
            <!-- Navigation Menu -->
            <nav class="p-4 space-y-1 flex-1 overflow-y-auto text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.bookings.*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Semua Reservasi
                </a>

                <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.reports.*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Laporan & Rekapitulasi
                </a>

                <div class="pt-4 pb-1 text-[10px] uppercase font-bold text-slate-500 px-3 tracking-wider">Master Data</div>

                <a href="{{ route('admin.motorcycles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.motorcycles.*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Unit Fisik Motor
                </a>

                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Tipe & Tarif Sewa
                </a>

                <a href="{{ route('admin.seasonal-prices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.seasonal-prices.*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Tarif Musim Libur / Peak
                </a>

                <a href="{{ route('admin.zones.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.zones.*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    Zona Antar & Ongkir
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Kelola Pengguna
                </a>

                <div class="pt-4 pb-1 text-[10px] uppercase font-bold text-slate-500 px-3 tracking-wider">Akses Lapangan</div>

                <a href="{{ route('staff.tasks') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 text-emerald-400 hover:text-emerald-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Buka Portal Petugas
                </a>

                <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Lihat Web Publik
                </a>
            </nav>

            <!-- User Info & Logout (Permanen terkunci di bawah sidebar) -->
            <div class="p-4 shrink-0 border-t border-slate-800 bg-slate-950/50 flex items-center justify-between text-xs mt-auto">
                <div class="min-w-0 pr-2">
                    <p class="font-bold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[11px] text-slate-400 capitalize">{{ Auth::user()->role }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" title="Keluar / Logout" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-900/40 text-slate-400 hover:text-rose-400 transition-colors inline-flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 md:h-screen md:overflow-y-auto">
        <!-- Topbar -->
        <header class="h-16 shrink-0 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-20">
            <h2 class="font-extrabold text-base text-slate-800">{{ $headerTitle ?? 'Admin Panel' }}</h2>
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <div class="hidden sm:block">
                    Waktu Server: <span class="font-semibold text-slate-700">{{ now()->format('d M Y, H:i') }} WIB</span>
                </div>
                <div class="flex items-center gap-2 pl-3 sm:border-l sm:border-slate-200">
                    <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <span class="font-bold text-slate-700 hidden md:inline">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </header>

        <!-- Flash Message Alerts -->
        @if(session('success'))
            <div class="mx-6 mt-4">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 text-xs shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-6 mt-4">
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-center gap-3 text-xs shadow-sm">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Page View Body -->
        <main class="p-6 flex-1">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
