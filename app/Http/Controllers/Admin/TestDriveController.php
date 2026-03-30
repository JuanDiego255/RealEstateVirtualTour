<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestDriveVideo;
use App\Properties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $request->validate([
            'title' => 'required|string|max:255',
            'video_type' => 'required|string',
            'video' => 'required|file|mimetypes:video/mp4,video/webm,video/quicktime|max:512000',
            'thumbnail' => 'nullable|image|max:5120',
            'engine_audio' => 'nullable|file|mimes:mp3,wav,ogg|max:20480',
        ]);

        $vehicle = Properties::findOrFail($vehicleId);

        // Guardar video
        $videoPath = $request->file('video')->store('test-drive/videos', 'public');

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
            'engine_audio' => 'nullable|file|mimes:mp3,wav,ogg|max:20480',
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
            'engine_startup_audio' => 'nullable|file|mimes:mp3,wav,ogg|max:20480',
            'engine_idle_audio' => 'nullable|file|mimes:mp3,wav,ogg|max:20480',
            'engine_rev_audio' => 'nullable|file|mimes:mp3,wav,ogg|max:20480',
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
}
