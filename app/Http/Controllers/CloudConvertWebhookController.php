<?php

namespace App\Http\Controllers;

use App\Models\Spin;
use App\Models\SpinJob;
use CloudConvert\CloudConvert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CloudConvertWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('CloudConvert webhook hit', ['headers' => $request->headers->all()]);
        $payload = $request->getContent();
        $signature = $request->header('CloudConvert-Signature');
        $secret = env('CLOUDCONVERT_WEBHOOK_SECRET');

        if (!$this->isValidSignature($payload, $signature, $secret)) {
            return response('Invalid signature', 401);
        }

        $data = $request->all();

        // CloudConvert envía info del job; aquí buscamos job id
        $jobId = data_get($data, 'data.id') ?? data_get($data, 'job.id');
        if (!$jobId) return response('No job id', 400);

        $spin = Spin::where('cloudconvert_job_id', $jobId)->first();
        if (!$spin) return response('Spin not found', 404);

        $spinJob = SpinJob::where('provider_job_id', $jobId)->latest()->first();
        if ($spinJob) $spinJob->update(['status' => 'finished']);

        // Descargar el ZIP desde export/url
        $zipUrl = $this->extractExportZipUrl($data);
        /* if (!$zipUrl) {
            $spin->update(['status' => 'failed', 'error_message' => 'No export ZIP url found']);
            return response('No export url', 400);
        } */

        $disk = 'public';
        $baseDir = "spins/{$spin->id}";
        $zipRel = "{$baseDir}/frames.zip";
        /** @var \Illuminate\Filesystem\FilesystemAdapter $fs */
        $fs = Storage::disk($disk);
        $zipAbs = $fs->path($zipRel);

        Storage::disk($disk)->makeDirectory($baseDir);

        // Descarga simple con fopen/stream (o Guzzle si lo usas)
        $in = fopen($zipUrl, 'r');
        $out = fopen($zipAbs, 'w');
        stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);

        // Unzip
        $framesDir = "{$baseDir}/frames";
        Storage::disk($disk)->makeDirectory($framesDir);

        $zip = new \ZipArchive();
        if ($zip->open($zipAbs) === true) {
            $zip->extractTo(storage_path('app/public/' . $framesDir));
            $zip->close();
        } else {
            $spin->update(['status' => 'failed', 'error_message' => 'Cannot open downloaded zip']);
            return response('Zip open failed', 500);
        }

        // Construir manifest frames.json (files[])
        $files = collect(Storage::disk($disk)->files($framesDir))
            ->filter(fn($p) => preg_match('/\.(webp|jpg|jpeg|png)$/i', $p))
            ->sort()
            ->values();

        $totalFrames = $files->count();

        if ($totalFrames < 10) {
            $spin->update([
                'status' => 'failed',
                'error_message' => 'Too few frames extracted'
            ]);
            return response('Few frames', 200);
        }

        $targetFrames = 72;

        // Selección uniforme
        if ($totalFrames > $targetFrames) {

            $step = $totalFrames / $targetFrames;
            $selected = collect();

            for ($i = 0; $i < $targetFrames; $i++) {
                $index = (int) round($i * $step);
                if ($index >= $totalFrames) {
                    $index = $totalFrames - 1;
                }
                $selected->push($files[$index]);
            }
        } else {
            $selected = $files;
        }

        // Limpiar carpeta antes de renumerar
        foreach (Storage::disk($disk)->files($framesDir) as $file) {
            Storage::disk($disk)->delete($file);
        }

        // Copiar renumerando
        $finalFiles = [];
        $i = 1;

        foreach ($selected as $srcPath) {

            $ext = pathinfo($srcPath, PATHINFO_EXTENSION);
            $newName = sprintf('frame-%03d.%s', $i++, $ext);
            $newPath = "{$framesDir}/{$newName}";

            Storage::disk($disk)->put(
                $newPath,
                Storage::disk($disk)->get($srcPath)
            );

            $finalFiles[] = $newName;
        }

        // Guardar manifest
        $manifest = [
            'count' => count($finalFiles),
            'files' => $finalFiles,
        ];

        Storage::disk($disk)->put(
            "{$baseDir}/frames.json",
            json_encode($manifest)
        );

        $spin->update([
            'frames_dir' => $framesDir,
            'frames_count' => count($finalFiles),
            'status' => 'ready',
            'error_message' => null,
        ]);

        $manifest = [
            'count' => $files->count(),
            'files' => $files->map(fn($p) => basename($p))->all(),
        ];

        Storage::disk($disk)->put("{$baseDir}/frames.json", json_encode($manifest));

        $spin->update([
            'frames_dir' => $framesDir,
            'frames_count' => $files->count(),
            'status' => 'ready',
            'error_message' => null,
        ]);

        if ($spinJob) $spinJob->update(['status' => 'downloaded']);

        return response('OK', 200);
    }

    private function isValidSignature(string $payload, ?string $signature, string $secret): bool
    {
        if (!$signature) return false;

        // CloudConvert usa HMAC SHA-256 en header CloudConvert-Signature. :contentReference[oaicite:6]{index=6}
        $calc = hash_hmac('sha256', $payload, $secret);

        // Comparación segura
        return hash_equals($calc, $signature);
    }

    private function extractExportZipUrl(array $data): ?string
    {
        // Depende del payload exacto, pero normalmente viene dentro de tasks/export/url
        $tasks = data_get($data, 'data.tasks') ?? data_get($data, 'job.tasks') ?? [];
        foreach ($tasks as $t) {
            if (($t['operation'] ?? null) === 'export/url') {
                $files = $t['result']['files'] ?? [];
                foreach ($files as $f) {
                    $url = $f['url'] ?? null;
                    $filename = $f['filename'] ?? '';
                    if ($url && str_ends_with(strtolower($filename), '.zip')) return $url;
                }
            }
        }
        return null;
    }
}
