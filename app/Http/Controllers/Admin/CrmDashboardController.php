<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Appointment;
use App\Lead;
use App\LeadTask;
use App\Reminder;
use Illuminate\Support\Carbon;

class CrmDashboardController extends Controller
{
    public function today()
    {
        $user    = auth()->user();
        $company = $user->company_id;
        $today   = Carbon::today();

        // Citas de hoy
        $appointmentsToday = Appointment::with('lead')
            ->where('company_id', $company)
            ->today()
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('starts_at')
            ->get();

        // Tareas vencidas (overdue) — pendientes o en progreso
        $overdueTasks = LeadTask::with('lead')
            ->byCompany($company)
            ->overdue()
            ->orderBy('due_at')
            ->limit(20)
            ->get();

        // Tareas con vencimiento hoy
        $tasksDueToday = LeadTask::with('lead')
            ->byCompany($company)
            ->dueToday()
            ->pending()
            ->orderBy('due_at')
            ->get();

        // Recordatorios pendientes/vencidos (remindable es polimórfico, no 'lead')
        $dueReminders = Reminder::with('remindable')
            ->byCompany($company)
            ->due()
            ->orderBy('remind_at')
            ->limit(20)
            ->get();

        // Leads que necesitan atención: follow-up vencido, sin actividad reciente en 7+ días
        $leadsNeedingAttention = Lead::with(['user'])
            ->where('company_id', $company)
            ->active()
            ->where(function ($q) use ($today) {
                // Follow-up vencido
                $q->where(function ($q2) use ($today) {
                    $q2->whereNotNull('next_follow_up')
                       ->where('next_follow_up', '<', $today);
                })
                // O sin actividad reciente (last_contact_at nulo o > 7 días)
                ->orWhere(function ($q2) use ($today) {
                    $q2->where(function ($q3) use ($today) {
                        $q3->whereNull('last_contact_at')
                           ->orWhere('last_contact_at', '<', $today->copy()->subDays(7));
                    })->whereNotIn('status', [Lead::STATUS_WON, Lead::STATUS_LOST]);
                });
            })
            ->orderByRaw("CASE WHEN next_follow_up < ? THEN 0 ELSE 1 END", [$today])
            ->orderBy('next_follow_up')
            ->limit(10)
            ->get();

        // Conteos para las stat cards
        $stats = [
            'appointments_today'  => $appointmentsToday->count(),
            'overdue_tasks'       => $overdueTasks->count(),
            'due_reminders'       => $dueReminders->count(),
            'needs_attention'     => $leadsNeedingAttention->count(),
        ];

        return view('admin.crm.dashboard', compact(
            'appointmentsToday',
            'overdueTasks',
            'tasksDueToday',
            'dueReminders',
            'leadsNeedingAttention',
            'stats'
        ));
    }
}
