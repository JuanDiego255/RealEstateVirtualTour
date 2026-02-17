<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SectorController extends Controller
{
    /**
     * Display a listing of sectors.
     */
    public function index()
    {
        $sectors = Sector::ordered()->withCount('categories')->get();
        return view('admin.sectors.index', compact('sectors'));
    }

    /**
     * Show the form for creating a new sector.
     */
    public function create()
    {
        return view('admin.sectors.create');
    }

    /**
     * Store a newly created sector.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:2048',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'description', 'icon', 'color', 'sort_order']);
        $data['slug'] = Sector::generateUniqueSlug($request->name);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('sectors', 'public');
        }

        Sector::create($data);

        return redirect()->route('admin.sectors.index')
            ->with('success', 'Sector creado correctamente.');
    }

    /**
     * Show the form for editing the specified sector.
     */
    public function edit(Sector $sector)
    {
        return view('admin.sectors.edit', compact('sector'));
    }

    /**
     * Update the specified sector.
     */
    public function update(Request $request, Sector $sector)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|image|max:2048',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'description', 'icon', 'color', 'sort_order']);
        $data['is_active'] = $request->has('is_active');

        if ($request->name !== $sector->name) {
            $data['slug'] = Sector::generateUniqueSlug($request->name, $sector->id);
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($sector->image) {
                \Storage::disk('public')->delete($sector->image);
            }
            $data['image'] = $request->file('image')->store('sectors', 'public');
        }

        $sector->update($data);

        return redirect()->route('admin.sectors.index')
            ->with('success', 'Sector actualizado correctamente.');
    }

    /**
     * Remove the specified sector.
     */
    public function destroy(Sector $sector)
    {
        // Check if sector has categories
        if ($sector->categories()->count() > 0) {
            return redirect()->route('admin.sectors.index')
                ->with('error', 'No se puede eliminar el sector porque tiene categorías asociadas.');
        }

        // Delete image if exists
        if ($sector->image) {
            \Storage::disk('public')->delete($sector->image);
        }

        $sector->delete();

        return redirect()->route('admin.sectors.index')
            ->with('success', 'Sector eliminado correctamente.');
    }

    /**
     * Toggle sector active status.
     */
    public function toggleStatus(Sector $sector)
    {
        $sector->update(['is_active' => !$sector->is_active]);

        return redirect()->route('admin.sectors.index')
            ->with('success', 'Estado del sector actualizado.');
    }
}
