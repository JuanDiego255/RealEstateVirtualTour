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

        return redirect()->route('config', [
            'id'   => $property_id
        ])->with('success', 'Punto de acceso agregado exitosamente');
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

    /**
     * Store multiple hotspots in batch.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBatch(Request $request)
    {
        $property_id = $request->input('property_id');
        $hotspots = $request->input('hotspots', []);
        $createdCount = 0;
        $errors = [];

        foreach ($hotspots as $index => $hotspotData) {
            try {
                // Validar cada hotspot
                if (empty($hotspotData['sourceScene']) || empty($hotspotData['type']) || empty($hotspotData['info'])) {
                    $errors[] = "Hotspot #" . ($index + 1) . ": Datos incompletos";
                    continue;
                }

                // Si es tipo 'scene', verificar targetScene
                if ($hotspotData['type'] === 'scene' && empty($hotspotData['targetScene'])) {
                    $errors[] = "Hotspot #" . ($index + 1) . ": Falta escena destino para tipo enlace";
                    continue;
                }

                $targetScene = $hotspotData['type'] === 'scene' ? $hotspotData['targetScene'] : null;

                Hotspot::create([
                    'type' => $hotspotData['type'],
                    'yaw' => isset($hotspotData['yaw']) ? (float) $hotspotData['yaw'] : 0,
                    'pitch' => isset($hotspotData['pitch']) ? (float) $hotspotData['pitch'] : 0,
                    'video_time' => isset($hotspotData['video_time']) && $hotspotData['video_time'] !== '' ? (float) $hotspotData['video_time'] : null,
                    'pos_x' => isset($hotspotData['pos_x']) && $hotspotData['pos_x'] !== '' ? (float) $hotspotData['pos_x'] : null,
                    'pos_y' => isset($hotspotData['pos_y']) && $hotspotData['pos_y'] !== '' ? (float) $hotspotData['pos_y'] : null,
                    'info' => $hotspotData['info'],
                    'sourceScene' => $hotspotData['sourceScene'],
                    'targetScene' => $targetScene,
                    'image' => null // No soportamos imágenes en batch por ahora
                ]);

                $createdCount++;
            } catch (\Exception $e) {
                $errors[] = "Hotspot #" . ($index + 1) . ": " . $e->getMessage();
            }
        }

        if ($createdCount > 0) {
            return response()->json([
                'success' => true,
                'message' => $createdCount . ' hotspot(s) creados exitosamente',
                'created' => $createdCount,
                'errors' => $errors,
                'redirect' => route('config', $property_id)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo crear ningún hotspot',
            'errors' => $errors
        ], 422);
    }
}
