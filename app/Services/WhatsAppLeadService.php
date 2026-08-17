<?php

namespace App\Services;

use App\Lead;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Puente WhatsApp → CRM: convierte cada conversación entrante en un lead
 * (creándolo o actualizándolo) para que nada se pierda. Independiente del bot:
 * captura el lead aunque el bot esté apagado o pausado.
 */
class WhatsAppLeadService
{
    /**
     * Captura un lead a partir de un mensaje entrante. Devuelve el lead o null
     * si no se pudo (sin tabla o sin asesor asignable).
     */
    public static function captureInbound(int $companyId, string $phone, ?string $contactName): ?Lead
    {
        if (!Schema::hasTable('leads')) {
            return null;
        }

        $existing = static::findByPhone($companyId, $phone);
        if ($existing) {
            $updates = ['last_contact_at' => now()];
            if (empty($existing->name) && $contactName) {
                $updates['name'] = $contactName;
            }
            if (empty($existing->whatsapp)) {
                $updates['whatsapp'] = $phone;
            }
            $existing->update($updates);
            return $existing;
        }

        $agent = static::defaultAgent($companyId);
        if (!$agent) {
            Log::channel('whatsapp')->warning('No hay asesor asignable para el lead de WhatsApp', ['company_id' => $companyId]);
            return null;
        }

        $lead = Lead::create([
            'company_id'       => $companyId,
            'user_id'          => $agent->id,
            'name'             => $contactName ?: ('WhatsApp ' . substr($phone, -4)),
            'phone'            => $phone,
            'whatsapp'         => $phone,
            'status'           => Lead::STATUS_NEW,
            'source'           => Lead::SOURCE_WHATSAPP,
            'first_contact_at' => now(),
            'last_contact_at'  => now(),
        ]);

        $lead->logActivity('whatsapp', [
            'subject'     => 'Primer contacto por WhatsApp',
            'description' => 'Lead generado automáticamente desde el bot de WhatsApp.',
        ]);

        // Aviso al asesor (no hay usuario "actuando": el bot es automático).
        CrmNotifier::leadAssigned($lead);

        Log::channel('whatsapp')->info('Lead creado desde WhatsApp', [
            'company_id' => $companyId, 'lead_id' => $lead->id, 'phone' => $phone,
        ]);

        return $lead;
    }

    /**
     * Busca un lead por teléfono dentro de la empresa (tolerando prefijos país).
     */
    public static function findByPhone(int $companyId, string $phone): ?Lead
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') {
            return null;
        }
        $tail = substr($digits, -8);
        return Lead::byCompany($companyId)
            ->where(fn($q) => $q->where('phone', 'like', "%{$tail}")->orWhere('whatsapp', 'like', "%{$tail}"))
            ->latest('id')
            ->first();
    }

    /**
     * Asesor por defecto para asignar (company_admin y, si no, el de menor id).
     */
    public static function defaultAgent(int $companyId): ?User
    {
        return User::where('company_id', $companyId)
            ->orderByRaw("FIELD(role, 'company_admin') DESC")
            ->orderBy('id')
            ->first();
    }
}
