<?php

namespace App\Services;

use App\Appointment;
use App\Lead;
use App\Properties;
use App\User;
use App\Models\CompanyWhatsappBot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Agenda pruebas de manejo desde el bot: crea una cita (tentativa) en la agenda
 * existente del negocio, evitando choques de horario. Regla del proyecto: el bot
 * agenda pero NO confirma en firme; un asesor confirma la cita.
 */
class TestDriveScheduler
{
    const DEFAULT_DURATION_MIN = 45;

    public function __construct(private CompanyWhatsappBot $bot)
    {
    }

    /**
     * @return array{ok: bool, appointment_id: ?int, when: ?string, error: ?string, needs_human: bool}
     */
    public function schedule(array $input, string $phone, ?string $contactName): array
    {
        $vehicleId = (int) ($input['vehicle_id'] ?? 0);
        $vehicle = Properties::query()->vehicles()
            ->whereHas('category', fn($q) => $q->where('company_id', $this->bot->company_id))
            ->find($vehicleId);

        if (!$vehicle) {
            return $this->fail('No encontré el vehículo para agendar la prueba.');
        }
        if (in_array($vehicle->status, ['sold', 'rented', 'inactive'], true)) {
            return $this->fail('Ese vehículo ya no está disponible para prueba de manejo.');
        }

        $when = $this->parseWhen($input['preferred_datetime'] ?? null);
        if (!$when) {
            return $this->fail('Necesito una fecha y hora válidas (a futuro) para la prueba.');
        }
        if ($when->isPast()) {
            return $this->fail('Esa fecha ya pasó; proponé una fecha y hora a futuro.');
        }

        if (!$this->withinBusinessHours($when)) {
            return $this->fail('Ese horario está fuera del horario de atención; ofrecé otro horario.');
        }

        $duration = max(15, min(180, (int) ($input['duration_minutes'] ?? self::DEFAULT_DURATION_MIN)));
        $endsAt = (clone $when)->addMinutes($duration);

        // Asegura el lead (ya suele existir por la captura de entrada) y usa su
        // asesor dueño para que cita y lead queden con el mismo responsable.
        $lead  = WhatsAppLeadService::captureInbound($this->bot->company_id, $phone, $contactName);
        $agent = ($lead ? User::find($lead->user_id) : null) ?: LeadAssignmentService::pickAgent($this->bot->company_id);
        if (!$agent) {
            return ['ok' => false, 'appointment_id' => null, 'when' => null, 'needs_human' => true,
                'error' => 'No hay un asesor asignable; paso el chat a una persona.'];
        }

        if ($this->hasConflict($vehicle->id, $agent->id, $when, $endsAt)) {
            return $this->fail('Ya hay una prueba agendada en ese horario; proponé otro.');
        }

        $clientName = ($lead->name ?? null) ?: ($input['client_name'] ?? $contactName) ?: 'Cliente WhatsApp';

        try {
            $appointment = Appointment::create([
                'company_id'       => $this->bot->company_id,
                'user_id'          => $agent->id,
                'lead_id'          => $lead->id ?? null,
                'vehicle_id'       => $vehicle->id,
                'title'            => 'Prueba de manejo — ' . trim(($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '')),
                'description'      => 'Solicitada por el bot de WhatsApp.' . (!empty($input['notes']) ? ' Notas: ' . $input['notes'] : ''),
                'type'             => Appointment::TYPE_VEHICLE_VISIT,
                'starts_at'        => $when,
                'ends_at'          => $endsAt,
                'location'         => $vehicle->location,
                'client_name'      => $clientName,
                'client_phone'     => $phone,
                'client_email'     => $input['client_email'] ?? null,
                'status'           => Appointment::STATUS_SCHEDULED,
                'reminder_minutes' => 60,
            ]);
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('Error al agendar prueba', ['message' => $e->getMessage()]);
            return $this->fail('No pude registrar la cita en la agenda; paso el chat a una persona.', true);
        }

        // Registra la actividad en el CRM y avanza la etapa (intención fuerte).
        if ($lead) {
            $lead->logActivity('meeting', [
                'subject'     => 'Prueba de manejo agendada',
                'description' => 'Vía WhatsApp para el ' . $when->format('d/m/Y H:i') . '.',
                'vehicle_id'  => $vehicle->id,
            ]);
            if (in_array($lead->status, [Lead::STATUS_NEW, Lead::STATUS_CONTACTED], true)) {
                $lead->changeStatus(Lead::STATUS_QUALIFIED, 'Agendó prueba de manejo desde WhatsApp.');
            }
        }

        Log::channel('whatsapp')->info('Prueba de manejo agendada', [
            'company_id'     => $this->bot->company_id,
            'appointment_id' => $appointment->id,
            'vehicle_id'     => $vehicle->id,
            'when'           => $when->toDateTimeString(),
        ]);

        return [
            'ok'             => true,
            'appointment_id' => $appointment->id,
            'when'           => $when->translatedFormat('l d/m/Y \a \l\a\s H:i'),
            'error'          => null,
            'needs_human'    => false,
        ];
    }

    private function parseWhen($raw): ?Carbon
    {
        if (empty($raw) || !is_string($raw)) {
            return null;
        }
        try {
            return Carbon::parse($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function withinBusinessHours(Carbon $when): bool
    {
        $start = $this->bot->business_hours_start;
        $end   = $this->bot->business_hours_end;
        if (!$start || !$end) {
            return true; // sin horario configurado, no se restringe
        }
        $t = $when->format('H:i');
        // Formatos guardados como "HH:MM" o "HH:MM:SS".
        return substr($t, 0, 5) >= substr($start, 0, 5) && substr($t, 0, 5) <= substr($end, 0, 5);
    }

    private function hasConflict(int $vehicleId, int $agentId, Carbon $start, Carbon $end): bool
    {
        return Appointment::byCompany($this->bot->company_id)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW])
            ->where(fn($q) => $q->where('vehicle_id', $vehicleId)->orWhere('user_id', $agentId))
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();
    }

    private function fail(string $error, bool $needsHuman = false): array
    {
        return ['ok' => false, 'appointment_id' => null, 'when' => null, 'error' => $error, 'needs_human' => $needsHuman];
    }
}
