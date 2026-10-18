@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
    editModal: false,
    editUser: {
        id: '',
        name: '',
        email: '',
        phone: '',
        role: 'staff',
        is_active: true,
        is_self: false
    },
    deleteModal: false,
    deleteUser: { id: '', name: '' },
    openEdit(u) {
        this.editUser = Object.assign({}, u);
        this.editUser.is_active_val = u.is_active ? '1' : '0';
        this.editModal = true;
    },
    openDelete(id, name) {
        this.deleteUser = { id: id, name: name };
        this.deleteModal = true;
    }
}">
    <!-- Header Controls: Judul & Tombol Tambah -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white p-5 md:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-lg md:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                Kelola Pengguna & Staf
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Kelola akun administrator dan staf operasional lapangan (pengantaran, penjemputan, & inspeksi unit)</p>
        </div>
        <button type="button" @click="showAddModal = true" class="py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Tambah Pengguna Baru</span>
        </button>
    </div>

    <!-- Search & Filter Controls -->
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search Form -->
            <form action="{{ route('admin.users.index') }}" method="GET" class="relative flex-1 max-w-md">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                @if(request('status') !== null && request('status') !== '')
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau no HP pengguna..."
                    class="w-full pl-9 pr-8 py-2 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                @if(request('q'))
                    <a href="{{ route('admin.users.index', array_filter(['role' => request('role'), 'status' => request('status')])) }}" 
                       title="Hapus pencarian" 
                       class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>

            <div class="text-xs text-slate-500 shrink-0 font-medium">
                Total: <span class="font-bold text-slate-900">{{ $users->total() }}</span> Akun Terdaftar
            </div>
        </div>

        <!-- Filter Tabs (Role & Status) -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
            @php
                $currentQ = request('q');
                $buildUrl = fn($r, $s) => route('admin.users.index', array_filter(['role' => $r, 'status' => $s, 'q' => $currentQ]));
            @endphp

            <a href="{{ $buildUrl(null, null) }}"
                class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ !$role && $status === null ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Semua ({{ $counts['all'] }})
            </a>
            <a href="{{ $buildUrl('admin', null) }}"
                class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ $role === 'admin' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Admin ({{ $counts['admin'] }})
            </a>
            <a href="{{ $buildUrl('staff', null) }}"
                class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ $role === 'staff' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Staf Lapangan ({{ $counts['staff'] }})
            </a>
            <a href="{{ $buildUrl(null, 'active') }}"
                class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ $status === 'active' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Aktif ({{ $counts['active'] }})
            </a>
            <a href="{{ $buildUrl(null, '0') }}"
                class="px-3 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap {{ $status === '0' ? 'bg-slate-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                Nonaktif ({{ $counts['inactive'] }})
            </a>
        </div>
    </div>

    <!-- Alert Validasi Jika Ada Error dari Form -->
    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Terjadi kesalahan input pada formulir pengguna:
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-rose-700 pl-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- DESKTOP / TABLET: Table View -->
    <div class="hidden md:block bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <table class="w-full text-left text-xs divide-y divide-slate-100">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider font-bold">
                <tr>
                    <th class="py-3 px-4 w-[30%]">Pengguna</th>
                    <th class="py-3 px-4 w-[22%]">Kontak & WhatsApp</th>
                    <th class="py-3 px-4 w-[16%]">Role / Peran</th>
                    <th class="py-3 px-4 w-[14%]">Status Akun</th>
                    <th class="py-3 px-4 w-[18%] text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($users as $u)
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $u->phone ?? '');
                        $waUrl = str_starts_with($cleanPhone, '0') 
                            ? '62' . substr($cleanPhone, 1) 
                            : (str_starts_with($cleanPhone, '62') ? $cleanPhone : '62' . $cleanPhone);
                        $isSelf = $u->id === Auth::id();
                        $totalTasks = $u->delivery_bookings_count + $u->return_bookings_count;
                        $hasHistory = $totalTasks > 0 || $u->inspections_count > 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <!-- Col 1: Nama & Email -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl {{ $u->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' }} font-extrabold text-xs flex items-center justify-center shrink-0 border border-slate-200/60 shadow-xs">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-900 truncate" title="{{ $u->name }}">{{ $u->name }}</span>
                                        @if($isSelf)
                                            <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 text-[9px] font-extrabold border border-emerald-200">
                                                Anda
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-slate-400 text-[11px] block truncate" title="{{ $u->email }}">{{ $u->email }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Col 2: Kontak WA -->
                        <td class="py-3.5 px-4">
                            @if($u->phone)
                                <a href="https://wa.me/{{ $waUrl }}" target="_blank" 
                                   title="Kirim pesan WhatsApp ke {{ $u->name }}"
                                   class="inline-flex items-center gap-1.5 text-slate-700 hover:text-emerald-700 font-medium group">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                    </svg>
                                    <span class="group-hover:underline">{{ $u->phone }}</span>
                                </a>
                            @else
                                <span class="text-slate-400 italic text-[11px]">-</span>
                            @endif
                            @if($hasHistory)
                                <span class="text-[10px] text-slate-400 block mt-0.5">
                                    {{ $totalTasks }} tugas, {{ $u->inspections_count }} cek fisik
                                </span>
                            @endif
                        </td>

                        <!-- Col 3: Role -->
                        <td class="py-3.5 px-4">
                            @if($u->role === 'admin')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Admin Rental
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Staf Lapangan
                                </span>
                            @endif
                        </td>

                        <!-- Col 4: Status -->
                        <td class="py-3.5 px-4">
                            @if($u->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Nonaktif
                                </span>
                            @endif
                        </td>

                        <!-- Col 5: Aksi -->
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button type="button" 
                                    @click="openEdit({
                                        id: {{ $u->id }},
                                        name: '{{ addslashes($u->name) }}',
                                        email: '{{ addslashes($u->email) }}',
                                        phone: '{{ addslashes($u->phone ?? '') }}',
                                        role: '{{ $u->role }}',
                                        is_active: {{ $u->is_active ? 'true' : 'false' }},
                                        is_self: {{ $isSelf ? 'true' : 'false' }}
                                    })"
                                    title="Edit Pengguna"
                                    class="p-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-600 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                @if($isSelf)
                                    <button type="button" disabled title="Tidak dapat menghapus akun sendiri" class="p-1.5 rounded-lg border border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @elseif($hasHistory)
                                    <button type="button" disabled title="Tidak dapat dihapus karena memiliki riwayat tugas (silakan nonaktifkan akun)" class="p-1.5 rounded-lg border border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @else
                                    <button type="button" 
                                        @click="openDelete({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                        title="Hapus Pengguna"
                                        class="p-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 text-slate-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">
                            <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="font-medium text-slate-500">Tidak ada pengguna yang sesuai dengan filter atau pencarian.</p>
                            @if(request('q') || request('role') || request('status') !== null)
                                <a href="{{ route('admin.users.index') }}" class="text-xs text-emerald-600 hover:underline font-semibold mt-1 inline-block">Reset Filter & Pencarian</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- MOBILE: Card View (Touch-friendly) -->
    <div class="md:hidden space-y-3">
        @forelse($users as $u)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $u->phone ?? '');
                $waUrl = str_starts_with($cleanPhone, '0') 
                    ? '62' . substr($cleanPhone, 1) 
                    : (str_starts_with($cleanPhone, '62') ? $cleanPhone : '62' . $cleanPhone);
                $isSelf = $u->id === Auth::id();
                $totalTasks = $u->delivery_bookings_count + $u->return_bookings_count;
                $hasHistory = $totalTasks > 0 || $u->inspections_count > 0;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl {{ $u->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' }} font-extrabold text-xs flex items-center justify-center shrink-0 border border-slate-200">
                            {{ strtoupper(substr($u->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-slate-900 text-xs truncate">{{ $u->name }}</span>
                                @if($isSelf)
                                    <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 text-[9px] font-extrabold">Anda</span>
                                @endif
                            </div>
                            <span class="text-slate-400 text-[11px] block truncate">{{ $u->email }}</span>
                        </div>
                    </div>
                    @if($u->is_active)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                            Aktif
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-500 border border-slate-200 shrink-0">
                            Nonaktif
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs border-y border-slate-100 py-2.5">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Peran / Role</span>
                        <span class="font-bold {{ $u->role === 'admin' ? 'text-indigo-700' : 'text-blue-700' }}">
                            {{ $u->role === 'admin' ? 'Admin Rental' : 'Staf Lapangan' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">WhatsApp</span>
                        @if($u->phone)
                            <a href="https://wa.me/{{ $waUrl }}" target="_blank" class="text-emerald-700 font-semibold text-[11px] truncate block">
                                {{ $u->phone }}
                            </a>
                        @else
                            <span class="text-slate-400 text-[11px]">-</span>
                        @endif
                    </div>
                </div>

                @if($hasHistory)
                    <div class="text-[10px] text-slate-500">
                        Total Aktivitas: <span class="font-bold text-slate-700">{{ $totalTasks }} tugas lapangan</span>, <span class="font-bold text-slate-700">{{ $u->inspections_count }} cek fisik unit</span>
                    </div>
                @endif

                <div class="flex items-center gap-2 pt-1">
                    <button type="button" 
                        @click="openEdit({
                            id: {{ $u->id }},
                            name: '{{ addslashes($u->name) }}',
                            email: '{{ addslashes($u->email) }}',
                            phone: '{{ addslashes($u->phone ?? '') }}',
                            role: '{{ $u->role }}',
                            is_active: {{ $u->is_active ? 'true' : 'false' }},
                            is_self: {{ $isSelf ? 'true' : 'false' }}
                        })"
                        class="flex-1 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Pengguna</span>
                    </button>

                    @if(!$isSelf && !$hasHistory)
                        <button type="button" 
                            @click="openDelete({{ $u->id }}, '{{ addslashes($u->name) }}')"
                            class="py-2 px-3 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition-colors">
                            Hapus
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400">
                <p class="font-medium text-slate-500 text-xs">Tidak ada data pengguna.</p>
            </div>
        @endforelse

        @if($users->hasPages())
            <div class="pt-2">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: Tambah Pengguna Baru -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="showAddModal = false" class="bg-white rounded-2xl max-w-lg w-full shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
            <!-- Header (Static) -->
            <div class="flex items-center justify-between border-b border-slate-100 p-5 pb-4 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900">Tambah Pengguna Baru</h3>
                        <p class="text-[11px] text-slate-400">Buat akun untuk admin atau staf lapangan</p>
                    </div>
                </div>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1 leading-none text-lg">&times;</button>
            </div>

            <!-- Form with Scrollable Body & Static Footer -->
            <form action="{{ route('admin.users.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                <div class="p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso"
                            class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Email Login <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="budi@rentaljogja.com"
                                class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">No. WhatsApp / HP <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="081234567890"
                                class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Peran / Role <span class="text-rose-500">*</span></label>
                            <select name="role" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                                <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staf Lapangan</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin Rental</option>
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1">Staf lapangan memiliki akses khusus ke portal tugas penyerahan & inspeksi motor.</p>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Akun</label>
                            <div class="flex items-center gap-2 mt-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                    <span class="font-medium text-slate-700 text-xs">Aktifkan akun ini</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 border-t border-slate-100">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kata Sandi <span class="text-rose-500">*</span></label>
                            <input type="password" name="password" required placeholder="Minimal 6 karakter"
                                class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Konfirmasi Sandi <span class="text-rose-500">*</span></label>
                            <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi"
                                class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <!-- Footer (Static) -->
                <div class="flex items-center justify-end gap-2 p-4 px-5 border-t border-slate-100 bg-slate-50/50 shrink-0">
                    <button type="button" @click="showAddModal = false" class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-600/20">
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Pengguna -->
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="editModal = false" class="bg-white rounded-2xl max-w-lg w-full shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
            <!-- Header (Static) -->
            <div class="flex items-center justify-between border-b border-slate-100 p-5 pb-4 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900">Edit Data Pengguna</h3>
                        <p class="text-[11px] text-slate-400">Perbarui profil, peran, atau kata sandi</p>
                    </div>
                </div>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 p-1 leading-none text-lg">&times;</button>
            </div>

            <!-- Form with Scrollable Body & Static Footer -->
            <form :action="'{{ url('admin/users') }}/' + editUser.id" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                @method('PUT')

                <div class="p-5 space-y-3.5 text-xs overflow-y-auto flex-1">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editUser.name" required
                            class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Email Login <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" x-model="editUser.email" required
                                class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">No. WhatsApp / HP <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" x-model="editUser.phone" required
                                class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Peran / Role <span class="text-rose-500">*</span></label>
                            <select name="role" x-model="editUser.role" :class="editUser.is_self ? 'pointer-events-none bg-slate-100 text-slate-500' : 'bg-white'" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                                <option value="staff">Staf Lapangan</option>
                                <option value="admin">Admin Rental</option>
                            </select>
                            <p x-show="editUser.is_self" class="text-[10px] text-amber-600 mt-1">Anda tidak dapat menurunkan role akun Anda sendiri.</p>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Akun <span class="text-rose-500">*</span></label>
                            <select name="is_active" x-model="editUser.is_active_val" :class="editUser.is_self ? 'pointer-events-none bg-slate-100 text-slate-500' : 'bg-white'" required class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                                <option value="1">Aktif (Dapat Login)</option>
                                <option value="0">Nonaktif (Diblokir)</option>
                            </select>
                            <p x-show="editUser.is_self" class="text-[10px] text-amber-600 mt-1">Anda tidak dapat menonaktifkan akun sendiri.</p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 space-y-3">
                        <div>
                            <span class="font-bold text-slate-800 block text-xs">Ubah Kata Sandi (Opsional)</span>
                            <p class="text-[11px] text-slate-400">Kosongkan kolom sandi jika tidak ingin mengubah kata sandi pengguna.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Kata Sandi Baru</label>
                                <input type="password" name="password" placeholder="Kosongkan jika tetap"
                                    class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Ulangi Sandi Baru</label>
                                <input type="password" name="password_confirmation" placeholder="Ulangi sandi baru"
                                    class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer (Static) -->
                <div class="flex items-center justify-end gap-2 p-4 px-5 border-t border-slate-100 bg-slate-50/50 shrink-0">
                    <button type="button" @click="editModal = false" class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 px-5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md shadow-indigo-600/20">
                        Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Konfirmasi Hapus -->
    <div x-show="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs" style="display: none;">
        <div @click.away="deleteModal = false" class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl space-y-4">
            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="font-extrabold text-base text-slate-900">Hapus Pengguna?</h3>
                <p class="text-xs text-slate-500">
                    Apakah Anda yakin ingin menghapus akun <span class="font-bold text-slate-800" x-text="deleteUser.name"></span>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <form :action="'{{ url('admin/users') }}/' + deleteUser.id" method="POST" class="flex items-center gap-2 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModal = false" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
