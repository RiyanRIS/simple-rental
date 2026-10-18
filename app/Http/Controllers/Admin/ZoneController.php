<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZoneController extends Controller
{
    public function index(): View
    {
        $zones = DeliveryZone::all();

        return view('admin.zones.index', compact('zones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:delivery_zones,name',
            'fee' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_free' => 'nullable|boolean',
        ]);

        $validated['is_free'] = (float) $validated['fee'] == 0 || $request->boolean('is_free');
        $validated['is_active'] = true;

        DeliveryZone::create($validated);

        return back()->with('success', "Zona antar-jemput {$validated['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $zone = DeliveryZone::findOrFail($id);

        $validated = $request->validate([
            'name' => "required|string|max:150|unique:delivery_zones,name,{$id}",
            'fee' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_free' => 'nullable|boolean',
            'is_active' => 'required|boolean',
        ]);

        $validated['is_free'] = (float) $validated['fee'] == 0 || $request->boolean('is_free');

        $zone->update($validated);

        return back()->with('success', "Zona {$zone->name} berhasil diperbarui.");
    }
}
