<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Motorcycle;
use App\Models\MotorcycleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MotorcycleController extends Controller
{
    public function index(): View
    {
        $motorcycles = Motorcycle::with('category')->latest()->get();
        $categories = MotorcycleCategory::where('is_active', true)->get();

        return view('admin.motorcycles.index', compact('motorcycles', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:motorcycle_categories,id',
            'plate_number' => 'required|string|unique:motorcycles,plate_number|max:20',
            'color' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:2015|max:2030',
            'default_helmets_count' => 'nullable|integer|min:0|max:2',
            'default_raincoats_count' => 'nullable|integer|min:0|max:4',
            'default_key_and_stnk' => 'nullable|boolean',
            'status' => 'required|in:available,rented,maintenance',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['default_helmets_count'] = $validated['default_helmets_count'] ?? 2;
        $validated['default_raincoats_count'] = $validated['default_raincoats_count'] ?? 1;
        $validated['default_key_and_stnk'] = $request->has('default_key_and_stnk') ? (bool) $request->input('default_key_and_stnk') : true;

        Motorcycle::create($validated);

        return back()->with('success', "Unit motor baru {$validated['plate_number']} berhasil ditambahkan.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $motorcycle = Motorcycle::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:motorcycle_categories,id',
            'plate_number' => "required|string|unique:motorcycles,plate_number,{$id}|max:20",
            'color' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:2015|max:2030',
            'default_helmets_count' => 'nullable|integer|min:0|max:2',
            'default_raincoats_count' => 'nullable|integer|min:0|max:4',
            'default_key_and_stnk' => 'nullable|boolean',
            'status' => 'required|in:available,rented,maintenance',
            'notes' => 'nullable|string|max:500',
        ]);

        if (! array_key_exists('default_helmets_count', $validated) || $validated['default_helmets_count'] === null) {
            unset($validated['default_helmets_count']);
        }
        if (! array_key_exists('default_raincoats_count', $validated) || $validated['default_raincoats_count'] === null) {
            unset($validated['default_raincoats_count']);
        }
        if (! array_key_exists('default_key_and_stnk', $validated) || $validated['default_key_and_stnk'] === null) {
            unset($validated['default_key_and_stnk']);
        }

        $motorcycle->update($validated);

        return back()->with('success', "Data motor {$motorcycle->plate_number} berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $motorcycle = Motorcycle::findOrFail($id);
        $plate = $motorcycle->plate_number;

        // Keamanan: Cek apakah motor sedang berstatus disewa atau sedang dalam penugasan aktif
        if ($motorcycle->status === 'rented') {
            return back()->with('error', "Motor {$plate} tidak dapat dihapus karena saat ini sedang disewa pelanggan.");
        }

        $activeBooking = $motorcycle->bookings()
            ->whereIn('status', ['approved', 'delivering', 'active', 'returning'])
            ->exists();

        if ($activeBooking) {
            return back()->with('error', "Motor {$plate} tidak dapat dihapus karena masih terikat pada jadwal sewa yang sedang aktif.");
        }

        $motorcycle->delete();

        return back()->with('success', "Unit motor {$plate} telah berhasil dihapus dari sistem.");
    }
}
