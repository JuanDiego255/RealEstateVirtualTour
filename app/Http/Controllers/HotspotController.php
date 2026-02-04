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

        // Validación condicional: targetScene solo requerido si tipo es 'scene'
        $rules = [
            'sourceScene' => 'required',
            'type' => 'required',
            'yaw' => 'required',
            'pitch' => 'required',
            'text' => 'required',
            'image' => 'nullable|image'
        ];

        // Solo requerir targetScene si el tipo es 'scene' (enlace)
        if ($request['type'] === 'scene') {
            $rules['targetScene'] = 'required';
        }

        $request->validate($rules);

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('uploads', 'public');
        }

        // targetScene puede ser null para tipo 'info'
        $targetScene = $request['type'] === 'scene' ? $request['targetScene'] : null;

        Hotspot::create([
            'type' => $request['type'],
            'yaw'   => (float) $request['yaw'],
            'pitch' => (float) $request['pitch'],
            'video_time' => $request['video_time'] !== null ? (float) $request['video_time'] : null,
            'pos_x' => $request['pos_x'] !== null ? (float) $request['pos_x'] : null,
            'pos_y' => $request['pos_y'] !== null ? (float) $request['pos_y'] : null,
            'info' => $request['text'],
            'sourceScene' => $request['sourceScene'],
            'targetScene' => $targetScene,
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

        $property_id = $request['property_id'];

        // Validación condicional: targetScene solo requerido si tipo es 'scene'
        $rules = [
            'sourceScene' => 'required',
            'type' => 'required',
            'yaw' => 'required',
            'pitch' => 'required',
            'text' => 'required',
            'image' => 'nullable|image'
        ];

        // Solo requerir targetScene si el tipo es 'scene' (enlace)
        if ($request['type'] === 'scene') {
            $rules['targetScene'] = 'required';
        }

        $request->validate($rules);

        // Mantener imagen existente si no se sube una nueva
        $image = $hotspot->image;
        if ($request->hasFile('image')) {
            if ($hotspot->image != null) {
                Storage::delete('public/' . $hotspot->image);
            }
            $image = $request->file('image')->store('uploads', 'public');
        }

        // targetScene puede ser null para tipo 'info'
        $targetScene = $request['type'] === 'scene' ? $request['targetScene'] : null;

        Hotspot::where('id', $id)->update([
            'type' => $request['type'],
            'yaw' => $request['yaw'],
            'pitch' => $request['pitch'],
            'video_time' => $request['video_time'] !== null ? (float) $request['video_time'] : null,
            'pos_x' => $request['pos_x'] !== null ? (float) $request['pos_x'] : null,
            'pos_y' => $request['pos_y'] !== null ? (float) $request['pos_y'] : null,
            'info' => $request['text'],
            'sourceScene' => $request['sourceScene'],
            'targetScene' => $targetScene,
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
