<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Lead;
use App\LeadTask;
use App\Reminder;

/**
 * Bandeja de trabajo del asesor: lo que necesita atención ahora — leads sin
 * contactar, tareas vencidas y recordatorios vencidos. Un agente ve lo suyo;
 * un administrador ve lo de toda la empresa.
 */
class LeadInboxController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_if(empty($user->company_id), 403, 'Tu usuario no está asociado a una empresa.');

        $companyId = (int) $user->company_id;
        $isAdmin   = $user->isAdmin();
        $uid       = $user->id;

        // Leads sin contactar (nuevos o sin primer contacto), priorizados por score.
        $uncontacted = Lead::byCompany($companyId)
            ->active()
            ->where(fn($q) => $q->where('status', Lead::STATUS_NEW)->orWhereNull('last_contact_at'))
            ->when(!$isAdmin, fn($q) => $q->where('user_id', $uid))
            ->with('user')
            ->orderByDesc('score')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        // Tareas vencidas.
        $overdueTasks = LeadTask::byCompany($companyId)
            ->overdue()
            ->when(!$isAdmin, fn($q) => $q->where('assigned_to', $uid))
            ->with(['lead', 'assignee'])
            ->orderBy('due_at')
            ->limit(100)
            ->get();

        // Recordatorios vencidos.
        $dueReminders = Reminder::byCompany($companyId)
            ->due()
            ->when(!$isAdmin, fn($q) => $q->where('user_id', $uid))
            ->with(['remindable', 'user'])
            ->orderBy('remind_at')
            ->limit(100)
            ->get();

        $counts = [
            'uncontacted' => $uncontacted->count(),
            'tasks'       => $overdueTasks->count(),
            'reminders'   => $dueReminders->count(),
        ];

        return view('admin.crm.inbox', compact('uncontacted', 'overdueTasks', 'dueReminders', 'counts', 'isAdmin'));
    }
}
