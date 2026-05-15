<?php

namespace App\Console\Commands;

use App\Lead;
use App\PipelineRule;
use App\LeadActivity;
use App\Reminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunPipelineRules extends Command
{
    protected $signature   = 'crm:run-pipeline-rules';
    protected $description = 'Evaluate active pipeline rules and execute their actions on matching leads';

    public function handle(): int
    {
        $rules = PipelineRule::where('is_active', true)->get();

        if ($rules->isEmpty()) {
            $this->info('No active pipeline rules found.');
            return 0;
        }

        $triggered = 0;
        $skipped   = 0;

        foreach ($rules as $rule) {
            $leads = Lead::where('company_id', $rule->company_id)
                ->where('status', $rule->trigger_stage)
                ->with('activities')
                ->get();

            foreach ($leads as $lead) {
                if (!$rule->triggersFor($lead)) {
                    continue;
                }

                // Deduplication: skip if already acted on this rule for this lead recently
                $alreadyActed = $lead->activities()
                    ->where('description', 'LIKE', '%[rule:' . $rule->id . ']%')
                    ->where('activity_at', '>=', now()->subDays($rule->days_inactive))
                    ->exists();

                if ($alreadyActed) {
                    $skipped++;
                    continue;
                }

                $marker = ' [rule:' . $rule->id . ']';

                switch ($rule->action) {
                    case 'alert':
                        $lead->activities()->create([
                            'user_id'     => null,
                            'type'        => 'note',
                            'description' => '⚠️ Alerta automática: ' . ($rule->action_message ?: $rule->name) . $marker,
                            'activity_at' => now(),
                        ]);
                        break;

                    case 'create_reminder':
                        // Only create if no pending reminder with same marker exists
                        $exists = \App\Reminder::where('remindable_type', Lead::class)
                            ->where('remindable_id', $lead->id)
                            ->where('title', 'LIKE', '%' . $rule->name . '%')
                            ->where('status', 'pending')
                            ->exists();

                        if (!$exists) {
                            \App\Reminder::create([
                                'remindable_type' => Lead::class,
                                'remindable_id'   => $lead->id,
                                'user_id'         => $lead->user_id,
                                'title'           => $rule->action_message ?: 'Seguimiento: ' . $rule->name,
                                'remind_at'       => now()->addDay(),
                                'status'          => 'pending',
                                'priority'        => 'high',
                            ]);
                            $lead->activities()->create([
                                'user_id'     => null,
                                'type'        => 'note',
                                'description' => '🔔 Recordatorio automático creado: ' . $rule->name . $marker,
                                'activity_at' => now(),
                            ]);
                        }
                        break;

                    case 'notify_admin':
                        $lead->activities()->create([
                            'user_id'     => null,
                            'type'        => 'note',
                            'description' => '📢 Notificación admin: ' . ($rule->action_message ?: $rule->name) . $marker,
                            'activity_at' => now(),
                        ]);
                        break;
                }

                $triggered++;
            }
        }

        $this->info("Pipeline rules processed: {$triggered} actions triggered, {$skipped} skipped (dedup).");
        return 0;
    }
}
