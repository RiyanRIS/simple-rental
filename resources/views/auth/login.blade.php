@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 py-12" x-data="{ email: '', password: '' }">
    <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white mx-auto shadow-lg shadow-emerald-600/20 mb-3">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Portal Tim Rental</h1>
        <p class="text-xs text-slate-500 mt-1">Masuk untuk Admin Pengelola atau Petugas Lapangan</p>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200">
        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                <input type="email" name="email" x-model="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-sm focus:ring-2 focus:ring-emerald-500">
                @error('email')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" x-model="password" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 text-sm focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <span class="text-slate-600">Ingat Saya</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 transition-all">
                Masuk ke Sistem
            </button>
        </form>

        <!-- Quick Demo Login Shortcut -->
        <div class="mt-6 pt-5 border-t border-slate-100">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">Akses Cepat Demo:</p>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" @click="email = 'admin@rentaljogja.com'; password = 'password'"
                    class="p-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 text-center transition-colors">
                    Akun Admin
                </button>
                <button type="button" @click="email = 'budi@rentaljogja.com'; password = 'password'"
                    class="p-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold border border-slate-200 text-center transition-colors">
                    Petugas Lapangan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
