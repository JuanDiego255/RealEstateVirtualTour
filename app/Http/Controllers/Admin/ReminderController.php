<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Reminder;
use App\Lead;
use App\Appointment;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReminderController extends Controller
{
    /**
     * Display a listing of reminders.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Reminder::with(['remindable'])
            ->byUser($user->id);

        // Filtros
        $filter = $request->get('filter', 'pending');

        switch ($filter) {
            case 'pending':
                $query->pending();
                break;
            case 'due':
                $query->due();
                break;
            case 'overdue':
                $query->overdue();
                break;
            case 'today':
                $query->today()->pending();
                break;
            case 'completed':
                $query->where('is_completed', true);
                break;
            case 'all':
                // No filter
                break;
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $reminders = $query->orderBy('remind_at', 'asc')->paginate(20)->withQueryString();

        // Contadores
        $counts = [
            'pending' => Reminder::byUser($user->id)->pending()->count(),
            'due' => Reminder::byUser($user->id)->due()->count(),
            'overdue' => Reminder::byUser($user->id)->overdue()->count(),
            'today' => Reminder::byUser($user->id)->today()->pending()->count(),
        ];

        return view('admin.crm.reminders.index', compact('reminders', 'counts', 'filter'));
    }

    /**
     * Show the form for creating a new reminder.
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        // Si viene de un lead o cita
        $relatedType = $request->get('type');
        $relatedId = $request->get('id');
        $relatedItem = null;

        if ($relatedType === 'lead' && $relatedId) {
            $relatedItem = Lead::find($relatedId);
        } elseif ($relatedType === 'appointment' && $relatedId) {
            $relatedItem = Appointment::find($relatedId);
        }

        $leads = Lead::byCompany($user->company_id)->active()->orderBy('name')->get();

        // Un admin puede asignar el recordatorio a cualquier miembro de su empresa.
        $agents = $user->isAdmin()
            ? User::where('company_id', $user->company_id)->where('status', User::STATUS_ACTIVE)->orderBy('name')->get()
            : null;

        return view('admin.crm.reminders.create', compact('relatedItem', 'relatedType', 'leads', 'agents'));
    }

    /**
     * Store a newly created reminder.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:' . implode(',', array_keys(Reminder::getPriorities())),
            'remind_at_date' => 'required|date',
            'remind_at_time' => 'required',
            'email_notification' => 'nullable|boolean',
            'is_recurring' => 'nullable|boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_ends_at' => 'nullable|date',
            'remindable_type' => 'nullable|string',
            'remindable_id' => 'nullable|integer',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $user = auth()->user();

        $remindAt = Carbon::parse($request->remind_at_date . ' ' . $request->remind_at_time);

        $data = [
            'user_id' => $this->resolveTargetUserId($request),
            'company_id' => $user->company_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'remind_at' => $remindAt,
            'email_notification' => $request->boolean('email_notification', true),
            'is_recurring' => $request->boolean('is_recurring'),
            'recurrence_type' => $request->recurrence_type,
            'recurrence_interval' => $request->recurrence_interval ?? 1,
            'recurrence_ends_at' => $request->recurrence_ends_at,
        ];

        // Asociar con lead o cita si corresponde
        if ($request->filled('remindable_type') && $request->filled('remindable_id')) {
            $data['remindable_type'] = $request->remindable_type;
            $data['remindable_id'] = $request->remindable_id;
        }

        Reminder::create($data);

        return redirect()->route('admin.crm.reminders.index')
            ->with('success', 'Recordatorio creado correctamente.');
    }

    /**
     * Display the specified reminder.
     */
    public function show(Reminder $reminder)
    {
        $this->authorizeAccess($reminder);

        $reminder->load('remindable');

        return view('admin.crm.reminders.show', compact('reminder'));
    }

    /**
     * Show the form for editing the reminder.
     */
    public function edit(Reminder $reminder)
    {
        $this->authorizeAccess($reminder);

        $user = auth()->user();
        $leads = Lead::byCompany($user->company_id)->active()->orderBy('name')->get();

        return view('admin.crm.reminders.edit', compact('reminder', 'leads'));
    }

    /**
     * Update the specified reminder.
     */
    public function update(Request $request, Reminder $reminder)
    {
        $this->authorizeAccess($reminder);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:' . implode(',', array_keys(Reminder::getPriorities())),
            'remind_at_date' => 'required|date',
            'remind_at_time' => 'required',
            'email_notification' => 'nullable|boolean',
            'is_recurring' => 'nullable|boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_ends_at' => 'nullable|date',
        ]);

        $remindAt = Carbon::parse($request->remind_at_date . ' ' . $request->remind_at_time);

        $reminder->update([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'remind_at' => $remindAt,
            'email_notification' => $request->boolean('email_notification', true),
            'is_recurring' => $request->boolean('is_recurring'),
            'recurrence_type' => $request->recurrence_type,
            'recurrence_interval' => $request->recurrence_interval ?? 1,
            'recurrence_ends_at' => $request->recurrence_ends_at,
            'notification_sent' => false, // Reset notification
        ]);

        return redirect()->route('admin.crm.reminders.index')
            ->with('success', 'Recordatorio actualizado correctamente.');
    }

    /**
     * Mark reminder as complete.
     */
    public function complete(Reminder $reminder)
    {
        $this->authorizeAccess($reminder);

        $reminder->complete();

        return redirect()->back()
            ->with('success', 'Recordatorio completado.');
    }

    /**
     * Dismiss reminder.
     */
    public function dismiss(Reminder $reminder)
    {
        $this->authorizeAccess($reminder);

        $reminder->dismiss();

        return redirect()->back()
            ->with('success', 'Recordatorio descartado.');
    }

    /**
     * Snooze reminder.
     */
    public function snooze(Request $request, Reminder $reminder)
    {
        $this->authorizeAccess($reminder);

        $minutes = $request->get('minutes', 15);
        $reminder->snooze($minutes);

        return redirect()->back()
            ->with('success', "Recordatorio pospuesto {$minutes} minutos.");
    }

    /**
     * Remove the specified reminder.
     */
    public function destroy(Reminder $reminder)
    {
        $this->authorizeAccess($reminder);

        $reminder->delete();

        return redirect()->route('admin.crm.reminders.index')
            ->with('success', 'Recordatorio eliminado correctamente.');
    }

    /**
     * Get pending reminders count (for navbar badge).
     */
    public function pendingCount()
    {
        $count = Reminder::byUser(auth()->id())->due()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get due reminders (for notifications dropdown).
     */
    public function dueReminders()
    {
        $reminders = Reminder::with('remindable')
            ->byUser(auth()->id())
            ->due()
            ->orderBy('remind_at', 'asc')
            ->limit(10)
            ->get();

        return response()->json($reminders);
    }

    /**
     * Quick create reminder from modal.
     */
    public function quickStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'remind_at' => 'required|date',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $user = auth()->user();

        Reminder::create([
            'user_id' => $this->resolveTargetUserId($request),
            'company_id' => $user->company_id,
            'title' => $request->title,
            'remind_at' => Carbon::parse($request->remind_at),
            'priority' => $request->priority ?? 'medium',
            'email_notification' => true,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Destinatario del recordatorio: un admin puede asignarlo a un miembro de su
     * empresa (correo + campana van a esa persona); un agente siempre a sí mismo.
     */
    private function resolveTargetUserId(Request $request): int
    {
        $user = auth()->user();

        if ($user->isAdmin() && $request->filled('assigned_to')) {
            $agent = User::where('id', $request->input('assigned_to'))
                ->where('company_id', $user->company_id)
                ->first();
            if ($agent) {
                return (int) $agent->id;
            }
        }

        return (int) $user->id;
    }

    /**
     * Verify access.
     */
    private function authorizeAccess(Reminder $reminder): void
    {
        if ($reminder->user_id !== auth()->id()) {
            abort(403, 'No tienes acceso a este recordatorio.');
        }
    }
}
