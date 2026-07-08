<?php

namespace App\Services;

use App\Lead;
use App\Models\EventLead;
use App\Models\VehicleQuote;

/**
 * Centraliza la lógica de promoción de capturas de evento (EventLead / VehicleQuote)
 * al CRM robusto (App\Lead). Reutilizado por:
 *   - Acciones manuales del Dashboard de eventos y la vista de Eventos.
 *   - Migración masiva (bulk) desde la vista de Eventos.
 *   - Auto-guardado del kiosko cuando la empresa tiene kiosk_auto_crm activo.
 */
class LeadCrmService
{
    /**
     * Buscar un lead existente por teléfono o email (deduplicación).
     */
    public function findExistingLead(?string $phone, ?string $email): ?Lead
    {
        if (empty($phone) && empty($email)) {
            return null;
        }

        return Lead::where(function ($query) use ($phone, $email) {
            if (!empty($phone)) {
                $query->where('phone', $phone);
            }
            if (!empty($email)) {
                $query->orWhere('email', $email);
            }
        })->first();
    }

    /**
     * Promueve un EventLead ("Me interesa") al CRM.
     *
     * @return array{created: bool, lead: ?Lead, message: string}
     */
    public function migrateEventLead(EventLead $eventLead, ?int $userId = null, ?int $companyId = null): array
    {
        $userId    = $userId ?? auth()->id();
        $companyId = $companyId ?? $eventLead->company_id;

        $existing = $this->findExistingLead($eventLead->phone, $eventLead->email);
        if ($existing) {
            return [
                'created' => false,
                'lead'    => $existing,
                'message' => 'Ya existe un lead con este teléfono o email en el CRM',
            ];
        }

        $priorityMap = ['hot' => 'urgent', 'high' => 'high', 'medium' => 'medium', 'low' => 'low'];
        $sourceMap   = ['event' => 'event', 'kiosk' => 'kiosk', 'qr' => 'qr', 'compare' => 'event', 'quote' => 'quote'];

        $lead = Lead::create([
            'company_id'       => $companyId,
            'user_id'          => $userId,
            'property_id'      => $eventLead->property_id,
            'name'             => $eventLead->name,
            'email'            => $eventLead->email,
            'phone'            => $eventLead->phone,
            'status'           => 'new',
            'source'           => $sourceMap[$eventLead->source] ?? 'event',
            'priority'         => $priorityMap[$eventLead->interest_level] ?? 'medium',
            'interest_type'    => 'buy',
            'notes'            => "Lead capturado en evento: {$eventLead->event_name}\n" .
                "Origen: {$eventLead->source}\n" .
                "Nivel de interés: {$eventLead->interest_level}\n" .
                ($eventLead->notes ? "Notas: {$eventLead->notes}" : ''),
            'first_contact_at' => $eventLead->created_at,
            'event_name'       => $eventLead->event_name,
            'event_lead_id'    => $eventLead->id,
        ]);

        $lead->logActivity('note', [
            'subject'     => 'Lead importado desde evento',
            'description' => "Importado desde el evento '{$eventLead->event_name}'. " .
                ($eventLead->property ? "Interesado en: {$eventLead->property->brand} {$eventLead->property->model}" : ''),
        ]);

        $eventLead->update([
            'follow_up_status' => 'completed',
            'notes'            => ($eventLead->notes ? $eventLead->notes . "\n" : '') . "Migrado a CRM Lead #{$lead->id}",
        ]);

        return [
            'created' => true,
            'lead'    => $lead,
            'message' => 'Lead agregado al CRM exitosamente',
        ];
    }

    /**
     * Promueve una VehicleQuote ("Cotización") al CRM.
     *
     * @return array{created: bool, lead: ?Lead, message: string}
     */
    public function migrateQuote(VehicleQuote $quote, ?int $userId = null, ?int $companyId = null): array
    {
        $userId    = $userId ?? auth()->id();
        $companyId = $companyId ?? $quote->company_id;

        if (!$quote->customer_name && !$quote->customer_phone) {
            return [
                'created' => false,
                'lead'    => null,
                'message' => 'La cotización no tiene datos del cliente',
            ];
        }

        if ($quote->customer_phone) {
            $existing = Lead::where('phone', $quote->customer_phone)->first();
            if ($existing) {
                return [
                    'created' => false,
                    'lead'    => $existing,
                    'message' => 'Ya existe un lead con este teléfono en el CRM',
                ];
            }
        }

        $lead = Lead::create([
            'company_id'       => $companyId,
            'user_id'          => $userId,
            'property_id'      => $quote->property_id,
            'name'             => $quote->customer_name ?? 'Sin nombre',
            'email'            => $quote->customer_email,
            'phone'            => $quote->customer_phone,
            'status'           => 'new',
            'source'           => 'quote',
            'priority'         => 'high',
            'interest_type'    => 'buy',
            'budget_min'       => $quote->vehicle_price * 0.8,
            'budget_max'       => $quote->vehicle_price * 1.2,
            'budget_currency'  => $quote->currency,
            'notes'            => "Lead creado desde cotización del evento: {$quote->event_name}\n" .
                "Cotización: Cuota mensual ₡" . number_format($quote->monthly_payment) . "\n" .
                "Prima: " . $quote->down_payment_percent . "% - Plazo: {$quote->term_months} meses\n" .
                "Tasa: {$quote->interest_rate}%",
            'first_contact_at' => $quote->created_at,
            'event_name'       => $quote->event_name,
        ]);

        $lead->logActivity('note', [
            'subject'     => 'Lead creado desde cotización',
            'description' => "Cliente solicitó cotización en evento. " .
                ($quote->property ? "Vehículo: {$quote->property->brand} {$quote->property->model}" : '') .
                " - Cuota estimada: ₡" . number_format($quote->monthly_payment),
        ]);

        return [
            'created' => true,
            'lead'    => $lead,
            'message' => 'Lead creado en el CRM exitosamente',
        ];
    }
}
