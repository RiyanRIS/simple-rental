<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Portal Tugas Petugas - JogjaMotor' }}</title>
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
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen flex flex-col items-center">
    <!-- Mobile App Container Wrapper -->
    <div class="w-full max-w-md bg-white min-h-screen shadow-xl flex flex-col">
        <!-- Header Petugas -->
        <header class="bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 text-white p-4 sticky top-0 z-50 shadow-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/30 border border-emerald-400/40 flex items-center justify-center font-bold text-sm text-emerald-200">
                        🛵
                    </div>
                    <div>
                        <h1 class="font-extrabold text-sm tracking-tight text-white leading-tight">Portal Lapangan</h1>
                        <p class="text-[10px] text-emerald-300">{{ Auth::user()->name }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-[10px] font-bold bg-slate-800/80 px-2 py-1 rounded-md text-slate-300">
                            Admin
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[11px] font-bold bg-rose-900/40 text-rose-200 px-2.5 py-1 rounded-md border border-rose-500/30">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Flash Message Alerts -->
        @if(session('success'))
            <div class="p-3 mx-4 mt-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 mx-4 mt-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 mx-4 mt-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs shadow-sm space-y-1">
                <p class="font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mohon periksa isian form:
                </p>
                <ul class="list-disc list-inside text-[11px] pl-1 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Body -->
        <main class="flex-1 p-4 pb-20 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
