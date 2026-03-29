<?php

namespace App\Http\Controllers;

use App\Properties;
use App\Subcategory;
use App\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    /**
     * Admin listing of all vehicles
     * Solo accesible por admin con suscripcion activa
     */
    public function indexAdmin(Request $request)
    {
        $user = Auth::user();

        // Query base: properties con property_type = 'vehicle' del usuario actual
        $query = Properties::where('property_type', 'vehicle')
            ->where('user_id', $user->id)
            ->with(['subcategory.category.sector', 'category']);

        if ($request->has('subcategory_id') && $request->subcategory_id != '') {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        $vehicles = $query->orderBy('created_at', 'desc')->get();

        // Get subcategories for the dropdown (preferably from automotive sector)
        $automotiveSector = Sector::where('slug', 'sector-automotriz')->first();
        if ($automotiveSector) {
            $subcategories = Subcategory::whereHas('category', function ($q) use ($automotiveSector) {
                $q->where('sector_id', $automotiveSector->id);
            })->with('category.sector')->where('is_active', true)->orderBy('name')->get();
        } else {
            $subcategories = Subcategory::with('category.sector')->where('is_active', true)->orderBy('name')->get();
        }

        // If no subcategories found, show all
        if ($subcategories->isEmpty()) {
            $subcategories = Subcategory::with('category.sector')->where('is_active', true)->orderBy('name')->get();
        }

        return view('admin.vehicles.vehicle', compact('vehicles', 'subcategories'));
    }

    /**
     * Store a newly created vehicle
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $campos = [
                'name' => 'required|string|max:150',
                'subcategory_id' => 'required|exists:subcategories,id',
                'brand' => 'required|string|max:80',
                'model' => 'required|string|max:80',
                'year' => 'required|string|max:10',
                'price' => 'required|string|max:60',
                'color' => 'nullable|string|max:50',
                'mileage_km' => 'nullable|string|max:30',
                'fuel_tank_capacity' => 'nullable|string|max:30',
                'fuel_type' => 'nullable|string|max:50',
                'engine_cc' => 'nullable|string|max:30',
                'doors' => 'nullable|string|max:10',
                'passengers' => 'nullable|string|max:10',
                'tires' => 'nullable|string|max:50',
                'drivetrain' => 'nullable|string|max:50',
                'transmission' => 'nullable|string|max:50',
                'condition' => 'nullable|string|max:20',
                'plate' => 'nullable|string|max:20',
            ];

            $mensaje = ["required" => 'El :attribute es requerido'];
            $this->validate($request, $campos, $mensaje);

            $vehicle = new Properties();

            // Campos esenciales para identificar como vehiculo
            $vehicle->property_type = 'vehicle';
            $vehicle->user_id = Auth::id();
            $vehicle->status = 'available';

            if ($request->hasFile('image')) {
                $vehicle->image = $request->file('image')->store('uploads', 'public');
            }

            $vehicle->subcategory_id = $request->subcategory_id;

            // Derivar category_id de la subcategoria
            $sub = Subcategory::find($request->subcategory_id);
            $vehicle->category_id = $sub ? $sub->category_id : null;

            $vehicle->name = $request->name;
            $vehicle->brand = $request->brand;
            $vehicle->model = $request->model;
            $vehicle->year = $request->year;
            $vehicle->color = $request->color;
            $vehicle->mileage_km = $request->mileage_km;
            $vehicle->fuel_tank_capacity = $request->fuel_tank_capacity;
            $vehicle->fuel_type = $request->fuel_type;
            $vehicle->engine_cc = $request->engine_cc;
            $vehicle->doors = $request->doors;
            $vehicle->passengers = $request->passengers;
            $vehicle->tires = $request->tires;
            $vehicle->drivetrain = $request->drivetrain;
            $vehicle->transmission = $request->transmission;
            $vehicle->price = $request->price;
            $vehicle->condition = $request->condition;
            $vehicle->plate = $request->plate;
            $vehicle->currency = $request->currency ?? 'CRC';
            $vehicle->save();

            DB::commit();
            return redirect()->route('vehicles')->with('success', 'Vehiculo creado con exito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect()->route('vehicles')->with('error', 'No se pudo crear el vehiculo! ' . $th->getMessage());
        }
    }

    /**
     * Update the specified vehicle
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $campos = [
                'name' => 'required|string|max:150',
                'subcategory_id' => 'required|exists:subcategories,id',
                'brand' => 'required|string|max:80',
                'model' => 'required|string|max:80',
                'year' => 'required|string|max:10',
                'price' => 'required|string|max:60',
            ];

            $mensaje = ["required" => 'El :attribute es requerido'];
            $this->validate($request, $campos, $mensaje);

            // Buscar solo vehiculos del usuario actual
            $vehicle = Properties::where('id', $id)
                ->where('property_type', 'vehicle')
                ->where('user_id', Auth::id())
                ->firstOrFail();

            if ($request->hasFile('image')) {
                if ($vehicle->image) {
                    Storage::delete('public/' . $vehicle->image);
                }
                $vehicle->image = $request->file('image')->store('uploads', 'public');
            }

            $vehicle->subcategory_id = $request->subcategory_id;

            // Derivar category_id de la subcategoria
            $sub = Subcategory::find($request->subcategory_id);
            $vehicle->category_id = $sub ? $sub->category_id : null;

            $vehicle->name = $request->name;
            $vehicle->brand = $request->brand;
            $vehicle->model = $request->model;
            $vehicle->year = $request->year;
            $vehicle->color = $request->color;
            $vehicle->mileage_km = $request->mileage_km;
            $vehicle->fuel_tank_capacity = $request->fuel_tank_capacity;
            $vehicle->fuel_type = $request->fuel_type;
            $vehicle->engine_cc = $request->engine_cc;
            $vehicle->doors = $request->doors;
            $vehicle->passengers = $request->passengers;
            $vehicle->tires = $request->tires;
            $vehicle->drivetrain = $request->drivetrain;
            $vehicle->transmission = $request->transmission;
            $vehicle->price = $request->price;
            $vehicle->condition = $request->condition;
            $vehicle->plate = $request->plate;
            $vehicle->save();

            DB::commit();
            return redirect()->route('vehicles')->with('success', 'Vehiculo editado con exito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect()->route('vehicles')->with('error', 'No se pudo editar el vehiculo!');
        }
    }

    /**
     * Remove the specified vehicle
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Buscar solo vehiculos del usuario actual
            $vehicle = Properties::where('id', $id)
                ->where('property_type', 'vehicle')
                ->where('user_id', Auth::id())
                ->firstOrFail();

            if ($vehicle->image) {
                Storage::delete('public/' . $vehicle->image);
            }
            $vehicle->delete();

            DB::commit();
            return redirect()->route('vehicles')->with('success', 'Vehiculo eliminado con exito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect()->route('vehicles')->with('error', 'No se pudo eliminar el vehiculo!');
        }
    }
}
