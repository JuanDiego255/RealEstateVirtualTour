<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Hotspot;
use Illuminate\Support\Facades\Storage;

class HotspotController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $image = null;
        $property_id = $request['property_id'];
        $request->validate([
            'sourceScene' => 'required',
            'targetScene' => 'required',
            'type' => 'required',
            'yaw' => 'required',
            'pitch' => 'required',
            'text' => 'required'
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('uploads', 'public');
        }

        Hotspot::create([
            'type' => $request['type'],
            'yaw' => $request['yaw'],
            'pitch' => $request['pitch'],
            'info' => $request['text'],
            'sourceScene' => $request['sourceScene'],
            'targetScene' => $request['targetScene'],
            'image' => $image
        ]);

        return redirect()->route('config', $property_id)->with('success', '
        Punto de acceso agregado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $hotspot = Hotspot::find($id);
       
        $image = null;
        $property_id = $request['property_id'];
        $request->validate([
            'sourceScene' => 'required',
            'targetScene' => 'required',
            'type' => 'required',
            'yaw' => 'required',
            'pitch' => 'required',
            'text' => 'required'
        ]);
      
        if ($request->hasFile('image')) {
            if($hotspot->image != null){
                Storage::delete('public/' . $hotspot->image);
            }            
            $imageSave = $request->file('image')->store('uploads', 'public');
            $image = $imageSave;
        }
        Hotspot::where('id', $id)->update([
            'type' => $request['type'],
            'yaw' => $request['yaw'],
            'pitch' => $request['pitch'],
            'info' => $request['text'],
            'sourceScene' => $request['sourceScene'],
            'targetScene' => $request['targetScene'],
            'image' => $image
        ]);

        return redirect()->route('config', $property_id)->with(['success' => '
        El punto de acceso se cambió correctamente']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $hotspot = Hotspot::find($id);
        return view('/scene', compact('hotspot', 'id'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $property_id = $request['property_id'];
        Hotspot::destroy($id);
        return redirect()->route('config', $property_id)->with('success', '
        Punto de acceso eliminado exitosamente');
    }
}
