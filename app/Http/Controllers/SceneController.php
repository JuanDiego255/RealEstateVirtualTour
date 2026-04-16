<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Scene;
use App\Hotspot;
use App\Properties;
use App\Models\Spin;
use App\Models\SpinJob;
use App\Services\CloudConvertSpinService;
use CloudConvert\Models\Task;
use Illuminate\Support\Facades\Log;
use App\Sector;
use Datatables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\DataTables as YajraDataTables;

class SceneController extends Controller
{
    /**
     * Display a listing of the resource (admin scene config).
     * Supports both property and vehicle contexts.
     */
    public function index($typeOrId, Request $request, $id = null)
    {
        // Support both /scene/{id} (legacy) and /scene-config/{type}/{id} (new)
        if ($id !== null) {
            // Called from configTyped route: /scene-config/{type}/{id}
            $type = $typeOrId;
        } else {
            // Called from legacy config route: /scene/{id}
            $id = $typeOrId;
            $type = $request->query('type', 'property');
        }

        if ($type === 'vehicle') {
            $scene = DB::table('scenes')
                ->where('scenes.vehicle_id', $id)
                ->leftJoin('spins', function ($join) {
                    $join->on('scenes.spin_id', '=', 'spins.id')
                         ->where('spins.status', '=', 'ready');
                })
                ->select('scenes.*', 'spins.frames_dir as spin_frames_dir', 'spins.frames_count as spin_frames_count')
                ->get();
        } else {
            $scene = DB::table('scenes')
                ->where('scenes.property_id', $id)
                ->leftJoin('spins', function ($join) {
                    $join->on('scenes.spin_id', '=', 'spins.id')
                         ->where('spins.status', '=', 'ready');
                })
                ->select('scenes.*', 'spins.frames_dir as spin_frames_dir', 'spins.frames_count as spin_frames_count')
                ->get();
        }

        $sceneIds = $scene->pluck('id')->toArray();
        $hotspots = Hotspot::whereIn('sourceScene', $sceneIds)->get();

        $property = Properties::find($id);

        return view('admin.config', compact('hotspots', 'scene', 'id', 'type', 'property'));
    }

