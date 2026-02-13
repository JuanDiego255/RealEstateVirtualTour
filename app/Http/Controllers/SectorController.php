<?php

namespace App\Http\Controllers;

use App\Sector;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SectorController extends Controller
{
    /**
     * Display sectors for frontend (public)
     */
    public function index()
    {
        $sectors = Sector::where('status', true)
            ->with(['categories' => function ($q) {
                $q->where('status', true);
            }])
            ->get();

        // Fallback: load properties for backward compatibility when no sectors exist
        $properties = \App\Properties::all();

        return view('frontend.index', compact('sectors', 'properties'));
    }

    /**
     * Display a specific sector's categories (public)
     */
    public function show($slug)
    {
        $sector = Sector::where('slug', $slug)->where('status', true)->firstOrFail();
        $categories = Category::where('sector_id', $sector->id)
            ->where('status', true)
            ->withCount(['properties', 'vehicles'])
            ->get();

        return view('frontend.sector', compact('sector', 'categories'));
    }

    /**
     * Admin listing of all sectors
     */
    public function indexAdmin()
    {
        $sectors = Sector::withCount('categories')->get();
        return view('admin.sectors.sector', compact('sectors'));
    }

    /**
     * Store a newly created sector
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $campos = [
                'name' => 'required|string|max:100',
                'icon' => 'nullable|string|max:50',
                'description' => 'nullable|string',
            ];

            $mensaje = ["required" => 'El :attribute es requerido'];
            $this->validate($request, $campos, $mensaje);

            $sector = new Sector();
            if ($request->hasFile('image')) {
                $sector->image = $request->file('image')->store('uploads', 'public');
            }
            $sector->name = $request->name;
            $sector->icon = $request->icon;
            $sector->description = $request->description;
            $sector->status = true;
            $sector->save();

            DB::commit();
            return redirect('/sectors')->with('success', '¡Sector creado con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/sectors')->with('error', '¡No se pudo crear el sector!');
        }
    }

    /**
     * Update the specified sector
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $campos = [
                'name' => 'required|string|max:100',
                'icon' => 'nullable|string|max:50',
                'description' => 'nullable|string',
            ];

            $mensaje = ["required" => 'El :attribute es requerido'];
            $this->validate($request, $campos, $mensaje);

            $sector = Sector::findOrFail($id);
            if ($request->hasFile('image')) {
                if ($sector->image) {
                    Storage::delete('public/' . $sector->image);
                }
                $sector->image = $request->file('image')->store('uploads', 'public');
            }
            $sector->name = $request->name;
            $sector->icon = $request->icon;
            $sector->description = $request->description;
            $sector->update();

            DB::commit();
            return redirect('/sectors')->with('success', '¡Sector editado con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/sectors')->with('error', '¡No se pudo editar el sector!');
        }
    }

    /**
     * Remove the specified sector
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $sector = Sector::findOrFail($id);
            if ($sector->image) {
                Storage::delete('public/' . $sector->image);
            }
            $sector->delete();
            DB::commit();
            return redirect('/sectors')->with('success', '¡Sector eliminado con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/sectors')->with('error', '¡No se pudo eliminar el sector!');
        }
    }
}
