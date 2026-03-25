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
     * Crea job de CloudConvert para extraer frames de spin.
     *
     * Job pipeline:
     * 1) import/upload
     * 2) command (ffmpeg) -> extrae frames a /output/frame-XXX.webp
     * 3) archive -> zip de todos los frames
     * 4) export/url -> URL del zip
     *
     * @param float $fps FPS calculado según duración del video
     * @param string $imgExt Extensión de imagen (no usado, siempre webp)
     * @param int $width Ancho de los frames (defecto 1280 para buena calidad en pantalla completa)
     * @param int $quality Calidad WebP 0-100 (defecto 70)
     */
    public function createSpinJob(float $fps = 2.0, string $imgExt = 'jpg', int $width = 1280, int $quality = 70): Job
    {
        $cloudconvert = $this->client();
        $desiredFrames = 72;

        // fps float calculado: $fps = $desiredFrames / $duration;
        $fpsStr = rtrim(rtrim(number_format($fps, 6, '.', ''), '0'), '.'); // ej "2.769231"

        // compression_level 5 para mejor balance tamaño/velocidad
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
                            '-c:v libwebp -quality ' . $quality . ' -compression_level 5 -preset picture ' .
                            '/output/frame-%03d.webp'
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