    /**
     * Update spin display settings for a property
     */
    public function updateSpinSettings(Request $request)
    {
        try {
            $property = Properties::findOrFail($request->input('property_id'));
            $property->spin_active = $request->boolean('spin_active');
            $property->spin_auto_rotate = $request->boolean('spin_auto_rotate');
            $property->save();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display frontend landing page with sectors
     */
    public function frontendIndex()
    {
        $sectors = Sector::where('status', true)
            ->with(['categories' => function ($q) {
                $q->where('status', true);
            }])
            ->get();

        // Keep backward compat: also load properties for legacy usage
        $properties = Properties::all();

        return view('frontend.index', compact('sectors', 'properties'));
    }

    /**
     * Mark one scene as the global demo for /vende-tu-vehiculo.
     * Clears is_demo on all others first.
     */
    public function setDemoScene(Request $request, $id)
    {
        Scene::query()->update(['is_demo' => false]);
        Scene::where('id', $id)->update(['is_demo' => true]);

        return response()->json(['success' => true, 'message' => 'Escena de demo actualizada.']);
    }

    public function dataScene(Request $request)
    {
        if ($request->ajax()) {
            $type = $request->input('type', 'property');
            $itemId = $request->input('property_id');

            if ($type === 'vehicle') {
                $data = Scene::where('vehicle_id', $itemId)->select('*');
            } else {
                $data = Scene::where('property_id', $itemId)->select('*');
            }

            return YajraDataTables::of($data)
                ->addColumn('demo', function ($row) {
                    $url   = route('setDemoScene', $row->id);
                    $csrf  = csrf_token();
                    $active = $row->is_demo ? 'btn-warning' : 'btn-outline-secondary';
                    $icon   = $row->is_demo ? '⭐ Demo activo' : 'Marcar como demo';
                    return '<button type="button"
                                class="btn btn-sm ' . $active . ' set-demo-btn"
                                data-url="' . $url . '"
                                data-token="' . $csrf . '">' . $icon . '</button>';
                })
                ->addColumn('status', function ($row) use ($type) {
                    $sendData = route('changeFScene', $row->id);
                    $csrf = csrf_token();
                    $idValue = $type === 'vehicle' ? $row->vehicle_id : $row->property_id;
                    if ($row->status != 0)
                        return '<form method="post" id="status' . $row->id . '" action=' . $sendData . '>
                                    <input name="_token" type="hidden" value=' . $csrf . '>
                                    <input name="property_id" type="hidden" value=' . $idValue . '>
                                    <input name="type" type="hidden" value=' . $type . '>
                                    <input name="_method" type="hidden" value="PUT">
                                    <input type="checkbox" id="' . $row->id . '" name="check" checked value="1"/>
                                </form>';
                    else
                        return '<form method="post" id="status' . $row->id . '" action=' . $sendData . '>
                                    <input name="_token" type="hidden" value=' . $csrf . '>
                                    <input name="property_id" type="hidden" value=' . $idValue . '>
                                    <input name="type" type="hidden" value=' . $type . '>
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
                ->rawColumns(['status', 'action', 'demo'])
                ->make(true);
        }
    }

    public function dataHotspot(Request $request)
    {
        $type = $request->input('type', 'property');
        $itemId = $request->input('property_id');

        if ($type === 'vehicle') {
            $hotspots = DB::table('hotspots')->where('sc1.vehicle_id', $itemId)
                ->join('scenes as sc1', 'hotspots.sourceScene', '=', 'sc1.id')
                ->leftJoin('scenes as sc2', 'hotspots.targetScene', '=', 'sc2.id')
                ->select('sc1.title as sourceSceneName', 'sc2.title as targetSceneName', 'hotspots.*');
        } else {
            $hotspots = DB::table('hotspots')->where('sc1.property_id', $itemId)
                ->join('scenes as sc1', 'hotspots.sourceScene', '=', 'sc1.id')
                ->leftJoin('scenes as sc2', 'hotspots.targetScene', '=', 'sc2.id')
                ->select('sc1.title as sourceSceneName', 'sc2.title as targetSceneName', 'hotspots.*');
        }

        return YajraDataTables::of($hotspots)
            ->addColumn('action', function ($row) {
                return '<span class="badge badge-light text-muted" title="Yaw: ' . $row->yaw . ' | Pitch: ' . $row->pitch . '"><i class="ti-eye"></i> #' . $row->id . '</span>';
            })
            ->editColumn('targetSceneName', function ($row) {
                return $row->targetSceneName ?? 'N/A (Solo información)';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Virtual tour viewer - supports both property and vehicle
     * Ahora detecta automáticamente el tipo basándose en property_type
     */
    public function pannellum($id, Request $request)
    {
        // Detectar automáticamente el tipo de la publicación
        $property = Properties::find($id);

        // Si tiene property_type='vehicle', usar vehicle_id en scenes
        // Si no, usar property_id en scenes
        $isVehicle = $property && $property->property_type === 'vehicle';

        // Para vehículos, buscar por vehicle_id; para propiedades, por property_id
        if ($isVehicle) {
            // Los vehículos pueden tener escenas vinculadas por vehicle_id O property_id
            // Primero intentamos con vehicle_id, si no hay, con property_id
            $fscene = DB::table('scenes')
                ->leftJoin('properties', function($join) use ($id) {
                    $join->on('scenes.property_id', '=', 'properties.id')
                         ->orOn('scenes.vehicle_id', '=', DB::raw($id));
                })
                ->leftJoin('spins', function ($join) {
                    $join->on('scenes.spin_id', '=', 'spins.id')
                         ->where('spins.status', '=', 'ready');
                })
                ->where(function($query) use ($id) {
                    $query->where('scenes.vehicle_id', $id)
                          ->orWhere('scenes.property_id', $id);
                })
                ->where('scenes.status', '1')
                ->select('scenes.*', 'properties.name as property_name', 'spins.frames_dir as spin_frames_dir', 'spins.frames_count as spin_frames_count')
                ->first();

            $scenes = DB::table('scenes')
                ->leftJoin('properties', function($join) use ($id) {
                    $join->on('scenes.property_id', '=', 'properties.id')
                         ->orOn('scenes.vehicle_id', '=', DB::raw($id));
                })
                ->leftJoin('spins', function ($join) {
                    $join->on('scenes.spin_id', '=', 'spins.id')
                         ->where('spins.status', '=', 'ready');
                })
                ->where(function($query) use ($id) {
                    $query->where('scenes.vehicle_id', $id)
                          ->orWhere('scenes.property_id', $id);
                })
                ->select('scenes.*', 'properties.name as property_name', 'spins.frames_dir as spin_frames_dir', 'spins.frames_count as spin_frames_count')
                ->get();

            $sceneIds = $scenes->pluck('id')->toArray();
            $hotspots = DB::table('hotspots')
                ->whereIn('hotspots.sourceScene', $sceneIds)
                ->leftJoin('scenes as sc2', 'sc2.id', '=', 'hotspots.targetScene')
                ->select('hotspots.*', 'sc2.title as targetSceneName')
                ->get();
        } else {
            $fscene = DB::table('scenes')
                ->join('properties', 'scenes.property_id', '=', 'properties.id')
                ->leftJoin('spins', function ($join) {
                    $join->on('scenes.spin_id', '=', 'spins.id')
                         ->where('spins.status', '=', 'ready');
                })
                ->where('scenes.property_id', $id)
                ->where('scenes.status', '1')
                ->select('scenes.*', 'properties.name as property_name', 'spins.frames_dir as spin_frames_dir', 'spins.frames_count as spin_frames_count')
                ->first();

            $scenes = DB::table('scenes')
                ->where('scenes.property_id', $id)
                ->join('properties', 'scenes.property_id', '=', 'properties.id')
                ->leftJoin('spins', function ($join) {
                    $join->on('scenes.spin_id', '=', 'spins.id')
                         ->where('spins.status', '=', 'ready');
                })
                ->select('scenes.*', 'properties.name as property_name', 'spins.frames_dir as spin_frames_dir', 'spins.frames_count as spin_frames_count')
                ->get();

            $hotspots = DB::table('hotspots')
                ->where('sc1.property_id', $id)
                ->join('scenes as sc1', 'sc1.id', '=', 'hotspots.sourceScene')
                ->leftJoin('scenes as sc2', 'sc2.id', '=', 'hotspots.targetScene')
                ->select('hotspots.*', 'sc2.title as targetSceneName')
                ->get();
        }

        // Get polygons from all scenes
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
     * Supports both property_id and vehicle_id contexts.
     */
    public function store(Request $request, CloudConvertSpinService $cc)
    {
        abort_unless(auth()->user()->canAccessModule('properties', 'scenes'), 403, 'Sin permiso para gestionar escenas.');
        $isVideo = $request->type === 'video';
        $hasChunkedVideo = $isVideo && $request->filled('video_path');
        $type = $request->input('item_type', 'property');
        $isSpin = $request->has('is_spin');
        $spinId = $request->spinId;

        $rules = [
            'title' => 'required|max:255',
            'type' => 'required',
            'image_ref' => 'required|image'
        ];

        if ($isVideo) {
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

        if ($hasChunkedVideo) {
            $video = $request->video_path;
        } elseif ($request->hasFile('video')) {
            $video = $request->file('video')->store('uploads', 'public');
        }

        if ($request->hasFile('image_ref')) {
            $imageRef = $request->file('image_ref')->store('uploads', 'public');
        }

        $sceneData = [
            'title' => $request['title'],
            'type' => $request['type'],
            'hfov' => $isVideo ? 100 : $request['hfov'],
            'yaw' => $isVideo ? 0 : $request['yaw'],
            'pitch' => $isVideo ? 0 : $request['pitch'],
            'image' => $image ?? $imageRef,
            'video' => $video,
            'image_ref' => $imageRef
        ];

        $itemId = $request['property_id'];

        if ($type === 'vehicle') {
            $sceneData['vehicle_id'] = $itemId;
        } else {
            $sceneData['property_id'] = $itemId;
        }

        $scene = Scene::create($sceneData);
        // ✅ Si es escena tipo video y pertenece a un vehículo, disparar SPIN con el video ya subido por chunks
       
        if ($isVideo && !empty($video) && $isSpin) {
            $videoDuration = (float) $request->input('video_duration', 0);
            $spinId = $this->startSpinForVehicleVideo((int)$itemId, $video, $cc, $spinId, $videoDuration);
            $scene->update(['spin_id' => $spinId]);
        }

        return redirect()->route('config', ['id' => $itemId, 'type' => $type])
            ->with('success', 'Escena guardada con éxito!');
    }

    /**
     * Store multiple panorama scenes in batch (AJAX).
     * Each scene: title, hfov, yaw, pitch, image (panorama), image_ref (thumbnail), property_id, item_type
     */
    public function storeBatch(Request $request)
    {
        abort_unless(auth()->user()->canAccessModule('properties', 'scenes'), 403, 'Sin permiso para gestionar escenas.');
        $type    = $request->input('item_type', 'property');
        $itemId  = $request->input('property_id');
        $titles  = $request->input('titles', []);
        $hfovs   = $request->input('hfovs', []);
        $yaws    = $request->input('yaws', []);
        $pitchs  = $request->input('pitchs', []);
        $count   = count($titles);

        if ($count === 0) {
            return response()->json(['success' => false, 'message' => 'No se recibieron escenas'], 422);
        }

        $created = 0;
        $errors  = [];

        for ($i = 0; $i < $count; $i++) {
            try {
                $imageKey    = 'images_' . $i;
                $imageRefKey = 'images_ref_' . $i;

                if (!$request->hasFile($imageKey)) {
                    $errors[] = 'Escena #' . ($i + 1) . ': falta imagen panorámica';
                    continue;
                }

                $title = trim($titles[$i] ?? '');
                if ($title === '') {
                    $errors[] = 'Escena #' . ($i + 1) . ': falta título';
                    continue;
                }

                $hfov  = isset($hfovs[$i])  && is_numeric($hfovs[$i])  ? (float) $hfovs[$i]  : 200;
                $yaw   = isset($yaws[$i])   && is_numeric($yaws[$i])   ? (float) $yaws[$i]   : 0;
                $pitch = isset($pitchs[$i]) && is_numeric($pitchs[$i]) ? (float) $pitchs[$i] : 0;

                $image    = $request->file($imageKey)->store('uploads', 'public');
                $imageRef = $request->hasFile($imageRefKey)
                    ? $request->file($imageRefKey)->store('uploads', 'public')
                    : null;

                $sceneData = [
                    'title'     => $title,
                    'type'      => 'equirectangular',
                    'hfov'      => $hfov,
                    'yaw'       => $yaw,
                    'pitch'     => $pitch,
                    'image'     => $image,
                    'image_ref' => $imageRef,
                ];

                if ($type === 'vehicle') {
                    $sceneData['vehicle_id'] = $itemId;
                } else {
                    $sceneData['property_id'] = $itemId;
                }

                Scene::create($sceneData);
                $created++;
            } catch (\Exception $e) {
                $errors[] = 'Escena #' . ($i + 1) . ': ' . $e->getMessage();
            }
        }

        if ($created > 0) {
            return response()->json([
                'success'  => true,
                'message'  => $created . ' escena(s) creada(s) exitosamente',
                'created'  => $created,
                'errors'   => $errors,
                'redirect' => route('config', ['id' => $itemId, 'type' => $type]),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo crear ninguna escena',
            'errors'  => $errors,
        ], 422);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $scene = Scene::find($id);
        return view('/scene', compact('scene', 'id'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->canAccessModule('properties', 'scenes'), 403, 'Sin permiso para gestionar escenas.');
        $scene = Scene::find($id);
        $isVideo = $request->type === 'video';
        $type = $request->input('item_type', 'property');

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
        $itemId = $request['property_id'];

        // Keep existing files if no new ones uploaded
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
            'hfov' => $isVideo ? 100 : $request['hfov'],
            'yaw' => $isVideo ? 0 : $request['yaw'],
            'pitch' => $isVideo ? 0 : $request['pitch'],
            'image' => $isVideo ? ($image ?? $imageRef) : $image,
            'video' => $isVideo ? $video : null,
            'image_ref' => $imageRef
        ];

        if ($type === 'vehicle') {
            $updateData['vehicle_id'] = $itemId;
            $updateData['property_id'] = null;
        } else {
            $updateData['property_id'] = $itemId;
            $updateData['vehicle_id'] = null;
        }

        Scene::where('id', $id)->update($updateData);

        return redirect()->route('config', ['id' => $itemId, 'type' => $type])
            ->with('success', 'Escena editada con éxito');
    }

    public function status(Request $request, $id)
    {
        $property_id = $request['property_id'];
        $type = $request->input('type', 'property');
        $scene = Scene::find($id);
        $updated = Scene::where('id', $id)->update([
            'status' => $request['check']
        ]);

        return redirect()->route('config', ['id' => $property_id, 'type' => $type])
            ->with('success', 'La escena principal cambió con éxito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        abort_unless(auth()->user()->canAccessModule('properties', 'scenes'), 403, 'Sin permiso para gestionar escenas.');
        $scene = Scene::findOrfail($id);
        if (
            Storage::delete('public/' . $scene->image)
        ) {
            Scene::destroy($id);
        }
        Scene::destroy($id);
        $property_id = $request['property_id'];
        $type = $request->input('item_type', 'property');
        return redirect()->route('config', ['id' => $property_id, 'type' => $type])
            ->with('success', 'Escena eliminada exitosamente');
    }

    /**
     * Recibe un chunk de video y lo guarda temporalmente.
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

        $tempDir = storage_path('app/chunks/' . $uploadId);
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $chunk = $request->file('chunk');
        $chunkPath = $tempDir . '/chunk_' . str_pad($chunkIndex, 5, '0', STR_PAD_LEFT);
        $chunk->move($tempDir, 'chunk_' . str_pad($chunkIndex, 5, '0', STR_PAD_LEFT));

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
        $isSpin = $request->_isSpin;

        $tempDir = storage_path('app/chunks/' . $uploadId);

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

        $chunkFiles = [];
        foreach ($chunks as $chunk) {
            $chunkFiles[] = $chunk->getPathname();
        }
        sort($chunkFiles);

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($isSpin) {
            $spin = Spin::create([
                'vehicle_id' => $request->_propertyId,
                'status' => 'uploaded',
            ]);
            $finalFileName = "spins/{$spin->id}/" . uniqid('video_') . '.' . $extension;
        } else {
            $finalFileName = 'uploads/' . uniqid('video_') . '.' . $extension;
        }
        $finalPath = storage_path('app/public/' . $finalFileName);

        $finalDir = dirname($finalPath);
        if (!File::exists($finalDir)) {
            File::makeDirectory($finalDir, 0755, true);
        }

        $finalFile = fopen($finalPath, 'wb');
        if (!$finalFile) {
            return response()->json([
                'success' => false,
                'error' => 'No se pudo crear archivo final'
            ], 500);
        }

        foreach ($chunkFiles as $chunkFile) {
            $chunkContent = file_get_contents($chunkFile);
            fwrite($finalFile, $chunkContent);
        }
        fclose($finalFile);

        File::deleteDirectory($tempDir);

        $spin->update([
            'video_path' => $finalFileName,
            'status' => 'processing',
        ]);

        return response()->json([
            'success' => true,
            'videoPath' => $finalFileName,
            'spinId' => $spin->id,
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

    private function startSpinForVehicleVideo(int $vehicleId, string $videoPath, CloudConvertSpinService $cc, $spinId, float $videoDuration = 0): ?int
    {
        // Crea Spin
        $spin = Spin::where('id', $spinId)->first();
        try {
            // fps calculado dinámicamente: 72 frames distribuidos en toda la duración del video
            $desiredFrames = 72;
            if ($videoDuration > 0) {
                $fps = (float) ($desiredFrames / $videoDuration);
            } else {
                // Fallback conservador para videos ~40s
                $fps = 1.8;
            }
            $ext = env('SPIN_FRAME_EXT', 'jpg');

            // 1) Crear job CloudConvert (usa tu versión que ya funciona con command/ffmpeg)
            $job = $cc->createSpinJob($fps, $ext);

            $spin->update(['cloudconvert_job_id' => $job->getId()]);

            $spinJob = SpinJob::create([
                'spin_id' => $spin->id,
                'provider' => 'cloudconvert',
                'provider_job_id' => $job->getId(),
                'status' => 'created',
            ]);
            $tasks = $job->getTasks();
            // 2) Encontrar task import-1 por nombre
            $importTask = null;
            foreach ($tasks as $t) {
                if (method_exists($t, 'getName') && $t->getName() === 'import-1') {
                    $importTask = $t;
                    break;
                }
            }
            if (!$importTask) {
                Log::error('Spin start failed', [
                    'vehicle_id' => $vehicleId,
                    'video_path' => $videoPath,
                    'spin_id' => $spin->id,
                    'error' => 'CloudConvert: no se encontró el task import-1',
                ]);
                dd('error');
                throw new \RuntimeException('CloudConvert: no se encontró el task import-1');
            }
            // 3) Subir video al import task (pasando el objeto Task)
            $cc->uploadToImportTask($importTask, 'public', $videoPath);
            $spinJob->update(['status' => 'uploaded']);

            return $spin->id;
        } catch (\Throwable $e) {
            Log::error('Spin start failed', [
                'vehicle_id' => $vehicleId,
                'video_path' => $videoPath,
                'spin_id' => $spin->id,
                'error' => $e->getMessage(),
            ]);
            dd($e->getMessage());

            $spin->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return $spin->id; // igual devolvemos ID para trazabilidad
        }
    }
}
