<?php

namespace App\Http\Controllers;

use App\Properties;
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin()
    {
        //
        $properties = Properties::get();
        return view('admin.properties.property', compact('properties'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //
        DB::beginTransaction();
        try {

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

            $mensaje = ["required" => 'El :attribute es requerido store'];
            $this->validate($request, $campos, $mensaje);

            $property =  new  Properties();
            if ($request->hasFile('image')) {
                $property->image = $request->file('image')->store('uploads', 'public');
            }
            $property->name = $request->name;
            $property->code = $request->code;
            $property->rooms = $request->rooms;
            $property->bathrooms = $request->bathrooms;
            $property->garage = $request->garage;
            $property->floor_levels = $request->floor_levels;
            $property->construction = $request->construction;
            $property->land = $request->land;
            $property->construction_year = $request->construction_year;
            $property->maintenance = $request->maintenance;
            $property->price = $request->price;
            $property->save();
            DB::commit();
            return redirect('/property')->with('success', 'Propiedad guardada con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/property')->with('success', 'Propiedad no se pudo guardar con éxito!');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        DB::beginTransaction();
        try {
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

            $mensaje = ["required" => 'El :attribute es requerido ' . $id . ' update'];
            $this->validate($request, $campos, $mensaje);

            $property = Properties::findOrfail($id);
            if ($request->hasFile('image')) {
                Storage::delete('public/' . $property->image);
                $image = $request->file('image')->store('uploads', 'public');
                $property->image = $image;
            }
            $property->name = $request->name;
            $property->code = $request->code;
            $property->rooms = $request->rooms;
            $property->bathrooms = $request->bathrooms;
            $property->garage = $request->garage;
            $property->floor_levels = $request->floor_levels;
            $property->construction = $request->construction;
            $property->land = $request->land;
            $property->construction_year = $request->construction_year;
            $property->maintenance = $request->maintenance;
            $property->price = $request->price;
            $property->update();
            DB::commit();
            return redirect('/property')->with('success', 'Propiedad editada con éxito!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/property')->with('success', 'Propiedad no se pudo editar con éxito!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        DB::beginTransaction();
        try {

            $property = Properties::findOrfail($id);
            if (
                Storage::delete('public/' . $property->image)
            ) {
                Properties::destroy($id);
            }
            Properties::destroy($id);
            DB::commit();
            return redirect('/property')->with('success', 'Propiedad eliminada con éxito!');
        } catch (\Exception $th) {
            //throw $th;
            DB::rollBack();
        }
    }
}
