<?php

namespace App\Http\Controllers;

use App\Vehicle;
use App\Category;
use App\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    /**
     * Admin listing of all vehicles
     */
    public function indexAdmin(Request $request)
    {
        $query = Vehicle::with('category.sector');

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        $vehicles = $query->get();

        // Get only automotive sector categories for the dropdown
        $automotiveSector = Sector::where('slug', 'sector-automotriz')->first();
        $categories = $automotiveSector
            ? Category::where('sector_id', $automotiveSector->id)->get()
            : Category::whereHas('sector', function($q) {
                $q->where('slug', 'like', '%automotriz%')->orWhere('slug', 'like', '%auto%');
            })->get();

        // If no automotive categories found, show all categories
        if ($categories->isEmpty()) {
            $categories = Category::all();
        }

        return view('admin.vehicles.vehicle', compact('vehicles', 'categories'));
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
                'category_id' => 'required|exists:categories,id',
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

            $vehicle = new Vehicle();
            if ($request->hasFile('image')) {
                $vehicle->image = $request->file('image')->store('uploads', 'public');
            }
            $vehicle->category_id = $request->category_id;
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
            $vehicle->status = true;
            $vehicle->save();

            DB::commit();
            return redirect('/vehicles')->with('success', '¡Vehículo creado con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/vehicles')->with('error', '¡No se pudo crear el vehículo! ' . $th->getMessage());
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
                'category_id' => 'required|exists:categories,id',
                'brand' => 'required|string|max:80',
                'model' => 'required|string|max:80',
                'year' => 'required|string|max:10',
                'price' => 'required|string|max:60',
            ];

            $mensaje = ["required" => 'El :attribute es requerido'];
            $this->validate($request, $campos, $mensaje);

            $vehicle = Vehicle::findOrFail($id);
            if ($request->hasFile('image')) {
                if ($vehicle->image) {
                    Storage::delete('public/' . $vehicle->image);
                }
                $vehicle->image = $request->file('image')->store('uploads', 'public');
            }
            $vehicle->category_id = $request->category_id;
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
            $vehicle->update();

            DB::commit();
            return redirect('/vehicles')->with('success', '¡Vehículo editado con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/vehicles')->with('error', '¡No se pudo editar el vehículo!');
        }
    }

    /**
     * Remove the specified vehicle
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $vehicle = Vehicle::findOrFail($id);
            if ($vehicle->image) {
                Storage::delete('public/' . $vehicle->image);
            }
            $vehicle->delete();
            DB::commit();
            return redirect('/vehicles')->with('success', '¡Vehículo eliminado con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/vehicles')->with('error', '¡No se pudo eliminar el vehículo!');
        }
    }
}
