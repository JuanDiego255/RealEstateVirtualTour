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
            'target_yaw' => $request->filled('target_yaw') ? (float) $request['target_yaw'] : null,
            'target_pitch' => $request->filled('target_pitch') ? (float) $request['target_pitch'] : null,
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
            'target_yaw' => $request->filled('target_yaw') ? (float) $request['target_yaw'] : null,
            'target_pitch' => $request->filled('target_pitch') ? (float) $request['target_pitch'] : null,
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
     */
    public function storeBatch(Request $request)
    {
        $property_id = $request->input('property_id');
        $hotspots = $request->input('hotspots', []);
        $createdCount = 0;
        $errors = [];

        foreach ($hotspots as $index => $hotspotData) {
            try {
                if (empty($hotspotData['sourceScene']) || empty($hotspotData['type']) || empty($hotspotData['info'])) {
                    $errors[] = "Hotspot #" . ($index + 1) . ": Datos incompletos";
                    continue;
                }

                if ($hotspotData['type'] === 'scene' && empty($hotspotData['targetScene'])) {
                    $errors[] = "Hotspot #" . ($index + 1) . ": Falta escena destino para tipo enlace";
                    continue;
                }

                $targetScene = $hotspotData['type'] === 'scene' ? $hotspotData['targetScene'] : null;

                $image = null;
                $fileKey = 'images_' . $index;
                if ($request->hasFile($fileKey)) {
                    $image = $request->file($fileKey)->store('uploads', 'public');
                }

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
                    'target_yaw' => isset($hotspotData['target_yaw']) && $hotspotData['target_yaw'] !== '' ? (float) $hotspotData['target_yaw'] : null,
                    'target_pitch' => isset($hotspotData['target_pitch']) && $hotspotData['target_pitch'] !== '' ? (float) $hotspotData['target_pitch'] : null,
                    'image' => $image
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

    /**
     * Get hotspots for a specific scene (AJAX).
     */
    public function getByScene(Request $request)
    {
        $sceneId = $request->input('scene_id');
        $hotspots = Hotspot::where('sourceScene', $sceneId)->get()->map(function ($h) {
            return [
                'id' => $h->id,
                'type' => $h->type,
                'yaw' => $h->yaw,
                'pitch' => $h->pitch,
                'video_time' => $h->video_time,
                'pos_x' => $h->pos_x,
                'pos_y' => $h->pos_y,
                'info' => $h->info,
                'sourceScene' => $h->sourceScene,
                'targetScene' => $h->targetScene,
                'target_yaw' => $h->target_yaw,
                'target_pitch' => $h->target_pitch,
                'image' => $h->image,
                'image_url' => $h->image ? route('file', $h->image) : null,
            ];
        });

        return response()->json(['success' => true, 'hotspots' => $hotspots]);
    }

    /**
     * Update multiple hotspots in batch (AJAX).
     */
    public function updateBatch(Request $request)
    {
        $property_id = $request->input('property_id');
        $hotspots = $request->input('hotspots', []);
        $updatedCount = 0;
        $errors = [];

        foreach ($hotspots as $index => $data) {
            try {
                $id = $data['id'] ?? null;
                if (!$id) {
                    $errors[] = "Hotspot #" . ($index + 1) . ": Sin ID";
                    continue;
                }

                $hotspot = Hotspot::find($id);
                if (!$hotspot) {
                    $errors[] = "Hotspot #" . ($index + 1) . ": No encontrado";
                    continue;
                }

                $targetScene = ($data['type'] ?? '') === 'scene' ? ($data['targetScene'] ?? null) : null;

                $image = $hotspot->image;
                $fileKey = 'images_' . $index;
                if ($request->hasFile($fileKey)) {
                    if ($hotspot->image) {
                        Storage::delete('public/' . $hotspot->image);
                    }
                    $image = $request->file($fileKey)->store('uploads', 'public');
                }

                $hotspot->update([
                    'type' => $data['type'] ?? $hotspot->type,
                    'yaw' => isset($data['yaw']) ? (float) $data['yaw'] : $hotspot->yaw,
                    'pitch' => isset($data['pitch']) ? (float) $data['pitch'] : $hotspot->pitch,
                    'video_time' => isset($data['video_time']) && $data['video_time'] !== '' ? (float) $data['video_time'] : null,
                    'pos_x' => isset($data['pos_x']) && $data['pos_x'] !== '' ? (float) $data['pos_x'] : null,
                    'pos_y' => isset($data['pos_y']) && $data['pos_y'] !== '' ? (float) $data['pos_y'] : null,
                    'info' => $data['info'] ?? $hotspot->info,
                    'sourceScene' => $data['sourceScene'] ?? $hotspot->sourceScene,
                    'targetScene' => $targetScene,
                    'target_yaw' => isset($data['target_yaw']) && $data['target_yaw'] !== '' ? (float) $data['target_yaw'] : null,
                    'target_pitch' => isset($data['target_pitch']) && $data['target_pitch'] !== '' ? (float) $data['target_pitch'] : null,
                    'image' => $image,
                ]);

                $updatedCount++;
            } catch (\Exception $e) {
                $errors[] = "Hotspot #" . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => $updatedCount > 0,
            'message' => $updatedCount . ' hotspot(s) actualizados exitosamente',
            'updated' => $updatedCount,
            'errors' => $errors,
            'redirect' => route('config', $property_id)
        ]);
    }

    /**
     * Delete a hotspot via AJAX.
     */
    public function destroyAjax($id)
    {
        $hotspot = Hotspot::find($id);
        if (!$hotspot) {
            return response()->json(['success' => false, 'message' => 'No encontrado'], 404);
        }
        if ($hotspot->image) {
            Storage::delete('public/' . $hotspot->image);
        }
        $hotspot->delete();
        return response()->json(['success' => true, 'message' => 'Eliminado']);
    }
}
