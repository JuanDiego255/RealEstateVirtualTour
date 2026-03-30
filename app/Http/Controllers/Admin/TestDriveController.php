<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestDriveVideo;
use App\Properties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class TestDriveController extends Controller
{
    /**
     * Display test drive configuration for a vehicle
     */
    public function index($vehicleId)
    {
        $vehicle = Properties::findOrFail($vehicleId);
        $videos = TestDriveVideo::where('property_id', $vehicleId)
            ->orderBy('order')
            ->get();

        $videoTypes = TestDriveVideo::videoTypes();

        return view('admin.test-drive.index', compact('vehicle', 'videos', 'videoTypes'));
    }

    /**
     * Store a new test drive video
     */
    public function storeVideo(Request $request, $vehicleId)
    {
        // Video puede venir como archivo o como path (subido por chunks)
        $rules = [
            'title' => 'required|string|max:255',
            'video_type' => 'required|string',
            'thumbnail' => 'nullable|image|max:5120',
            'engine_audio' => 'nullable|file|mimes:mp3,wav,ogg,mp4,m4a|max:20480',
        ];

        // Si viene video_path, es subida por chunks
        if ($request->filled('video_path')) {
            $rules['video_path'] = 'required|string';
        } else {
            $rules['video'] = 'required|file|mimetypes:video/mp4,video/webm,video/quicktime|max:512000';
        }

        $request->validate($rules);

        $vehicle = Properties::findOrFail($vehicleId);

        // Obtener path del video (ya subido por chunks o subida tradicional)
        if ($request->filled('video_path')) {
            $videoPath = $request->video_path;
        } else {
            $videoPath = $request->file('video')->store('test-drive/videos', 'public');
        }

        // Guardar thumbnail si existe
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('test-drive/thumbnails', 'public');
        }

        // Guardar audio del motor si existe
        $audioPath = null;
        if ($request->hasFile('engine_audio')) {
            $audioPath = $request->file('engine_audio')->store('test-drive/audio', 'public');
        }

        // Obtener duración del video (aproximada)
        $duration = 0;
        try {
            $videoFullPath = Storage::disk('public')->path($videoPath);
            if (function_exists('shell_exec')) {
                $output = shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($videoFullPath) . " 2>/dev/null");
                if ($output) {
                    $duration = (int) floatval(trim($output));
                }
            }
        } catch (\Exception $e) {
            // Ignorar errores de ffprobe
        }

        $maxOrder = TestDriveVideo::where('property_id', $vehicleId)->max('order') ?? 0;

        TestDriveVideo::create([
            'property_id' => $vehicleId,
            'title' => $request->title,
            'description' => $request->description,
            'video_path' => $videoPath,
            'thumbnail' => $thumbnailPath,
            'engine_audio' => $audioPath,
            'video_type' => $request->video_type,
            'duration_seconds' => $duration,
            'autoplay' => $request->boolean('autoplay'),
            'loop' => $request->boolean('loop'),
            'status' => true,
            'order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.test-drive.index', $vehicleId)
            ->with('success', 'Video de Test Drive agregado exitosamente');
    }

    /**
     * Update a test drive video
     */
    public function updateVideo(Request $request, $vehicleId, $videoId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_type' => 'required|string',
            'video' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:512000',
            'thumbnail' => 'nullable|image|max:5120',
            'engine_audio' => 'nullable|file|mimes:mp3,wav,ogg,mp4,m4a|max:20480',
        ]);

        $video = TestDriveVideo::findOrFail($videoId);

        // Actualizar video si se sube uno nuevo
        if ($request->hasFile('video')) {
            if ($video->video_path) {
                Storage::disk('public')->delete($video->video_path);
            }
            $video->video_path = $request->file('video')->store('test-drive/videos', 'public');
        }

        // Actualizar thumbnail
        if ($request->hasFile('thumbnail')) {
            if ($video->thumbnail) {
                Storage::disk('public')->delete($video->thumbnail);
            }
            $video->thumbnail = $request->file('thumbnail')->store('test-drive/thumbnails', 'public');
        }

        // Actualizar audio
        if ($request->hasFile('engine_audio')) {
            if ($video->engine_audio) {
                Storage::disk('public')->delete($video->engine_audio);
            }
            $video->engine_audio = $request->file('engine_audio')->store('test-drive/audio', 'public');
        }

        $video->update([
            'title' => $request->title,
            'description' => $request->description,
            'video_type' => $request->video_type,
            'autoplay' => $request->boolean('autoplay'),
            'loop' => $request->boolean('loop'),
        ]);

        return redirect()->route('admin.test-drive.index', $vehicleId)
            ->with('success', 'Video actualizado exitosamente');
    }

    /**
     * Delete a test drive video
     */
    public function destroyVideo($vehicleId, $videoId)
    {
        $video = TestDriveVideo::findOrFail($videoId);

        // Eliminar archivos
        if ($video->video_path) {
            Storage::disk('public')->delete($video->video_path);
        }
        if ($video->thumbnail) {
            Storage::disk('public')->delete($video->thumbnail);
        }
        if ($video->engine_audio) {
            Storage::disk('public')->delete($video->engine_audio);
        }

        $video->delete();

        return redirect()->route('admin.test-drive.index', $vehicleId)
            ->with('success', 'Video eliminado exitosamente');
    }

    /**
     * Toggle video status
     */
    public function toggleStatus($vehicleId, $videoId)
    {
        $video = TestDriveVideo::findOrFail($videoId);
        $video->status = !$video->status;
        $video->save();

        return response()->json(['success' => true, 'status' => $video->status]);
    }

    /**
     * Reorder videos
     */
    public function reorder(Request $request, $vehicleId)
    {
        $order = $request->input('order', []);

        foreach ($order as $index => $videoId) {
            TestDriveVideo::where('id', $videoId)
                ->where('property_id', $vehicleId)
                ->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Update engine audio settings for the vehicle
     */
    public function updateEngineAudio(Request $request, $vehicleId)
    {
        $request->validate([
            'engine_startup_audio' => 'nullable|file|mimes:mp3,wav,ogg,mp4,m4a|max:20480',
            'engine_idle_audio' => 'nullable|file|mimes:mp3,wav,ogg,mp4,m4a|max:20480',
            'engine_rev_audio' => 'nullable|file|mimes:mp3,wav,ogg,mp4,m4a|max:20480',
            'interior_pov_video' => 'nullable|file|mimetypes:video/mp4,video/webm|max:512000',
            'featured_image' => 'nullable|image|max:10240',
        ]);

        $vehicle = Properties::findOrFail($vehicleId);

        // Procesar cada archivo
        $audioFields = ['engine_startup_audio', 'engine_idle_audio', 'engine_rev_audio'];

        foreach ($audioFields as $field) {
            if ($request->hasFile($field)) {
                // Eliminar archivo anterior
                if ($vehicle->$field) {
                    Storage::disk('public')->delete($vehicle->$field);
                }
                $vehicle->$field = $request->file($field)->store('test-drive/engine-audio', 'public');
            }
        }

        // Video POV
        if ($request->hasFile('interior_pov_video')) {
            if ($vehicle->interior_pov_video) {
                Storage::disk('public')->delete($vehicle->interior_pov_video);
            }
            $vehicle->interior_pov_video = $request->file('interior_pov_video')
                ->store('test-drive/pov-videos', 'public');
        }

        // Imagen destacada
        if ($request->hasFile('featured_image')) {
            if ($vehicle->featured_image) {
                Storage::disk('public')->delete($vehicle->featured_image);
            }
            $vehicle->featured_image = $request->file('featured_image')
                ->store('test-drive/featured', 'public');
        }

        $vehicle->save();

        return redirect()->route('admin.test-drive.index', $vehicleId)
            ->with('success', 'Configuración de audio del motor actualizada');
    }

    /**
     * Delete a specific engine audio file
     */
    public function deleteEngineAudio($vehicleId, $field)
    {
        $allowedFields = ['engine_startup_audio', 'engine_idle_audio', 'engine_rev_audio', 'interior_pov_video', 'featured_image'];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'error' => 'Campo no válido'], 400);
        }

        $vehicle = Properties::findOrFail($vehicleId);

        if ($vehicle->$field) {
            Storage::disk('public')->delete($vehicle->$field);
            $vehicle->$field = null;
            $vehicle->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Preview test drive experience
     */
    public function preview($vehicleId)
    {
        $vehicle = Properties::findOrFail($vehicleId);
        $videos = TestDriveVideo::getForProperty($vehicleId);

        return view('admin.test-drive.preview', compact('vehicle', 'videos'));
    }

    /**
     * Upload a video chunk
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
     * Complete video upload by assembling chunks
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
        $finalFileName = 'test-drive/videos/' . uniqid('video_') . '.' . $extension;
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

        return response()->json([
            'success' => true,
            'videoPath' => $finalFileName,
            'message' => 'Video ensamblado correctamente'
        ]);
    }

    /**
     * Cancel video upload and clean up chunks
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
