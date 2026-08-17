<?php

namespace App\Services;

use App\Lead;
use App\LeadTask;
use App\Models\CompanyWhatsappBot;
use App\Models\FollowUpEnrollment;
use App\Models\FollowUpSequence;
use App\Models\FollowUpStep;
use App\Models\WhatsappConversation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Motor de secuencias de seguimiento (nurturing). Inscribe leads, procesa los
 * pasos vencidos y envía por email o WhatsApp. Respeta la política de WhatsApp:
 * fuera de la ventana de 24 h no manda texto libre; deja una tarea al asesor.
 */
class FollowUpService
{
    /**
     * Inscribe un lead recién creado en las secuencias automáticas de su empresa.
     */
    public static function enrollNewLead(Lead $lead): void
    {
        if (!FollowUpSequence::available()) {
            return;
        }

        $sequences = FollowUpSequence::forCompany($lead->company_id)
            ->active()
            ->where('trigger', FollowUpSequence::TRIGGER_LEAD_CREATED)
            ->with('steps')
            ->get();

        foreach ($sequences as $sequence) {
            static::enroll($sequence, $lead);
        }
    }

    /**
     * Crea la inscripción (si no existe) y agenda el primer paso.
     */
    public static function enroll(FollowUpSequence $sequence, Lead $lead): ?FollowUpEnrollment
    {
        $firstStep = $sequence->steps->sortBy('position')->first();
        if (!$firstStep) {
            return null; // secuencia sin pasos
        }

        $existing = FollowUpEnrollment::where('sequence_id', $sequence->id)
            ->where('lead_id', $lead->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        return FollowUpEnrollment::create([
            'sequence_id'      => $sequence->id,
            'lead_id'          => $lead->id,
            'company_id'       => $lead->company_id,
            'current_position' => 0,
            'enrolled_at'      => now(),
            'next_run_at'      => now()->addHours((int) $firstStep->delay_hours),
            'status'           => FollowUpEnrollment::STATUS_ACTIVE,
        ]);
    }

    /**
     * Procesa todas las inscripciones vencidas. Devuelve conteos.
     */
    public static function process(): array
    {
        if (!FollowUpEnrollment::available()) {
            return ['sent' => 0, 'stopped' => 0, 'completed' => 0];
        }

        $sent = $stopped = $completed = 0;

        FollowUpEnrollment::due()
            ->with(['sequence.steps', 'lead.user'])
            ->chunkById(100, function ($chunk) use (&$sent, &$stopped, &$completed) {
                foreach ($chunk as $enrollment) {
                    $outcome = static::processOne($enrollment);
                    if ($outcome === 'sent')           $sent++;
                    elseif ($outcome === 'stopped')    $stopped++;
                    elseif ($outcome === 'completed')  $completed++;
                }
            });

        return ['sent' => $sent, 'stopped' => $stopped, 'completed' => $completed];
    }

    private static function processOne(FollowUpEnrollment $enrollment): string
    {
        $lead = $enrollment->lead;
        $sequence = $enrollment->sequence;

        if (!$lead || !$sequence) {
            $enrollment->update(['status' => FollowUpEnrollment::STATUS_STOPPED, 'stopped_reason' => 'lead_o_secuencia_faltante']);
            return 'stopped';
        }

        // Cortes: lead cerrado o respondió.
        if (in_array($lead->status, [Lead::STATUS_WON, Lead::STATUS_LOST], true)) {
            $enrollment->update(['status' => FollowUpEnrollment::STATUS_STOPPED, 'stopped_reason' => 'lead_cerrado']);
            return 'stopped';
        }
        if ($sequence->stop_on_reply && static::leadReplied($lead, $enrollment->enrolled_at)) {
            $enrollment->update(['status' => FollowUpEnrollment::STATUS_STOPPED, 'stopped_reason' => 'lead_respondio']);
            return 'stopped';
        }

        $steps = $sequence->steps->sortBy('position')->values();
        $step  = $steps->get($enrollment->current_position);

        if (!$step) {
            $enrollment->update(['status' => FollowUpEnrollment::STATUS_COMPLETED]);
            return 'completed';
        }

        try {
            static::sendStep($step, $lead);
        } catch (\Throwable $e) {
            Log::error('Fallo enviando paso de seguimiento', ['enrollment_id' => $enrollment->id, 'error' => $e->getMessage()]);
        }

        // Avanza al siguiente paso o completa.
        $nextIndex = $enrollment->current_position + 1;
        $nextStep  = $steps->get($nextIndex);

        if ($nextStep) {
            $enrollment->update([
                'current_position' => $nextIndex,
                'next_run_at'      => now()->addHours((int) $nextStep->delay_hours),
                'last_sent_at'     => now(),
            ]);
            return 'sent';
        }

        $enrollment->update(['status' => FollowUpEnrollment::STATUS_COMPLETED, 'last_sent_at' => now()]);
        return 'sent';
    }

    /* ── Envío ── */

    private static function sendStep(FollowUpStep $step, Lead $lead): void
    {
        $agent = $lead->user;
        $body  = static::resolveBody($step, $lead);

        if (trim((string) $body) === '') {
            return;
        }

        if ($step->channel === 'email') {
            static::sendEmail($step, $lead, $body);
        } else {
            static::sendWhatsApp($lead, $body);
        }
    }

    private static function resolveBody(FollowUpStep $step, Lead $lead): string
    {
        $agent = $lead->user;
        if ($step->message_template_id && $step->template && $agent) {
            return $step->template->interpolate($lead, $agent, $lead->portal_url ?? null);
        }

        $vars = [
            '{{nombre}}'  => $lead->name,
            '{{name}}'    => $lead->name,
            '{{agente}}'  => optional($agent)->name ?? '',
            '{{agent}}'   => optional($agent)->name ?? '',
            '{{empresa}}' => optional(optional($agent)->company)->name ?? '',
            '{{fecha}}'   => now()->format('d/m/Y'),
            '{{date}}'    => now()->format('d/m/Y'),
        ];
        return str_replace(array_keys($vars), array_values($vars), (string) $step->body);
    }

    private static function sendEmail(FollowUpStep $step, Lead $lead, string $body): void
    {
        if (!$lead->email) {
            return;
        }
        $subject = $step->subject ?: (optional($step->template)->subject ?: 'Seguimiento');
        $mailer  = CompanyMailer::mailerName($lead->company_id) ?: config('mail.default');
        $from    = CompanyMailer::from($lead->company_id);

        Mail::mailer($mailer)->raw($body, function ($m) use ($lead, $subject, $from) {
            $m->to($lead->email)->subject($subject);
            if ($from) {
                $m->from($from[0], $from[1]);
            }
        });

        $lead->logActivity('email', ['subject' => 'Seguimiento automático', 'description' => \Illuminate\Support\Str::limit($body, 200)]);
    }

    private static function sendWhatsApp(Lead $lead, string $body): void
    {
        $phone = $lead->whatsapp ?: $lead->phone;
        if (!$phone) {
            return;
        }

        $bot = CompanyWhatsappBot::where('company_id', $lead->company_id)->first();
        if (!$bot || !$bot->isUsable()) {
            static::manualTask($lead, 'El bot de WhatsApp no está disponible para el envío automático.');
            return;
        }

        // Regla de Meta: fuera de la ventana de 24 h no se puede mandar texto libre.
        if (!static::within24hWindow($lead->company_id, $phone)) {
            static::manualTask($lead, 'Fuera de la ventana de 24 h de WhatsApp: contactá al cliente manualmente.');
            return;
        }

        $result = app(WhatsAppCloudService::class)->sendText($bot, $phone, $body);

        WhatsappConversation::create([
            'company_id'   => $lead->company_id,
            'phone'        => $phone,
            'contact_name' => $lead->name,
            'direction'    => WhatsappConversation::DIRECTION_OUTBOUND,
            'message'      => $body,
            'message_type' => 'text',
            'is_human'     => false,
            'wam_id'       => $result['wam_id'] ?? null,
        ]);

        $lead->logActivity('whatsapp', ['subject' => 'Seguimiento automático', 'description' => \Illuminate\Support\Str::limit($body, 200)]);
    }

    private static function within24hWindow(int $companyId, string $phone): bool
    {
        return WhatsappConversation::forCompany($companyId)
            ->forPhone($phone)
            ->where('direction', WhatsappConversation::DIRECTION_INBOUND)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();
    }

    private static function manualTask(Lead $lead, string $reason): void
    {
        if (!$lead->user_id) {
            return;
        }
        LeadTask::create([
            'company_id'  => $lead->company_id,
            'lead_id'     => $lead->id,
            'created_by'  => $lead->user_id,
            'assigned_to' => $lead->user_id,
            'title'       => 'Seguimiento manual',
            'type'        => 'whatsapp',
            'priority'    => 'medium',
            'status'      => 'pending',
            'description' => $reason,
            'due_at'      => now(),
        ]);
    }

    private static function leadReplied(Lead $lead, $since): bool
    {
        if (!$since) {
            return false;
        }
        $phone = $lead->whatsapp ?: $lead->phone;
        if (!$phone) {
            return false;
        }
        return WhatsappConversation::forCompany($lead->company_id)
            ->forPhone($phone)
            ->where('direction', WhatsappConversation::DIRECTION_INBOUND)
            ->where('created_at', '>', $since)
            ->exists();
    }
}
