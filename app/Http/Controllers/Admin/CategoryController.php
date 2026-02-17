<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Category;
use App\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for the user's company.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            // Super admin can see all categories
            $categories = Category::with(['company', 'sector'])
                ->withCount('properties')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // Regular users only see their company's categories
            $categories = Category::with(['sector'])
                ->where('company_id', $user->company_id)
                ->withCount('properties')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $user = Auth::user();
        $company = $user->company;

        // Check if can create more categories
        if (!$user->isSuperAdmin() && !$company->canCreateCategory()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Ha alcanzado el límite de categorías de su plan. Actualice para crear más.');
        }

        $sectors = Sector::active()->ordered()->get();

        return view('admin.categories.create', compact('sectors'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Re-check limit
        if (!$user->isSuperAdmin() && !$company->canCreateCategory()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Ha alcanzado el límite de categorías de su plan.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'sector_id' => 'required|exists:sectors,id',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:5120',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_whatsapp' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'social_facebook' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'name', 'sector_id', 'description', 'contact_name', 'contact_email',
            'contact_phone', 'contact_whatsapp', 'address', 'website',
            'social_facebook', 'social_instagram'
        ]);

        $data['company_id'] = $company->id;
        $data['slug'] = Category::generateUniqueSlug($request->name, $company->id);
        $data['share_token'] = Str::random(32);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('categories/logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('categories/covers', 'public');
        }

        // Handle coordinates
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $this->authorizeCategory($category);

        $category->load(['sector', 'company', 'properties' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        $this->authorizeCategory($category);

        $sectors = Sector::active()->ordered()->get();

        return view('admin.categories.edit', compact('category', 'sectors'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $this->authorizeCategory($category);

        $request->validate([
            'name' => 'required|string|max:255',
            'sector_id' => 'required|exists:sectors,id',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:5120',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_whatsapp' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'social_facebook' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'name', 'sector_id', 'description', 'contact_name', 'contact_email',
            'contact_phone', 'contact_whatsapp', 'address', 'website',
            'social_facebook', 'social_instagram'
        ]);

        $data['is_active'] = $request->has('is_active');

        // Update slug if name changed
        if ($request->name !== $category->name) {
            $data['slug'] = Category::generateUniqueSlug($request->name, $category->company_id, $category->id);
        }

        // Handle images
        if ($request->hasFile('logo')) {
            if ($category->logo) {
                \Storage::disk('public')->delete($category->logo);
            }
            $data['logo'] = $request->file('logo')->store('categories/logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($category->cover_image) {
                \Storage::disk('public')->delete($category->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('categories/covers', 'public');
        }

        // Handle coordinates
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        $this->authorizeCategory($category);

        // Check if category has properties
        if ($category->properties()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'No se puede eliminar la categoría porque tiene propiedades asociadas.');
        }

        // Delete images
        if ($category->logo) {
            \Storage::disk('public')->delete($category->logo);
        }
        if ($category->cover_image) {
            \Storage::disk('public')->delete($category->cover_image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }

    /**
     * Toggle category active status.
     */
    public function toggleStatus(Category $category)
    {
        $this->authorizeCategory($category);

        $category->update(['is_active' => !$category->is_active]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Estado de la categoría actualizado.');
    }

    /**
     * Regenerate share token.
     */
    public function regenerateToken(Category $category)
    {
        $this->authorizeCategory($category);

        $category->update(['share_token' => Str::random(32)]);

        return redirect()->back()
            ->with('success', 'Enlace compartible regenerado.');
    }

    /**
     * Check if user is authorized to access this category.
     */
    private function authorizeCategory(Category $category)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && $category->company_id !== $user->company_id) {
            abort(403, 'No tiene permisos para acceder a esta categoría.');
        }
    }
}
