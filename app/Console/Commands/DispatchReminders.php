<?php

namespace App\Console\Commands;

use App\Appointment;
use App\LeadTask;
use App\Reminder;
use App\Notifications\AppointmentReminderNotification;
use App\Notifications\LeadTaskDueNotification;
use App\Notifications\ReminderDueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Envía de verdad los recordatorios vencidos y los avisos de citas próximas.
 * Los modelos ya sabían "quién necesita aviso" (needsNotification / needsReminder),
 * pero nada los despachaba: este comando cierra ese hueco. Cada aviso se marca
 * como enviado tras intentarlo, para no repetirlo en cada corrida.
 */
class DispatchReminders extends Command
{
    protected $signature   = 'crm:dispatch-reminders';
    protected $description = 'Despacha recordatorios vencidos y avisos de citas próximas (email + campana)';

    public function handle(): int
    {
        $reminders = $this->dispatchReminders();
        $appointments = $this->dispatchAppointments();
        $tasks = $this->dispatchTasks();

        $this->info("Recordatorios enviados: {$reminders}. Avisos de cita: {$appointments}. Tareas vencidas: {$tasks}.");
        return 0;
    }

    private function dispatchReminders(): int
    {
        $sent = 0;
        Reminder::needsNotification()->with(['user', 'remindable'])->chunkById(100, function ($chunk) use (&$sent) {
            foreach ($chunk as $reminder) {
                if (!$reminder->user) {
                    $reminder->markNotificationSent(); // sin destinatario: no reintentar
                    continue;
                }
                try {
                    $reminder->user->notify(new ReminderDueNotification($reminder));
                    $sent++;
                } catch (\Throwable $e) {
                    Log::error('No se pudo enviar recordatorio', ['reminder_id' => $reminder->id, 'error' => $e->getMessage()]);
                } finally {
                    $reminder->markNotificationSent();
                }
            }
        });
        return $sent;
    }

    private function dispatchAppointments(): int
    {
        $sent = 0;
        Appointment::needsReminder()->with(['user', 'lead'])->chunkById(100, function ($chunk) use (&$sent) {
            foreach ($chunk as $appointment) {
                if (!$appointment->user) {
                    $appointment->update(['reminder_sent' => true]);
                    continue;
                }
                try {
                    $appointment->user->notify(new AppointmentReminderNotification($appointment));
                    $sent++;
                } catch (\Throwable $e) {
                    Log::error('No se pudo enviar aviso de cita', ['appointment_id' => $appointment->id, 'error' => $e->getMessage()]);
                } finally {
                    $appointment->update(['reminder_sent' => true]);
                }
            }
        });
        return $sent;
    }

    private function dispatchTasks(): int
    {
        $sent = 0;
        LeadTask::query()
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now())
            ->whereNull('due_notified_at')
            ->whereNotNull('assigned_to')
            ->with(['assignee', 'lead'])
            ->chunkById(100, function ($chunk) use (&$sent) {
                foreach ($chunk as $task) {
                    if (!$task->assignee) {
                        $task->update(['due_notified_at' => now()]);
                        continue;
                    }
                    try {
                        $task->assignee->notify(new LeadTaskDueNotification($task));
                        $sent++;
                    } catch (\Throwable $e) {
                        Log::error('No se pudo enviar aviso de tarea', ['task_id' => $task->id, 'error' => $e->getMessage()]);
                    } finally {
                        $task->update(['due_notified_at' => now()]);
                    }
                }
            });
        return $sent;
    }
}
