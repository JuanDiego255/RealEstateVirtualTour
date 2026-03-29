<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinHotspot extends Model
{
    protected $fillable = [
        'spin_id', 'title', 'description', 'icon', 'frame_number',
        'frame_range_start', 'frame_range_end', 'position_x', 'position_y',
        'image', 'video', 'audio', 'specs', 'status', 'order'
    ];

    protected $casts = [
        'specs' => 'array',
        'status' => 'boolean',
        'position_x' => 'decimal:2',
        'position_y' => 'decimal:2',
    ];

    public function spin()
    {
        return $this->belongsTo(Spin::class);
    }

    /**
     * Verificar si el hotspot es visible en un frame específico
     */
    public function isVisibleAtFrame($frameNumber)
    {
        if ($this->frame_range_start && $this->frame_range_end) {
            return $frameNumber >= $this->frame_range_start
                && $frameNumber <= $this->frame_range_end;
        }

        // Si no hay rango, visible en el frame exacto +/- 3
        return abs($frameNumber - $this->frame_number) <= 3;
    }

    /**
     * Obtener hotspots activos para un spin
     */
    public static function getForSpin($spinId)
    {
        return self::where('spin_id', $spinId)
            ->where('status', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Iconos disponibles
     */
    public static function availableIcons()
    {
        return [
            'info' => 'Información general',
            'engine' => 'Motor',
            'sound' => 'Sistema de sonido',
            'wheel' => 'Llantas/Rines',
            'interior' => 'Interior',
            'safety' => 'Seguridad',
            'tech' => 'Tecnología',
            'comfort' => 'Confort',
            'performance' => 'Rendimiento',
            'fuel' => 'Combustible',
        ];
    }
}
