<?php

namespace App\Http\Controllers;

use App\Category;
use App\Subcategory;
use App\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display items within a category (public frontend)
     */
    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->where('status', true)
            ->with(['sector', 'subcategories' => function ($q) {
                $q->active()->ordered()->with(['properties']);
            }])
            ->firstOrFail();

        $sector = $category->sector;

        return view('frontend.category', compact('category', 'sector'));
    }

    /**
     * Display properties/vehicles within a subcategory (public frontend)
     */
    public function showSubcategory($categorySlug, $subcategorySlug)
    {
        $category = Category::where('slug', $categorySlug)
            ->where('status', true)
            ->with('sector')
            ->firstOrFail();

        $subcategory = Subcategory::where('category_id', $category->id)
            ->where('slug', $subcategorySlug)
            ->where('is_active', true)
            ->with(['properties.scenes'])
            ->firstOrFail();

        $sector = $category->sector;

        return view('frontend.subcategory', compact('category', 'subcategory', 'sector'));
    }

    /**
     * Admin listing of all categories
     */
    public function indexAdmin(Request $request)
    {
        $sectors = Sector::all();
        $query = Category::with(['sector', 'subcategories']);

        if ($request->has('sector_id') && $request->sector_id != '') {
            $query->where('sector_id', $request->sector_id);
        }

        $categories = $query->get();
        return view('admin.categories.category', compact('categories', 'sectors'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $campos = [
                'name' => 'required|string|max:150',
                'sector_id' => 'required|exists:sectors,id',
                'location' => 'nullable|string|max:255',
                'facilities' => 'nullable|string',
                'notes' => 'nullable|string',
                'features' => 'nullable|string',
                'description' => 'nullable|string',
            ];

            $mensaje = ["required" => 'El :attribute es requerido'];
            $this->validate($request, $campos, $mensaje);

            $category = new Category();
            if ($request->hasFile('image')) {
                $category->image = $request->file('image')->store('uploads', 'public');
            }
            $category->sector_id = $request->sector_id;
            $category->name = $request->name;
            $category->location = $request->location;
            $category->facilities = $request->facilities;
            $category->notes = $request->notes;
            $category->features = $request->features;
            $category->description = $request->description;
            $category->status = true;
            $category->save();

            DB::commit();
            return redirect('/categories')->with('success', '¡Categoría creada con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/categories')->with('error', '¡No se pudo crear la categoría! ' . $th->getMessage());
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $campos = [
                'name' => 'required|string|max:150',
                'sector_id' => 'required|exists:sectors,id',
                'location' => 'nullable|string|max:255',
                'facilities' => 'nullable|string',
                'notes' => 'nullable|string',
                'features' => 'nullable|string',
                'description' => 'nullable|string',
            ];

            $mensaje = ["required" => 'El :attribute es requerido'];
            $this->validate($request, $campos, $mensaje);

            $category = Category::findOrFail($id);
            if ($request->hasFile('image')) {
                if ($category->image) {
                    Storage::delete('public/' . $category->image);
                }
                $category->image = $request->file('image')->store('uploads', 'public');
            }
            $category->sector_id = $request->sector_id;
            $category->name = $request->name;
            $category->location = $request->location;
            $category->facilities = $request->facilities;
            $category->notes = $request->notes;
            $category->features = $request->features;
            $category->description = $request->description;
            $category->update();

            DB::commit();
            return redirect('/categories')->with('success', '¡Categoría editada con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/categories')->with('error', '¡No se pudo editar la categoría!');
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $category = Category::findOrFail($id);
            if ($category->image) {
                Storage::delete('public/' . $category->image);
            }
            $category->delete();
            DB::commit();
            return redirect('/categories')->with('success', '¡Categoría eliminada con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/categories')->with('error', '¡No se pudo eliminar la categoría!');
        }
    }
}
