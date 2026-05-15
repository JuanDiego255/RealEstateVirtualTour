<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Appointment;
use App\Lead;
use App\Properties;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display calendar view.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Vista de lista o calendario
        $view = $request->get('view', 'calendar');

        if ($view === 'list') {
            $appointments = Appointment::with(['user', 'lead', 'property', 'vehicle'])
                ->byCompany($user->company_id)
                ->orderBy('starts_at', 'desc')
                ->paginate(20);

            return view('admin.crm.appointments.list', compact('appointments'));
        }

        return view('admin.crm.appointments.calendar');
    }

    /**
     * Get appointments for calendar (API).
     */
    public function calendarEvents(Request $request)
    {
        $user = auth()->user();

        $start = $request->get('start', Carbon::now()->startOfMonth());
        $end = $request->get('end', Carbon::now()->endOfMonth());

        $appointments = Appointment::with(['lead', 'property', 'vehicle'])
            ->byCompany($user->company_id)
            ->betweenDates($start, $end);

        // Filtrar por usuario si se especifica
        if ($request->filled('user_id')) {
            $appointments->byUser($request->user_id);
        }

        $events = $appointments->get()->map(function ($appointment) {
            return $appointment->toCalendarEvent();
        });

        return response()->json($events);
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        $leads = Lead::byCompany($user->company_id)
            ->active()
            ->orderBy('name')
            ->get();

        $properties = Properties::realEstate()->whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->where('status', 'available')->get();

        $vehicles = Properties::vehicles()->whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->whereIn('status', ['available', 'reserved', 'negotiating'])->get();

        $agents = User::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Pre-llenar datos si viene de un lead
        $selectedLead = null;
        if ($request->filled('lead_id')) {
            $selectedLead = Lead::find($request->lead_id);
        }

        // Pre-llenar fecha/hora si viene del calendario
        $defaultDate = $request->get('date', now()->format('Y-m-d'));
        $defaultTime = $request->get('time', '09:00');

        return view('admin.crm.appointments.create', compact(
            'leads', 'properties', 'vehicles', 'agents', 'selectedLead', 'defaultDate', 'defaultTime'
        ));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Appointment::getTypes())),
            'starts_at_date' => 'required|date',
            'starts_at_time' => 'required',
            'duration' => 'required|integer|min:15|max:480',
            'lead_id' => 'nullable|exists:leads,id',
            'property_id' => 'nullable|exists:properties,id',
            'vehicle_id' => ['nullable', Rule::exists('properties', 'id')->where('property_type', 'vehicle')],
            'user_id' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'client_name' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'reminder_minutes' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:20',
        ]);

        $user = auth()->user();

        $startsAt = Carbon::parse($request->starts_at_date . ' ' . $request->starts_at_time);
        $endsAt = $startsAt->copy()->addMinutes($request->duration);

        $appointment = Appointment::create([
            'company_id' => $user->company_id,
            'user_id' => $request->user_id ?? $user->id,
            'lead_id' => $request->lead_id,
            'property_id' => $request->property_id,
            'vehicle_id' => $request->vehicle_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'all_day' => $request->boolean('all_day'),
            'location' => $request->location,
            'address' => $request->address,
            'client_name' => $request->client_name,
            'client_phone' => $request->client_phone,
            'client_email' => $request->client_email,
            'status' => 'scheduled',
            'reminder_minutes' => $request->reminder_minutes ?? 60,
            'color' => $request->color,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'appointment' => $appointment->toCalendarEvent(),
            ]);
        }

        return redirect()->route('admin.crm.appointments.index')
            ->with('success', 'Cita programada correctamente.');
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $this->authorizeCompanyAccess($appointment);

        $appointment->load(['user', 'lead', 'property', 'vehicle']);

        return view('admin.crm.appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the appointment.
     */
    public function edit(Appointment $appointment)
    {
        $this->authorizeCompanyAccess($appointment);

        $user = auth()->user();

        $leads = Lead::byCompany($user->company_id)
            ->orderBy('name')
            ->get();

        $properties = Properties::realEstate()->whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->get();

        $vehicles = Properties::vehicles()->whereHas('category', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->get();

        $agents = User::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.crm.appointments.edit', compact(
            'appointment', 'leads', 'properties', 'vehicles', 'agents'
        ));
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $this->authorizeCompanyAccess($appointment);

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Appointment::getTypes())),
            'starts_at_date' => 'required|date',
            'starts_at_time' => 'required',
            'duration' => 'required|integer|min:15|max:480',
            'lead_id' => 'nullable|exists:leads,id',
            'property_id' => 'nullable|exists:properties,id',
            'vehicle_id' => ['nullable', Rule::exists('properties', 'id')->where('property_type', 'vehicle')],
            'user_id' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'client_name' => 'nullable|string|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', array_keys(Appointment::getStatuses())),
            'reminder_minutes' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:20',
        ]);

        $startsAt = Carbon::parse($request->starts_at_date . ' ' . $request->starts_at_time);
        $endsAt = $startsAt->copy()->addMinutes($request->duration);

        $appointment->update([
            'user_id' => $request->user_id ?? auth()->id(),
            'lead_id' => $request->lead_id,
            'property_id' => $request->property_id,
            'vehicle_id' => $request->vehicle_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'all_day' => $request->boolean('all_day'),
            'location' => $request->location,
            'address' => $request->address,
            'client_name' => $request->client_name,
            'client_phone' => $request->client_phone,
            'client_email' => $request->client_email,
            'status' => $request->status,
            'reminder_minutes' => $request->reminder_minutes ?? 60,
            'color' => $request->color,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'appointment' => $appointment->fresh()->toCalendarEvent(),
            ]);
        }

        return redirect()->route('admin.crm.appointments.index')
            ->with('success', 'Cita actualizada correctamente.');
    }

    /**
     * Update appointment via drag-drop (API).
     */
    public function updateDates(Request $request, Appointment $appointment)
    {
        $this->authorizeCompanyAccess($appointment);

        $request->validate([
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $appointment->update([
            'starts_at' => Carbon::parse($request->starts_at),
            'ends_at' => Carbon::parse($request->ends_at),
            'reminder_sent' => false, // Reset reminder
        ]);

        return response()->json([
            'success' => true,
            'appointment' => $appointment->fresh()->toCalendarEvent(),
        ]);
    }

    /**
     * Cancel appointment.
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $this->authorizeCompanyAccess($appointment);

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $appointment->cancel($request->reason);

        return redirect()->back()
            ->with('success', 'Cita cancelada correctamente.');
    }

    /**
     * Complete appointment.
     */
    public function complete(Request $request, Appointment $appointment)
    {
        $this->authorizeCompanyAccess($appointment);

        $request->validate([
            'outcome' => 'nullable|in:' . implode(',', array_keys(Appointment::getOutcomes())),
            'outcome_notes' => 'nullable|string',
        ]);

        $appointment->complete($request->outcome, $request->outcome_notes);

        return redirect()->back()
            ->with('success', 'Cita completada correctamente.');
    }

    /**
     * Today's appointments.
     */
    public function today()
    {
        $user = auth()->user();

        $appointments = Appointment::with(['user', 'lead', 'property', 'vehicle'])
            ->byCompany($user->company_id)
            ->today()
            ->orderBy('starts_at', 'asc')
            ->get();

        return view('admin.crm.appointments.today', compact('appointments'));
    }

    /**
     * Upcoming appointments.
     */
    public function upcoming()
    {
        $user = auth()->user();

        $appointments = Appointment::with(['user', 'lead', 'property', 'vehicle'])
            ->byCompany($user->company_id)
            ->upcoming()
            ->limit(20)
            ->get();

        return view('admin.crm.appointments.upcoming', compact('appointments'));
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment)
    {
        $this->authorizeCompanyAccess($appointment);

        $appointment->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.crm.appointments.index')
            ->with('success', 'Cita eliminada correctamente.');
    }

    /**
     * Verify company access.
     */
    private function authorizeCompanyAccess(Appointment $appointment): void
    {
        if ($appointment->company_id !== auth()->user()->company_id) {
            abort(403, 'No tienes acceso a esta cita.');
        }
    }
}
