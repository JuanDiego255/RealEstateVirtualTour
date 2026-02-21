<?php

namespace App\Services;

use CloudConvert\CloudConvert;
use CloudConvert\Models\Job;
use CloudConvert\Models\Task;
use Illuminate\Support\Facades\Storage;

class CloudConvertSpinService
{
    public function client(): CloudConvert
    {
        return new CloudConvert([
            'api_key' => config('services.cloudconvert.key'),
            'sandbox' => false,
        ]);
    }

    /**
     * Job:
     * 1) import/upload
     * 2) command (ffmpeg) -> extrae frames a /output/frame-XXX.jpg
     * 3) archive -> zip de todos los frames
     * 4) export/url -> URL del zip
     */
    public function createSpinJob(int $fps = 2, string $imgExt = 'jpg'): Job
    {
        $cloudconvert = $this->client();
        $desiredFrames = 180;

        // $fps debe ser float, NO int
        // ideal: $fps = $desiredFrames / $duration;
        $fpsStr = rtrim(rtrim(number_format($fps, 6, '.', ''), '0'), '.'); // "6.923077"

        // Escala: sube a 1600 o 1920 si tu video lo aguanta
        $width = 1920; // o 1920

        $job = (new Job())
            ->addTask(new Task('import/upload', 'import-1'))
            ->addTask(
                (new Task('command', 'extract-frames'))
                    ->set('input', 'import-1')
                    ->set('engine', 'ffmpeg')
                    ->set('command', 'ffmpeg')
                    ->set(
                        'arguments',
                        '-i /input/import-1/input.mp4 ' .
                            '-vf "fps=' . $fpsStr . ',scale=' . $width . ':-1:flags=lanczos" ' .
                            '-frames:v ' . $desiredFrames . ' ' .
                            '-q:v 2 ' . // 1=mejor, 2=excelente, 3-5=normal
                            '/output/frame-%03d.jpg'
                    )
                    ->set('capture_output', true)
            )
            ->addTask(
                (new Task('archive', 'archive'))
                    ->set('input', 'extract-frames')
                    ->set('output_format', 'zip')
            )
            ->addTask(
                (new Task('export/url', 'export-1'))
                    ->set('input', 'archive')
            );

        return $cloudconvert->jobs()->create($job);
    }

    /**
     * Sube el video al import task.
     * OJO: subimos siempre como "input.mp4" para que el comando sea estable.
     */
    public function uploadToImportTask(Task $task, string $disk, string $videoPath): void
    {
        $cloudconvert = $this->client();

        $stream = Storage::disk($disk)->readStream($videoPath);
        if (!$stream) {
            throw new \RuntimeException('No se pudo abrir stream del video: ' . $videoPath);
        }

        // filename fijo dentro del import task
        $cloudconvert->tasks()->upload($task, $stream, 'input.mp4');

        if (is_resource($stream)) {
            fclose($stream);
        }
    }
}
