<?php

namespace App\Services;

use App\Appointment;
use App\Lead;
use App\Properties;
use App\User;
use App\Models\CompanyWhatsappBot;
use App\Models\TestDriveProposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Pruebas de manejo. El bot crea la cita como TENTATIVA (status scheduled) y deja
 * una propuesta ligada. Un asesor la revisa en el panel y, al confirmar, la cita
 * pasa a CONFIRMED (solo cambia el estado; no se crea nada nuevo, así que nunca
 * choca ni se duplica). Descartarla la cancela.
 */
class TestDriveScheduler
{
    const DEFAULT_DURATION_MIN = 45;

    public function __construct(private CompanyWhatsappBot $bot)
    {
    }

    /**
     * El bot agenda la prueba de forma tentativa (pendiente de confirmar).
     *
     * @return array{ok: bool, proposal_id?: int, when: ?string, error: ?string, needs_human: bool}
     */
    public function propose(array $input, string $phone, ?string $contactName, ?int $chatId = null): array
    {
        $vehicle = Properties::query()->vehicles()
            ->whereHas('category', fn($q) => $q->where('company_id', $this->bot->company_id))
            ->find((int) ($input['vehicle_id'] ?? 0));

        if (!$vehicle) {
            return $this->fail('No encontré el vehículo para agendar la prueba.');
        }
        if (in_array($vehicle->status, ['sold', 'rented', 'inactive'], true)) {
            return $this->fail('Ese vehículo ya no está disponible para prueba de manejo.');
        }

        $when = $this->parseWhen($input['preferred_datetime'] ?? null);
        if (!$when) {
            return $this->fail('Necesito una fecha y hora válidas (a futuro) para la prueba. Pedísela al cliente.');
        }
        if (!$this->withinBusinessHours($when)) {
            return $this->fail('Ese horario está fuera del horario de atención; ofrecé otro horario.');
        }

        $lead  = WhatsAppLeadService::captureInbound($this->bot->company_id, $phone, $contactName);
        $agent = ($lead ? User::find($lead->user_id) : null) ?: LeadAssignmentService::pickAgent($this->bot->company_id);
        if (!$agent) {
            return ['ok' => false, 'when' => null, 'needs_human' => true,
                'error' => 'No hay un asesor asignable; paso el chat a una persona.'];
        }

        $duration   = max(15, min(180, (int) ($input['duration_minutes'] ?? self::DEFAULT_DURATION_MIN)));
        $endsAt     = (clone $when)->addMinutes($duration);
        $clientName = ($lead->name ?? null) ?: ($input['client_name'] ?? $contactName) ?: 'Cliente WhatsApp';

        if ($this->hasConflict($vehicle->id, $agent->id, $when, $endsAt)) {
            return $this->fail('Ya hay una prueba en ese horario; ofrecé otro.');
        }

        try {
            $appointment = Appointment::create([
                'company_id'       => $this->bot->company_id,
                'user_id'          => $agent->id,
                'lead_id'          => $lead->id ?? null,
                'vehicle_id'       => $vehicle->id,
                'title'            => 'Prueba de manejo — ' . trim(($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '')),
                'description'      => 'Solicitada por el bot de WhatsApp (tentativa).' . (!empty($input['notes']) ? ' Notas: ' . $input['notes'] : ''),
                'type'             => Appointment::TYPE_VEHICLE_VISIT,
                'starts_at'        => $when,
                'ends_at'          => $endsAt,
                'location'         => $vehicle->location,
                'client_name'      => $clientName,
                'client_phone'     => $phone,
                'client_email'     => $input['client_email'] ?? null,
                'status'           => Appointment::STATUS_SCHEDULED, // tentativa
                'reminder_minutes' => 60,
            ]);
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('Error al agendar prueba', ['message' => $e->getMessage()]);
            return $this->fail('No pude registrar la cita; paso el chat a una persona.', true);
        }

        $proposal = TestDriveProposal::available()
            ? TestDriveProposal::create([
                'company_id'       => $this->bot->company_id,
                'chat_id'          => $chatId,
                'lead_id'          => $lead->id ?? null,
                'phone'            => $phone,
                'vehicle_id'       => $vehicle->id,
                'client_name'      => $clientName,
                'client_email'     => $input['client_email'] ?? null,
                'proposed_at'      => $when,
                'duration_minutes' => $duration,
                'notes'            => $input['notes'] ?? null,
                'status'           => TestDriveProposal::STATUS_PENDING,
                'appointment_id'   => $appointment->id,
            ])
            : null;

        if ($lead) {
            try {
                $lead->logActivity('note', [
                    'subject'     => 'Prueba de manejo solicitada',
                    'description' => 'Vía WhatsApp para el ' . $when->format('d/m/Y H:i') . '. Tentativa, pendiente de confirmar.',
                    'vehicle_id'  => $vehicle->id,
                ]);
            } catch (\Throwable $e) {
                Log::channel('whatsapp')->warning('No se pudo registrar actividad de propuesta', ['error' => $e->getMessage()]);
            }
        }

        Log::channel('whatsapp')->info('Prueba de manejo tentativa creada', [
            'company_id'     => $this->bot->company_id,
            'appointment_id' => $appointment->id,
            'proposal_id'    => optional($proposal)->id,
            'when'           => $when->toDateTimeString(),
        ]);

        return [
            'ok'          => true,
            'proposal_id' => optional($proposal)->id,
            'when'        => $when->translatedFormat('l d/m/Y \a \l\a\s H:i'),
            'error'       => null,
            'needs_human' => false,
        ];
    }

