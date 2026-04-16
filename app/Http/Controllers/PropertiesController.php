<?php

namespace App\Http\Controllers;

use App\Properties;
use App\PropertyImage;
use App\Category;
use App\Subcategory;
use App\Sector;
use App\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $properties = Properties::get();
        return view('frontend.index', compact('properties'));
    }
    /**
     * Display a listing of the resource (admin).
     * Shows all publications (properties + vehicles) unified.
     */
    public function indexAdmin()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user->isSuperAdmin()) {
            // Super admin ve todas las publicaciones
            $properties = Properties::with(['subcategory.category.sector', 'category', 'images'])->get();
            $subcategories = Subcategory::with('category.sector')->where('is_active', true)->orderBy('name')->get();
        } else {
            // company_admin y agents ven solo las publicaciones de su empresa
            $categoriesOfCompany = \App\Category::where('company_id', $user->company_id)->pluck('id');
            $subcategoryIds = Subcategory::whereIn('category_id', $categoriesOfCompany)->pluck('id');

            $properties = Properties::with(['subcategory.category.sector', 'category', 'images'])
                ->where(function ($q) use ($categoriesOfCompany, $subcategoryIds) {
                    $q->whereIn('category_id', $categoriesOfCompany)
                      ->orWhereIn('subcategory_id', $subcategoryIds);
                })
                ->get();

            $subcategories = Subcategory::with('category.sector')
                ->whereIn('category_id', $categoriesOfCompany)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        $hotspotCounts = DB::table('hotspots')
            ->join('scenes', 'hotspots.sourceScene', '=', 'scenes.id')
            ->selectRaw('COALESCE(scenes.property_id, scenes.vehicle_id) as prop_id, COUNT(*) as total')
            ->groupByRaw('COALESCE(scenes.property_id, scenes.vehicle_id)')
            ->pluck('total', 'prop_id');

        return view('admin.properties.property', compact('properties', 'subcategories', 'hotspotCounts'));
    }

    /**
     * Store a newly created resource in storage.
     * Handles both real estate and vehicle types dynamically.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->canAccessModule('properties', 'create'), 403, 'Sin permiso para crear publicaciones.');
        DB::beginTransaction();
        try {
            $isVehicle = ($request->property_type === 'vehicle');

            // Validación dinámica según tipo
            if ($isVehicle) {
                $campos = [
                    'name' => 'required|string|max:150',
                    'subcategory_id' => 'required|exists:subcategories,id',
                    'brand' => 'required|string|max:80',
                    'model' => 'required|string|max:80',
                    'year' => 'required|string|max:10',
                    'price' => 'required|string|max:100',
                ];
            } else {
                $campos = [
                    'name' => 'required|string|max:100',
                    'code' => 'required|string|max:100',
                    'rooms' => 'required|string|max:100',
                    'bathrooms' => 'required|string|max:100',
                    'garage' => 'required|string|max:100',
                    'floor_levels' => 'required|string|max:100',
                    'construction' => 'required|string|max:100',
                    'land' => 'required|string|max:100',
                    'construction_year' => 'required|string|max:100',
                    'maintenance' => 'required|string|max:100',
                    'price' => 'required|string|max:100',
                ];
            }

            $mensaje = ["required" => 'El :attribute es requerido'];
            $this->validate($request, $campos, $mensaje);

            $property = new Properties();
            if ($request->hasFile('image')) {
                $property->image = $request->file('image')->store('uploads', 'public');
            }

            $property->name = $request->name;
            $property->property_type = $request->property_type ?? 'house';
            $property->subcategory_id = $request->subcategory_id;

            // Derivar category_id de la subcategoría
            if ($request->subcategory_id) {
                $sub = Subcategory::find($request->subcategory_id);
                $property->category_id = $sub ? $sub->category_id : null;
            }

            $property->description = $request->description;
            $property->price = $request->price;
            $property->currency = $request->currency ?? 'CRC';
            $property->status = $request->status ?? 'available';
            $property->user_id = auth()->id();

            if ($isVehicle) {
                // Campos específicos de vehículo
                $property->code = $request->code ?? '';
                $property->brand = $request->brand;
                $property->model = $request->model;
                $property->year = $request->year;
                $property->color = $request->color;
                $property->mileage_km = $request->mileage_km;
                $property->fuel_tank_capacity = $request->fuel_tank_capacity;
                $property->fuel_type = $request->fuel_type;
                $property->engine_cc = $request->engine_cc;
                $property->doors = $request->doors;
                $property->passengers = $request->passengers;
                $property->tires = $request->tires;
                $property->drivetrain = $request->drivetrain;
                $property->transmission = $request->transmission;
                $property->condition = $request->condition;
                $property->plate = $request->plate;
                // Defaults para campos de propiedad
                $property->rooms = $request->rooms ?? '0';
                $property->bathrooms = $request->bathrooms ?? '0';
                $property->garage = $request->garage ?? '0';
                $property->floor_levels = $request->floor_levels ?? '0';
                $property->construction = $request->construction ?? '0';
                $property->land = $request->land ?? '0';
                $property->construction_year = $request->construction_year ?? '';
                $property->maintenance = $request->maintenance ?? '0';
            } else {
                // Campos específicos de propiedad
                $property->code = $request->code;
                $property->rooms = $request->rooms;
                $property->bathrooms = $request->bathrooms;
                $property->garage = $request->garage;
                $property->floor_levels = $request->floor_levels;
                $property->construction = $request->construction;
                $property->land = $request->land;
                $property->construction_year = $request->construction_year;
                $property->maintenance = $request->maintenance;
                $property->location = $request->location;
                $property->address = $request->address;
            }

            // Comisión y bolsa (aplica a todos los tipos)
            $property->commission_percentage = $request->commission_percentage;
            $property->commission_notes = $request->commission_notes;
            $property->is_exclusive = $request->is_exclusive ?? false;

            $property->save();

            // Guardar imágenes adicionales
            if ($request->hasFile('additional_images')) {
                foreach ($request->file('additional_images') as $index => $file) {
                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image' => $file->store('uploads/gallery', 'public'),
                        'sort_order' => $index,
                    ]);
                }
            }

            DB::commit();
            return redirect('/property')->with('success', 'Publicación guardada con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/property')->with('error', 'No se pudo guardar la publicación! Error: ' . $th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Handles both real estate and vehicle types dynamically.
     */
    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->canAccessModule('properties', 'edit'), 403, 'Sin permiso para editar publicaciones.');
        DB::beginTransaction();
        try {
            $isVehicle = ($request->property_type === 'vehicle');

            if ($isVehicle) {
                $campos = [
                    'name' => 'required|string|max:150',
                    'subcategory_id' => 'required|exists:subcategories,id',
                    'brand' => 'required|string|max:80',
                    'model' => 'required|string|max:80',
                    'year' => 'required|string|max:10',
                    'price' => 'required|string|max:100',
                ];
            } else {
                $campos = [
                    'name' => 'required|string|max:100',
                    'code' => 'required|string|max:100',
                    'rooms' => 'required|string|max:100',
                    'bathrooms' => 'required|string|max:100',
                    'garage' => 'required|string|max:100',
                    'floor_levels' => 'required|string|max:100',
                    'construction' => 'required|string|max:100',
                    'land' => 'required|string|max:100',
                    'construction_year' => 'required|string|max:100',
                    'maintenance' => 'required|string|max:100',
                    'price' => 'required|string|max:100',
                ];
            }

            $mensaje = ["required" => 'El :attribute es requerido'];
            $this->validate($request, $campos, $mensaje);

            $property = Properties::findOrFail($id);
            if ($request->hasFile('image')) {
                Storage::delete('public/' . $property->image);
                $property->image = $request->file('image')->store('uploads', 'public');
            }

            $property->name = $request->name;
            $property->property_type = $request->property_type ?? $property->property_type;
            $property->subcategory_id = $request->subcategory_id;

            if ($request->subcategory_id) {
                $sub = Subcategory::find($request->subcategory_id);
                $property->category_id = $sub ? $sub->category_id : null;
            }

            $property->description = $request->description;
            $property->price = $request->price;
            $property->currency = $request->currency ?? $property->currency;
            $property->status = $request->status ?? $property->status;

            if ($isVehicle) {
                $property->code = $request->code ?? $property->code;
                $property->brand = $request->brand;
                $property->model = $request->model;
                $property->year = $request->year;
                $property->color = $request->color;
                $property->mileage_km = $request->mileage_km;
                $property->fuel_tank_capacity = $request->fuel_tank_capacity;
                $property->fuel_type = $request->fuel_type;
                $property->engine_cc = $request->engine_cc;
                $property->doors = $request->doors;
                $property->passengers = $request->passengers;
                $property->tires = $request->tires;
                $property->drivetrain = $request->drivetrain;
                $property->transmission = $request->transmission;
                $property->condition = $request->condition;
                $property->plate = $request->plate;
            } else {
                $property->code = $request->code;
                $property->rooms = $request->rooms;
                $property->bathrooms = $request->bathrooms;
                $property->garage = $request->garage;
                $property->floor_levels = $request->floor_levels;
                $property->construction = $request->construction;
                $property->land = $request->land;
                $property->construction_year = $request->construction_year;
                $property->maintenance = $request->maintenance;
                $property->location = $request->location;
                $property->address = $request->address;
            }

            // Comisión y bolsa (aplica a todos los tipos)
            $property->commission_percentage = $request->commission_percentage;
            $property->commission_notes = $request->commission_notes;
            $property->is_exclusive = $request->is_exclusive ?? false;

            $property->update();

            // Guardar imágenes adicionales
            if ($request->hasFile('additional_images')) {
                $maxOrder = $property->images()->max('sort_order') ?? 0;
                foreach ($request->file('additional_images') as $index => $file) {
                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image' => $file->store('uploads/gallery', 'public'),
                        'sort_order' => $maxOrder + $index + 1,
                    ]);
                }
            }

            DB::commit();
            return redirect('/property')->with('success', 'Publicación editada con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/property')->with('success', 'No se pudo editar la publicación!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        abort_unless(auth()->user()->canAccessModule('properties', 'delete'), 403, 'Sin permiso para eliminar publicaciones.');
        DB::beginTransaction();
        try {
            $property = Properties::findOrFail($id);
            if ($property->image) {
                Storage::delete('public/' . $property->image);
            }
            // Eliminar imágenes de galería
            foreach ($property->images as $img) {
                Storage::delete('public/' . $img->image);
                $img->delete();
            }
            $property->delete();
            DB::commit();
            return redirect('/property')->with('success', 'Publicación eliminada con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/property')->with('success', 'No se pudo eliminar la publicación!');
        }
    }

    /**
     * Remove a gallery image from a property.
     */
    public function destroyImage($propertyId, $imageId)
    {
        $image = PropertyImage::where('property_id', $propertyId)
            ->where('id', $imageId)
            ->firstOrFail();

        Storage::delete('public/' . $image->image);
        $image->delete();

        return redirect('/property')->with('success', 'Imagen eliminada con éxito!');
    }

    /**
     * Mostrar vista de detalle de una propiedad (pública)
     */
    public function show($id)
    {
        $property = Properties::with(['category.sector', 'subcategory', 'user', 'images', 'sale'])
            ->findOrFail($id);

        // Incrementar contador de vistas
        $property->incrementViews();

        // Propiedades similares (mismo tipo y sector si existe)
        $similarPropertiesQuery = Properties::published()
            ->available()
            ->where('id', '!=', $property->id)
            ->where('property_type', $property->property_type);

        // Filtrar por sector solo si la propiedad tiene categoría y sector
        if ($property->category && $property->category->sector_id) {
            $similarPropertiesQuery->inSector($property->category->sector_id);
        }

        $similarProperties = $similarPropertiesQuery
            ->with(['category', 'subcategory'])
            ->limit(6)
            ->get();

        // Verificar si es favorito del usuario actual
        $isFavorite = auth()->check()
            ? Favorite::isFavorite(auth()->id(), $property->id)
            : false;

        return view('properties.show', compact('property', 'similarProperties', 'isFavorite'));
    }
}
