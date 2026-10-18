<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MotorcycleCategory;
use App\Models\SeasonalPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeasonalPriceController extends Controller
{
    public function index(): View
    {
        $seasonalPrices = SeasonalPrice::with('category')->latest()->get();
        $categories = MotorcycleCategory::where('is_active', true)->get();

        return view('admin.seasonal_prices.index', compact('seasonalPrices', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:motorcycle_categories,id',
            'price_type' => 'required|in:surcharge,fixed',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        SeasonalPrice::create($validated);

        return redirect()->route('admin.seasonal-prices.index')
            ->with('success', "Tarif musim khusus '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $seasonalPrice = SeasonalPrice::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:motorcycle_categories,id',
            'price_type' => 'required|in:surcharge,fixed',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ]);

        $seasonalPrice->update($validated);

        return redirect()->route('admin.seasonal-prices.index')
            ->with('success', "Tarif musim khusus '{$seasonalPrice->name}' berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $seasonalPrice = SeasonalPrice::findOrFail($id);
        $name = $seasonalPrice->name;
        $seasonalPrice->delete();

        return redirect()->route('admin.seasonal-prices.index')
            ->with('success', "Tarif musim khusus '{$name}' telah dihapus.");
    }
}
