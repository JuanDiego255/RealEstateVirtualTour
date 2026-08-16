<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Configuración operativa del bot de una empresa (la administra el negocio):
 * tono aprendido, reglas propias, cómo se cierra una compra y política de relevo.
 */
class WhatsappBotSetting extends Model
{
    protected $fillable = [
        'company_id', 'store_name', 'notify_email',
        'training_profile', 'custom_rules', 'order_instructions', 'handoff',
    ];

    protected $casts = [
        'handoff' => 'array',
    ];

    /**
     * Disparadores de relevo por defecto (cuándo entra una persona).
     */
    public const DEFAULT_HANDOFF = [
        'asks_for_human'   => true,
        'complaint'        => true,
        'asks_past_order'  => true,
        'sends_receipt'    => true,
        'sends_voice_note' => true,
        'not_found'        => false,
        'keywords'         => 'reclamo, factura, devolución, pedido mal',
        'max_messages'     => 0,
        'resume_after_h'   => 2,
        'handoff_message'  => 'En un momento te atenderá una persona del equipo, gracias por tu paciencia.',
    ];

    /**
     * Lee la política de relevo aceptando array o JSON crudo (filas viejas),
     * con fallback a los valores por defecto.
     */
    public function handoffConfig(): array
    {
        $raw = $this->handoff;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($raw)) {
            $raw = [];
        }
        return array_merge(self::DEFAULT_HANDOFF, $raw);
    }
}
