<?php

namespace App\Http\Controllers;

use App\Models\Spin;
use App\Models\SpinJob;
use App\Services\CloudConvertSpinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SpinController extends Controller
{
    public function store(Request $request, CloudConvertSpinService $cc)
    {
        $request->validate([
            'vehicle_id' => ['nullable', 'integer'],
            'video' => ['required', 'file', 'mimes:mp4,mov,webm', 'max:512000'],
        ]);

        $disk = 'public';

        $spin = Spin::create([
            'vehicle_id' => $request->vehicle_id,
            'status' => 'uploaded',
        ]);

        $videoPath = $request->file('video')->store("spins/{$spin->id}", $disk);

        $spin->update([
            'video_path' => $videoPath,
            'status' => 'processing',
        ]);

        // fps dinámico según duración del video
        $videoDuration = (float) $request->input('video_duration', 0);
        $desiredFrames = 72;
        $fps = $videoDuration > 0 ? ($desiredFrames / $videoDuration) : 1.8;

        // 1) Crear job
        $job = $cc->createSpinJob($fps, 'jpg');
        $spin->update(['cloudconvert_job_id' => $job->getId()]);

        $spinJob = SpinJob::create([
            'spin_id' => $spin->id,
            'provider' => 'cloudconvert',
            'provider_job_id' => $job->getId(),
            'status' => 'created',
        ]);

        // 2) Subir archivo al import task
        $tasks = $job->getTasks();

        // Buscar por el nombre exacto que definimos en createSpinJob(): 'import-1'
        $importTask = null;
        foreach ($tasks as $t) {
            if (method_exists($t, 'getName') && $t->getName() === 'import-1') {
                $importTask = $t;
                break;
            }
        }
        if (!$importTask) {
            throw new \RuntimeException('CloudConvert: no se encontró el task import-1');
        }

        // subir el video al import task (pasando el objeto)
        $cc->uploadToImportTask($importTask, $disk, $videoPath);

        $spinJob->update(['status' => 'uploaded']);

        return back()->with('success', 'Video cargado. Procesando spin...');
    }
}
