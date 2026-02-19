<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Category;
use App\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubcategoryController extends Controller
{
    /**
     * Display subcategories for a given category (sucursal).
     */
    public function index(Category $category)
    {
        $this->authorizeCategory($category);

        $subcategories = $category->subcategories()
            ->ordered()
            ->paginate(20);

        return view('admin.subcategories.index', compact('category', 'subcategories'));
    }

    /**
     * Show form to create a new subcategory.
     */
    public function create(Category $category)
    {
        $this->authorizeCategory($category);

        // Check limit
        if (!$this->canCreateSubcategory($category)) {
            return redirect()->route('admin.subcategories.index', $category)
                ->with('error', 'Ha alcanzado el límite de categorías por sucursal de su plan. Actualice para crear más.');
        }

        return view('admin.subcategories.create', compact('category'));
    }

    /**
     * Store a new subcategory.
     */
    public function store(Request $request, Category $category)
    {
        $this->authorizeCategory($category);

        if (!$this->canCreateSubcategory($category)) {
            return redirect()->route('admin.subcategories.index', $category)
                ->with('error', 'Ha alcanzado el límite de categorías por sucursal de su plan.');
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:10000',
            'is_active' => 'nullable',
        ]);

        $data = $request->only(['name', 'description']);
        $data['category_id'] = $category->id;
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('subcategories', 'public');
        }

        Subcategory::create($data);

        return redirect()->route('admin.subcategories.index', $category)
            ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Show form to edit a subcategory.
     */
    public function edit(Category $category, Subcategory $subcategory)
    {
        $this->authorizeCategory($category);
        $this->ensureBelongs($subcategory, $category);

        return view('admin.subcategories.edit', compact('category', 'subcategory'));
    }

    /**
     * Update a subcategory.
     */
    public function update(Request $request, Category $category, Subcategory $subcategory)
    {
        $this->authorizeCategory($category);
        $this->ensureBelongs($subcategory, $category);

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:10000',
            'is_active' => 'nullable',
        ]);

        $data = $request->only(['name', 'description']);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($subcategory->image) {
                \Storage::disk('public')->delete($subcategory->image);
            }
            $data['image'] = $request->file('image')->store('subcategories', 'public');
        }

        $subcategory->update($data);

        return redirect()->route('admin.subcategories.index', $category)
            ->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Delete a subcategory.
     */
    public function destroy(Category $category, Subcategory $subcategory)
    {
        $this->authorizeCategory($category);
        $this->ensureBelongs($subcategory, $category);

        if ($subcategory->image) {
            \Storage::disk('public')->delete($subcategory->image);
        }

        $subcategory->delete();

        return redirect()->route('admin.subcategories.index', $category)
            ->with('success', 'Categoría eliminada correctamente.');
    }

    /**
     * Toggle subcategory active status.
     */
    public function toggleStatus(Category $category, Subcategory $subcategory)
    {
        $this->authorizeCategory($category);
        $this->ensureBelongs($subcategory, $category);

        $subcategory->update(['is_active' => !$subcategory->is_active]);

        return redirect()->route('admin.subcategories.index', $category)
            ->with('success', 'Estado de la categoría actualizado.');
    }

    /**
     * Display publicaciones (propiedades + vehículos unificados) for a subcategory.
     */
    public function inmuebles(Category $category, Subcategory $subcategory)
    {
        $this->authorizeCategory($category);
        $this->ensureBelongs($subcategory, $category);

        $properties = $subcategory->properties()->get();

        return view('admin.subcategories.inmuebles', compact('category', 'subcategory', 'properties'));
    }

    /**
     * Check if user can access this category (sucursal).
     */
    private function authorizeCategory(Category $category)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $category->company_id !== $user->company_id) {
            abort(403, 'No tiene permisos para acceder a esta sucursal.');
        }
    }

    /**
     * Check subcategory belongs to the category.
     */
    private function ensureBelongs(Subcategory $subcategory, Category $category)
    {
        if ($subcategory->category_id !== $category->id) {
            abort(404);
        }
    }

    /**
     * Check if more subcategories can be created for this category.
     */
    private function canCreateSubcategory(Category $category): bool
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$category->company) {
            return true;
        }

        $package = $category->company->getCurrentPackage();
        if (!$package) {
            return false;
        }

        $currentCount = $category->subcategories()->count();
        return $currentCount < $package->max_subcategories_per_category;
    }
}
