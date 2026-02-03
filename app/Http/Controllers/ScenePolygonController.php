<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ScenePolygon;
use App\Scene;

class ScenePolygonController extends Controller
{
    /**
     * Obtener todos los polígonos de una escena (para AJAX)
     */
    public function index($sceneId)
    {
        $polygons = ScenePolygon::where('scene_id', $sceneId)->get();
        return response()->json($polygons);
    }

    /**
     * Guardar un nuevo polígono
     */
    public function store(Request $request)
    {
        $request->validate([
            'scene_id' => 'required|exists:scenes,id',
            'name' => 'required|max:255',
            'fill_color' => 'required|max:7',
            'fill_opacity' => 'required|numeric|min:0|max:1',
            'stroke_color' => 'required|max:7',
            'stroke_width' => 'required|integer|min:0|max:10',
            'points' => 'required|json'
        ]);

        $polygon = ScenePolygon::create([
            'scene_id' => $request->scene_id,
            'name' => $request->name,
            'fill_color' => $request->fill_color,
            'fill_opacity' => $request->fill_opacity,
            'stroke_color' => $request->stroke_color,
            'stroke_width' => $request->stroke_width,
            'points' => json_decode($request->points, true)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Polígono guardado exitosamente',
            'polygon' => $polygon
        ]);
    }

    /**
     * Actualizar un polígono
     */
    public function update(Request $request, $id)
    {
        $polygon = ScenePolygon::findOrFail($id);

        $request->validate([
            'name' => 'required|max:255',
            'fill_color' => 'required|max:7',
            'fill_opacity' => 'required|numeric|min:0|max:1',
            'stroke_color' => 'required|max:7',
            'stroke_width' => 'required|integer|min:0|max:10',
            'points' => 'required|json'
        ]);

        $polygon->update([
            'name' => $request->name,
            'fill_color' => $request->fill_color,
            'fill_opacity' => $request->fill_opacity,
            'stroke_color' => $request->stroke_color,
            'stroke_width' => $request->stroke_width,
            'points' => json_decode($request->points, true)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Polígono actualizado exitosamente',
            'polygon' => $polygon
        ]);
    }

    /**
     * Eliminar un polígono
     */
    public function destroy($id)
    {
        $polygon = ScenePolygon::findOrFail($id);
        $polygon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Polígono eliminado exitosamente'
        ]);
    }
}