    /**
     * Un asesor confirma la propuesta: la cita ligada pasa a CONFIRMED. No crea
     * nada nuevo (salvo propuestas viejas sin cita ligada), por lo que no choca.
     *
     * @return array{ok: bool, appointment_id?: int, error: ?string}
     */
    public static function confirmProposal(TestDriveProposal $proposal, Carbon $when, int $durationMinutes): array
    {
        if ($proposal->status !== TestDriveProposal::STATUS_PENDING) {
            return ['ok' => false, 'error' => 'La propuesta ya fue procesada.'];
        }

        $durationMinutes = max(15, min(180, $durationMinutes));
        $endsAt = (clone $when)->addMinutes($durationMinutes);
        $appointment = $proposal->appointment;

        try {
            if ($appointment) {
                // Solo cambia el estado (y ajusta hora si el asesor la modificó).
                $appointment->update([
                    'status'    => Appointment::STATUS_CONFIRMED,
                    'starts_at' => $when,
                    'ends_at'   => $endsAt,
                ]);
            } else {
                // Propuesta vieja sin cita ligada: la creamos ya confirmada.
                $lead  = $proposal->lead;
                $agent = ($lead ? User::find($lead->user_id) : null) ?: LeadAssignmentService::pickAgent($proposal->company_id);
                if (!$agent) {
                    return ['ok' => false, 'error' => 'No hay un asesor asignable en la empresa.'];
                }
                $appointment = Appointment::create([
                    'company_id'       => $proposal->company_id,
                    'user_id'          => $agent->id,
                    'lead_id'          => $proposal->lead_id,
                    'vehicle_id'       => $proposal->vehicle_id,
                    'title'            => 'Prueba de manejo — ' . $proposal->vehicleTitle(),
                    'description'      => 'Confirmada desde WhatsApp.' . ($proposal->notes ? ' Notas: ' . $proposal->notes : ''),
                    'type'             => Appointment::TYPE_VEHICLE_VISIT,
                    'starts_at'        => $when,
                    'ends_at'          => $endsAt,
                    'location'         => optional($proposal->vehicle)->location,
                    'client_name'      => $proposal->client_name,
                    'client_phone'     => $proposal->phone,
                    'client_email'     => $proposal->client_email,
                    'status'           => Appointment::STATUS_CONFIRMED,
                    'reminder_minutes' => 60,
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('Error al confirmar prueba', ['proposal_id' => $proposal->id, 'message' => $e->getMessage()]);
            return ['ok' => false, 'error' => 'No pude confirmar la cita: ' . $e->getMessage()];
        }

        $proposal->update([
            'status'           => TestDriveProposal::STATUS_CONFIRMED,
            'appointment_id'   => $appointment->id,
            'proposed_at'      => $when,
            'duration_minutes' => $durationMinutes,
        ]);

        $lead = $proposal->lead;
        if ($lead) {
            try {
                $lead->logActivity('meeting', [
                    'subject'     => 'Prueba de manejo confirmada',
                    'description' => 'Cita para el ' . $when->format('d/m/Y H:i') . '.',
                    'vehicle_id'  => $proposal->vehicle_id,
                ]);
                if (in_array($lead->status, [Lead::STATUS_NEW, Lead::STATUS_CONTACTED], true)) {
                    $lead->changeStatus(Lead::STATUS_QUALIFIED, 'Prueba de manejo confirmada.');
                }
            } catch (\Throwable $e) {
                Log::channel('whatsapp')->warning('No se pudo registrar actividad de confirmación', ['error' => $e->getMessage()]);
            }
        }

        return ['ok' => true, 'appointment_id' => $appointment->id, 'error' => null];
    }

    /**
     * Descartar: cancela la cita tentativa ligada (si existe).
     */
    public static function dismissProposal(TestDriveProposal $proposal): void
    {
        if ($proposal->appointment && $proposal->appointment->status === Appointment::STATUS_SCHEDULED) {
            $proposal->appointment->update(['status' => Appointment::STATUS_CANCELLED, 'cancellation_reason' => 'Propuesta descartada por el asesor']);
        }
        $proposal->update(['status' => TestDriveProposal::STATUS_DISMISSED]);
    }

    private function parseWhen($raw): ?Carbon
    {
        if (empty($raw) || !is_string($raw)) {
            return null;
        }
        try {
            $when = Carbon::parse($raw);
            return $when->isPast() ? null : $when;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function withinBusinessHours(Carbon $when): bool
    {
        $start = $this->bot->business_hours_start;
        $end   = $this->bot->business_hours_end;
        if (!$start || !$end) {
            return true;
        }
        $t = substr($when->format('H:i'), 0, 5);
        return $t >= substr($start, 0, 5) && $t <= substr($end, 0, 5);
    }

    private function hasConflict(int $vehicleId, int $agentId, Carbon $start, Carbon $end): bool
    {
        return Appointment::byCompany($this->bot->company_id)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW])
            ->where(function ($q) use ($vehicleId, $agentId) {
                $q->where('vehicle_id', $vehicleId)->orWhere('user_id', $agentId);
            })
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();
    }

    private function fail(string $error, bool $needsHuman = false): array
    {
        return ['ok' => false, 'when' => null, 'error' => $error, 'needs_human' => $needsHuman];
    }
}
