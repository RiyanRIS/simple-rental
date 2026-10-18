<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna & staf dengan filter role dan status.
     */
    public function index(Request $request): View
    {
        $role = $request->input('role');
        $status = $request->input('status');
        $search = $request->input('q');

        $query = User::query()
            ->withCount(['deliveryBookings', 'returnBookings', 'inspections']);

        if ($role && in_array($role, ['admin', 'staff'])) {
            $query->where('role', $role);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active' || $status === '1');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(12)->withQueryString();

        $counts = [
            'all' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'role', 'status', 'counts'));
    }

    /**
     * Simpan pengguna / staf baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:25'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar oleh pengguna lain.',
            'phone.required' => 'Nomor telepon / WhatsApp wajib diisi.',
            'role.required' => 'Pilih peran / role pengguna.',
            'role.in' => 'Role yang dipilih harus Admin atau Staf.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        return back()->with('success', "Pengguna {$user->name} ({$user->role}) berhasil ditambahkan.");
    }

    /**
     * Perbarui data pengguna yang sudah ada.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUserId = Auth::id();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:25'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar oleh pengguna lain.',
            'phone.required' => 'Nomor telepon / WhatsApp wajib diisi.',
            'role.required' => 'Pilih peran / role pengguna.',
            'role.in' => 'Role yang dipilih harus Admin atau Staf.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // Proteksi: Akun sendiri tidak boleh dinonaktifkan atau diturunkan rolenya
        if ($user->id === $currentUserId) {
            if (! $validated['is_active']) {
                return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang aktif.');
            }
            if ($validated['role'] !== 'admin') {
                return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri menjadi non-admin.');
            }
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus pengguna dari sistem dengan proteksi keamanan integritas data.
     */
    public function destroy(User $user): RedirectResponse
    {
        $currentUserId = Auth::id();

        if ($user->id === $currentUserId) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cek riwayat penugasan atau inspeksi
        $hasHistory = $user->deliveryBookings()->exists()
            || $user->returnBookings()->exists()
            || $user->inspections()->exists();

        if ($hasHistory) {
            return back()->with('error', "Pengguna {$user->name} tidak dapat dihapus karena memiliki riwayat tugas pengantaran, penjemputan, atau inspeksi unit. Anda dapat menonaktifkan akunnya (Nonaktif) agar tidak bisa login kembali.");
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('success', "Pengguna {$userName} berhasil dihapus dari sistem.");
    }
}
