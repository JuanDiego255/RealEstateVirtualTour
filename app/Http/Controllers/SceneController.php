<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Scene;
use App\Hotspot;
use App\Properties;
use Datatables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\DataTables as YajraDataTables;

class SceneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $scene = Scene::where('property_id', $id)->get();
        $sceneIds = $scene->pluck('id')->toArray();
        $hotspots = Hotspot::whereIn('sourceScene', $sceneIds)->get();
        return view('admin.config', compact('hotspots', 'scene', 'id'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function frontendIndex()
    {
        $properties = Properties::all();
        return view('frontend.index', compact('properties'));
    }

    public function dataScene(Request $request)
    {
        if ($request->ajax()) {
            $propertyId = $request->input('property_id');
            $data = Scene::where('property_id', $propertyId)->select('*');
            return YajraDataTables::of($data)
                ->addColumn('status', function ($row) {
                    $sendData = route('changeFScene', $row->id);
                    $csrf = csrf_token();
                    if ($row->status != 0)
                        return '<form method="post" id="status' . $row->id . '" action=' . $sendData . '>
                                    <input name="_token" type="hidden" value=' . $csrf . '>
                                    <input name="property_id" type="hidden" value=' . $row->property_id . '>
                                    <input name="_method" type="hidden" value="PUT">
                                    <input type="checkbox" id="' . $row->id . '" name="check" checked value="1"/>
                                </form>';
                    else
                        return '<form method="post" id="status' . $row->id . '" action=' . $sendData . '>
                                    <input name="_token" type="hidden" value=' . $csrf . '>
                                    <input name="property_id" type="hidden" value=' . $row->property_id . '>
                                    <input name="_method" type="hidden" value="PUT">            
                                    <input type="checkbox" id="' . $row->id . '" name="check" value="1"/>
                                </form>';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="#" class="text-success" data-toggle="modal" 
                        data-target="#detailScene' . $row->id . '"><i class="ti-eye"></i></a>
                            <a href="#" class="text-info" data-toggle="modal" 
                        data-target="#editModal' . $row->id . '"><i class="ti-pencil"></i></a>
                            <a href="#" class="text-danger" data-toggle="modal" 
                        data-target="#deleteModal' . $row->id . '"><i class="ti-trash"></i></a>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    public function dataHotspot(Request $request)
    {
        $propertyId = $request->input('property_id');
        $hotspots = DB::table('hotspots')->where('sc1.property_id',$propertyId)
            ->join('scenes as sc1', 'hotspots.sourceScene', '=', 'sc1.id')
            ->leftJoin('scenes as sc2', 'hotspots.targetScene', '=', 'sc2.id')
            ->select('sc1.title as sourceSceneName', 'sc2.title as targetSceneName', 'hotspots.*');

        return YajraDataTables::of($hotspots)
            ->addColumn('action', function ($row) {
                return '<a href="#" class="text-success" data-toggle="modal"
                    data-target="#detailHotspot' . $row->id . '"><i class="ti-eye"></i></a>
                        <a href="#" class="text-info" data-toggle="modal"
                    data-target="#editHotspot' . $row->id . '"><i class="ti-pencil"></i></a>
                        <a href="#" class="text-danger" data-toggle="modal"
                    data-target="#deleteHotspot' . $row->id . '"><i class="ti-trash"></i></a>';
            })
            ->editColumn('targetSceneName', function ($row) {
                return $row->targetSceneName ?? 'N/A (Solo información)';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function pannellum($id)
    {
        $fscene = DB::table('scenes')
            ->join('properties', 'scenes.property_id', '=', 'properties.id')
            ->where('scenes.property_id', $id)
            ->where('scenes.status', '1')
            ->select(
                'scenes.*',
                'properties.name as property_name'
            )
            ->first();


        $scenes = DB::table('scenes')
            ->where('property_id', $id)
            ->join('properties', 'scenes.property_id', '=', 'properties.id')
            ->select(
                'scenes.*',
                'properties.name as property_name'
            )
            ->get();

        // Obtener hotspots con nombre de escena destino
        $hotspots = DB::table('hotspots')
            ->where('sc1.property_id', $id)
            ->join('scenes as sc1', 'sc1.id', '=', 'hotspots.sourceScene')
            ->leftJoin('scenes as sc2', 'sc2.id', '=', 'hotspots.targetScene')
            ->select('hotspots.*', 'sc2.title as targetSceneName')
            ->get();

        // Obtener polígonos de todas las escenas de esta propiedad
        $sceneIds = $scenes->pluck('id')->toArray();
        $polygons = DB::table('scene_polygons')
            ->whereIn('scene_id', $sceneIds)
            ->get();

        return view('welcome', compact('fscene', 'scenes', 'hotspots', 'polygons'));
    }

    public function allProperties()
    {
        $fscene = DB::table('scenes')->where('status', '1')->first();
        $scenes = DB::table('scenes')->get();
        $hotspots = DB::table('hotspots')
            ->join('scenes', 'scenes.id', '=', 'hotspots.sourceScene')
            ->select('hotspots.*')
            ->get();

        return view('welcome', compact('fscene', 'scenes', 'hotspots'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $isVideo = $request->type === 'video';
        // video_path viene de la subida en chunks (ya está en el servidor)
        $hasChunkedVideo = $isVideo && $request->filled('video_path');

        $rules = [
            'title' => 'required|max:255',
            'type' => 'required',
            'image_ref' => 'required|image'
        ];

        if ($isVideo) {
            // Si se subió por chunks, video_path es requerido; sino, video file es requerido
            if ($hasChunkedVideo) {
                $rules['video_path'] = 'required|string';
            } else {
                $rules['video'] = 'required|file|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime';
            }
        } else {
            $rules['hfov'] = 'required|numeric|min:-360|max:360';
            $rules['yaw'] = 'required|numeric|min:-360|max:360';
            $rules['pitch'] = 'required|numeric|min:-360|max:360';
            $rules['image'] = 'required|image';
        }

        $request->validate($rules);

        $image = null;
        $video = null;
        $imageRef = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('uploads', 'public');
        }

        // Video: desde chunks o desde file upload tradicional
        if ($hasChunkedVideo) {
            $video = $request->video_path;
        } elseif ($request->hasFile('video')) {
            $video = $request->file('video')->store('uploads', 'public');
        }

        if ($request->hasFile('image_ref')) {
            $imageRef = $request->file('image_ref')->store('uploads', 'public');
        }

        $property_id = $request['property_id'];

        Scene::create([
            'title' => $request['title'],
            'property_id' => $property_id,
            'type' => $request['type'],
            'hfov' => $isVideo ? 100 : $request['hfov'],
            'yaw' => $isVideo ? 0 : $request['yaw'],
            'pitch' => $isVideo ? 0 : $request['pitch'],
            'image' => $image ?? $imageRef,
            'video' => $video,
            'image_ref' => $imageRef
        ]);

        return redirect()->route('config', $property_id)->with('success', 'Escena guardada con éxito!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $scene = Scene::find($id);
        return view('/scene', compact('scene', 'id'));
    }

    public function update(Request $request, $id)
    {
        $scene = Scene::find($id);
        $isVideo = $request->type === 'video';

        $rules = [
            'title' => 'required|max:255',
            'type' => 'required',
            'image_ref' => 'nullable|image'
        ];

        if ($isVideo) {
            $rules['video'] = 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime';
        } else {
            $rules['hfov'] = 'required|numeric|min:-360|max:360';
            $rules['yaw'] = 'required|numeric|min:-360|max:360';
            $rules['pitch'] = 'required|numeric|min:-360|max:360';
            $rules['image'] = 'nullable|image';
        }

        $request->validate($rules);
        $property_id = $request['property_id'];

        // Mantener archivos existentes si no se suben nuevos
        $image = $scene->image;
        $imageRef = $scene->image_ref;
        $video = $scene->video;

        if ($request->hasFile('image')) {
            if ($scene->image) Storage::delete('public/' . $scene->image);
            $image = $request->file('image')->store('uploads', 'public');
        }
        if ($request->hasFile('image_ref')) {
            if ($scene->image_ref) Storage::delete('public/' . $scene->image_ref);
            $imageRef = $request->file('image_ref')->store('uploads', 'public');
        }
        if ($request->hasFile('video')) {
            if ($scene->video) Storage::delete('public/' . $scene->video);
            $video = $request->file('video')->store('uploads', 'public');
        }

        $updateData = [
            'title' => $request['title'],
            'type' => $request['type'],
            'property_id' => $property_id,
            'hfov' => $isVideo ? 100 : $request['hfov'],
            'yaw' => $isVideo ? 0 : $request['yaw'],
            'pitch' => $isVideo ? 0 : $request['pitch'],
            'image' => $isVideo ? ($image ?? $imageRef) : $image,
            'video' => $isVideo ? $video : null,
            'image_ref' => $imageRef
        ];

        Scene::where('id', $id)->update($updateData);

        return redirect()->route('config', $property_id)->with('success', 'Escena editada con éxito');
    }

    public function status(Request $request, $id)
    {
        $property_id = $request['property_id'];
        $scene = Scene::find($id);
        $updated = Scene::where('id', $id)->update([
            'status' => $request['check']
        ]);

        return redirect()->route('config', $property_id)->with('success', 'La escena principal cambió con éxito');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $scene = Scene::findOrfail($id);
        if (
            Storage::delete('public/' . $scene->image)
        ) {
            Scene::destroy($id);
        }
        Scene::destroy($id);
        $property_id = $request['property_id'];
        return redirect()->route('config', $property_id)->with('success', '
        Escena eliminada exitosamente');
    }

    /**
     * Recibe un chunk de video y lo guarda temporalmente.
     * Permite subir videos grandes en fragmentos pequeños.
     */
    public function uploadVideoChunk(Request $request)
    {
        $request->validate([
            'chunk' => 'required|file',
            'chunkIndex' => 'required|integer|min:0',
            'totalChunks' => 'required|integer|min:1',
            'uploadId' => 'required|string',
            'fileName' => 'required|string'
        ]);

        $uploadId = $request->uploadId;
        $chunkIndex = $request->chunkIndex;
        $totalChunks = $request->totalChunks;

        // Directorio temporal para chunks
        $tempDir = storage_path('app/chunks/' . $uploadId);
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Guardar chunk
        $chunk = $request->file('chunk');
        $chunkPath = $tempDir . '/chunk_' . str_pad($chunkIndex, 5, '0', STR_PAD_LEFT);
        $chunk->move($tempDir, 'chunk_' . str_pad($chunkIndex, 5, '0', STR_PAD_LEFT));

        // Verificar progreso
        $uploadedChunks = count(File::files($tempDir));
        $progress = round(($uploadedChunks / $totalChunks) * 100);

        return response()->json([
            'success' => true,
            'chunkIndex' => $chunkIndex,
            'uploadedChunks' => $uploadedChunks,
            'totalChunks' => $totalChunks,
            'progress' => $progress
        ]);
    }

    /**
     * Ensambla todos los chunks en el archivo final de video.
     */
    public function completeVideoUpload(Request $request)
    {
        $request->validate([
            'uploadId' => 'required|string',
            'fileName' => 'required|string',
            'totalChunks' => 'required|integer|min:1'
        ]);

        $uploadId = $request->uploadId;
        $fileName = $request->fileName;
        $totalChunks = (int) $request->totalChunks;

        $tempDir = storage_path('app/chunks/' . $uploadId);

        // Verificar que existan todos los chunks
        if (!File::exists($tempDir)) {
            return response()->json(['success' => false, 'error' => 'Upload no encontrado'], 404);
        }

        $chunks = File::files($tempDir);
        if (count($chunks) !== $totalChunks) {
            return response()->json([
                'success' => false,
                'error' => 'Faltan chunks: ' . count($chunks) . ' de ' . $totalChunks
            ], 400);
        }

        // Ordenar chunks por nombre
        $chunkFiles = [];
        foreach ($chunks as $chunk) {
            $chunkFiles[] = $chunk->getPathname();
        }
        sort($chunkFiles);

        // Generar nombre único para el archivo final
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $finalFileName = 'uploads/' . uniqid('video_') . '.' . $extension;
        $finalPath = storage_path('app/public/' . $finalFileName);

        // Asegurar que existe el directorio de destino
        $uploadsDir = storage_path('app/public/uploads');
        if (!File::exists($uploadsDir)) {
            File::makeDirectory($uploadsDir, 0755, true);
        }

        // Ensamblar archivo final
        $finalFile = fopen($finalPath, 'wb');
        if (!$finalFile) {
            return response()->json(['success' => false, 'error' => 'No se pudo crear archivo final'], 500);
        }

        foreach ($chunkFiles as $chunkFile) {
            $chunkContent = file_get_contents($chunkFile);
            fwrite($finalFile, $chunkContent);
        }
        fclose($finalFile);

        // Limpiar directorio temporal
        File::deleteDirectory($tempDir);

        return response()->json([
            'success' => true,
            'videoPath' => $finalFileName,
            'message' => 'Video ensamblado correctamente'
        ]);
    }

    /**
     * Cancela una subida en progreso y limpia los chunks temporales.
     */
    public function cancelVideoUpload(Request $request)
    {
        $uploadId = $request->uploadId;

        if ($uploadId) {
            $tempDir = storage_path('app/chunks/' . $uploadId);
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }

        return response()->json(['success' => true, 'message' => 'Upload cancelado']);
    }
}
