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
 * Pruebas de manejo. El bot NO crea la cita: deja una PROPUESTA con los datos del
 * cliente. Un asesor la revisa en el panel de chat y, al confirmar, se crea la
 * cita real en la agenda. Así ninguna cita entra sin revisión humana.
 */
class TestDriveScheduler
{
    const DEFAULT_DURATION_MIN = 45;

    public function __construct(private CompanyWhatsappBot $bot)
    {
    }

    /**
     * El bot registra una propuesta de prueba de manejo (pendiente de confirmar).
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
        if (!TestDriveProposal::available()) {
            return $this->fail('No pude registrar la solicitud; paso el chat a una persona.', true);
        }

        // Fecha/hora: best-effort. Si no se entiende, el asesor la completa al confirmar.
        $when = $this->parseWhen($input['preferred_datetime'] ?? null);

        $lead       = WhatsAppLeadService::captureInbound($this->bot->company_id, $phone, $contactName);
        $clientName = ($lead->name ?? null) ?: ($input['client_name'] ?? $contactName) ?: 'Cliente WhatsApp';
        $duration   = max(15, min(180, (int) ($input['duration_minutes'] ?? self::DEFAULT_DURATION_MIN)));

        $proposal = TestDriveProposal::create([
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
        ]);

        if ($lead) {
            $lead->logActivity('note', [
                'subject'     => 'Prueba de manejo solicitada',
                'description' => 'Vía WhatsApp' . ($when ? ' para el ' . $when->format('d/m/Y H:i') : '') . '. Pendiente de confirmar por un asesor.',
                'vehicle_id'  => $vehicle->id,
            ]);
        }

        Log::channel('whatsapp')->info('Propuesta de prueba de manejo creada', [
            'company_id'  => $this->bot->company_id,
            'proposal_id' => $proposal->id,
            'vehicle_id'  => $vehicle->id,
            'when'        => $when ? $when->toDateTimeString() : null,
        ]);

        return [
            'ok'          => true,
            'proposal_id' => $proposal->id,
            'when'        => $when ? $when->translatedFormat('l d/m/Y \a \l\a\s H:i') : null,
            'error'       => null,
            'needs_human' => false,
        ];
    }

    /**
     * Un asesor confirma la propuesta: crea la cita real en la agenda.
     *
     * @return array{ok: bool, appointment_id?: int, error: ?string}
     */
    public static function confirmProposal(TestDriveProposal $proposal, Carbon $when, int $durationMinutes): array
    {
        if ($proposal->status !== TestDriveProposal::STATUS_PENDING) {
            return ['ok' => false, 'error' => 'La propuesta ya fue procesada.'];
        }

        $vehicle = $proposal->vehicle;
        $lead    = $proposal->lead;
        $agent   = ($lead ? User::find($lead->user_id) : null) ?: LeadAssignmentService::pickAgent($proposal->company_id);
        if (!$agent) {
            return ['ok' => false, 'error' => 'No hay un asesor asignable en la empresa.'];
        }

        $durationMinutes = max(15, min(180, $durationMinutes));
        $endsAt = (clone $when)->addMinutes($durationMinutes);

        $conflict = Appointment::byCompany($proposal->company_id)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW])
            ->where(function ($q) use ($proposal, $agent) {
                $q->where('vehicle_id', $proposal->vehicle_id)->orWhere('user_id', $agent->id);
            })
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $when)
            ->exists();
        if ($conflict) {
            return ['ok' => false, 'error' => 'Ya hay una cita en ese horario (mismo vehículo o asesor).'];
        }

        try {
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
                'location'         => optional($vehicle)->location,
                'client_name'      => $proposal->client_name,
                'client_phone'     => $proposal->phone,
                'client_email'     => $proposal->client_email,
                'status'           => Appointment::STATUS_SCHEDULED,
                'reminder_minutes' => 60,
            ]);
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->error('Error al confirmar prueba', ['proposal_id' => $proposal->id, 'message' => $e->getMessage()]);
            return ['ok' => false, 'error' => 'No pude crear la cita: ' . $e->getMessage()];
        }

        $proposal->update([
            'status'           => TestDriveProposal::STATUS_CONFIRMED,
            'appointment_id'   => $appointment->id,
            'proposed_at'      => $when,
            'duration_minutes' => $durationMinutes,
        ]);

        if ($lead) {
            $lead->logActivity('meeting', [
                'subject'     => 'Prueba de manejo confirmada',
                'description' => 'Cita para el ' . $when->format('d/m/Y H:i') . '.',
                'vehicle_id'  => $proposal->vehicle_id,
            ]);
            if (in_array($lead->status, [Lead::STATUS_NEW, Lead::STATUS_CONTACTED], true)) {
                $lead->changeStatus(Lead::STATUS_QUALIFIED, 'Prueba de manejo confirmada.');
            }
        }

        return ['ok' => true, 'appointment_id' => $appointment->id, 'error' => null];
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

    private function fail(string $error, bool $needsHuman = false): array
    {
        return ['ok' => false, 'when' => null, 'error' => $error, 'needs_human' => $needsHuman];
    }
}
