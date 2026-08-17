<?php

namespace App\Services;

use App\Lead;
use App\User;

/**
 * Reparte leads entrantes entre los asesores de la empresa. Estrategia: el menos
 * cargado (menos leads abiertos) — un round-robin justo y sin estado, ideal para
 * hosting compartido (no necesita tabla de "último asignado").
 */
class LeadAssignmentService
{
    /**
     * Elige el asesor que debe recibir el próximo lead, o null si no hay usuarios.
     */
    public static function pickAgent(int $companyId): ?User
    {
        $agents = static::eligibleAgents($companyId);
        if ($agents->isEmpty()) {
            // Sin asesores/administradores activos: cualquier usuario de la empresa.
            return User::where('company_id', $companyId)->orderBy('id')->first();
        }

        // Conteo de leads abiertos (no ganados/perdidos) por asesor.
        $counts = Lead::byCompany($companyId)
            ->active()
            ->whereIn('user_id', $agents->pluck('id'))
            ->selectRaw('user_id, COUNT(*) as c')
            ->groupBy('user_id')
            ->pluck('c', 'user_id');

        // Menos cargado primero; a igualdad, menor id (round-robin estable).
        return $agents->sortBy(fn($a) => [(int) ($counts[$a->id] ?? 0), $a->id])->first();
    }

    /**
     * Asesores elegibles: agentes activos; si no hay, admins de empresa activos.
     */
    private static function eligibleAgents(int $companyId)
    {
        $agents = User::where('company_id', $companyId)
            ->where('status', User::STATUS_ACTIVE)
            ->where('role', User::ROLE_AGENT)
            ->orderBy('id')
            ->get();

        if ($agents->isNotEmpty()) {
            return $agents;
        }

        return User::where('company_id', $companyId)
            ->where('status', User::STATUS_ACTIVE)
            ->where('role', User::ROLE_COMPANY_ADMIN)
            ->orderBy('id')
            ->get();
    }
}
