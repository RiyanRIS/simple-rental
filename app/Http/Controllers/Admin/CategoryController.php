<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MotorcycleCategory;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = MotorcycleCategory::withCount('motorcycles')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:motorcycle_categories,name',
            'brand' => 'nullable|string|max:50',
            'cc_capacity' => 'required|integer|min:50|max:1000',
            'daily_price' => 'required|numeric|min:10000',
            'weekend_price' => 'nullable|numeric|min:10000',
            'hourly_overtime_price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if (empty($validated['brand'])) {
            $parts = explode(' ', trim($validated['name']));
            $validated['brand'] = $parts[0] ?? 'Honda';
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = true;

        if ($request->hasFile('image')) {
            $processed = ImageOptimizer::processCategoryImages($request->file('image'), 'categories');
            $validated['image'] = $processed['large'];
            $validated['image_thumbnail'] = $processed['thumbnail'];
        }

        MotorcycleCategory::create($validated);

        return back()->with('success', "Kategori motor {$validated['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $category = MotorcycleCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => "required|string|max:100|unique:motorcycle_categories,name,{$id}",
            'brand' => 'nullable|string|max:50',
            'cc_capacity' => 'required|integer|min:50|max:1000',
            'daily_price' => 'required|numeric|min:10000',
            'weekend_price' => 'nullable|numeric|min:10000',
            'hourly_overtime_price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if (empty($validated['brand'])) {
            $parts = explode(' ', trim($validated['name']));
            $validated['brand'] = $parts[0] ?? $category->brand ?? 'Honda';
        }

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $processed = ImageOptimizer::processCategoryImages($request->file('image'), 'categories');
            $validated['image'] = $processed['large'];
            $validated['image_thumbnail'] = $processed['thumbnail'];
        }

        $category->update($validated);

        return back()->with('success', "Kategori motor {$category->name} berhasil diperbarui.");
    }
}
