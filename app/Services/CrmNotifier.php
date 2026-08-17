<?php

namespace App\Services;

use App\Lead;
use App\User;
use App\Notifications\LeadAssignedNotification;
use Illuminate\Support\Facades\Log;

/**
 * Punto único para disparar avisos del CRM a los asesores, de forma tolerante a
 * fallos (un error de correo nunca debe tumbar el flujo que lo originó).
 */
class CrmNotifier
{
    /**
     * Avisa al asesor dueño del lead que se le asignó, salvo que el propio asesor
     * sea quien hizo la acción (no tiene sentido avisarse a sí mismo).
     */
    public static function leadAssigned(Lead $lead, ?int $actingUserId = null): void
    {
        $agentId = $lead->user_id;
        if (!$agentId || $agentId === $actingUserId) {
            return;
        }

        try {
            $agent = User::find($agentId);
            $agent?->notify(new LeadAssignedNotification($lead));
        } catch (\Throwable $e) {
            Log::error('No se pudo notificar la asignación de lead', [
                'lead_id' => $lead->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
