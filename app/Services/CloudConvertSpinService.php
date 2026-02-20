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

        $job = (new Job())
            ->addTask(new Task('import/upload', 'import-1'))
            ->addTask(
                (new Task('command', 'extract-frames'))
                    ->set('input', 'import-1')
                    ->set('engine', 'ffmpeg')
                    ->set('command', 'ffmpeg')
                    ->set(
                        'arguments',
                        // input fijo: /input/import-1/input.mp4
                        // output: /output/frame-001.jpg, frame-002.jpg, ...
                        '-i /input/import-1/input.mp4 -vf "fps=' . (int)$fps . ',scale=1200:-1" /output/frame-%03d.' . $imgExt
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