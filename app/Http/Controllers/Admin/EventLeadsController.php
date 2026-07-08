<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Lead;
use App\Models\EventLead;
use App\Models\VehicleQuote;
use App\Services\LeadCrmService;
use Illuminate\Http\Request;

/**
 * Sección "Eventos" del panel: muestra TODOS los registros capturados en el
 * kiosko (sin el límite del Dashboard de eventos), separados por tipo:
 *   - "Me interesa"  → event_leads   (EventLead)
 *   - "Cotizaciones" → vehicle_quotes (VehicleQuote)
 * Permite búsqueda en tiempo real, agregar al CRM individualmente y una
 * integración masiva de todos los registros de un tipo.
 */
class EventLeadsController extends Controller
{
    /**
     * Listado con filtro por tipo + búsqueda por nombre (AJAX).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->canAccessModule('event_dashboard'), 403, 'Sin permiso para ver los eventos.');

        $type      = in_array($request->get('type'), ['interest', 'quotes']) ? $request->get('type') : 'interest';
        $search    = trim((string) $request->get('search', ''));
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        if ($type === 'quotes') {
            $records = VehicleQuote::with(['property', 'capturedBy'])
                ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->when($search, fn($q) => $q->where(function ($s) use ($search) {
                    $s->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(20)
                ->withQueryString();

            // Marcar cuáles ya están en el CRM (por teléfono, fuente 'quote')
            $phones = collect($records->items())->pluck('customer_phone')->filter()->values();
            $inCrm  = Lead::where('source', 'quote')->whereIn('phone', $phones)->pluck('phone')->flip();

            $partial = 'admin.eventos._table_quotes';
            $stats   = $this->quoteStats($companyId);
        } else {
            $records = EventLead::with(['property', 'capturedBy'])
                ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->when($search, fn($q) => $q->where(function ($s) use ($search) {
                    $s->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(20)
                ->withQueryString();

            // Marcar cuáles ya están en el CRM (por event_lead_id)
            $ids   = collect($records->items())->pluck('id');
            $inCrm = Lead::whereIn('event_lead_id', $ids)->pluck('event_lead_id')->flip();

            $partial = 'admin.eventos._table_interest';
            $stats   = $this->interestStats($companyId);
        }

        if ($request->ajax() || $request->boolean('ajax')) {
            return response()->json([
                'table_html' => view($partial, compact('records', 'inCrm'))->render(),
                'pagination' => $records->links()->toHtml(),
                'stats_html' => view('admin.eventos._stats', compact('type', 'stats'))->render(),
            ]);
        }

        return view('admin.eventos.index', compact('records', 'inCrm', 'type', 'search', 'stats', 'partial'));
    }

    /**
     * Integrar TODOS los registros de un tipo al CRM robusto.
     */
    public function bulkToCrm(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->canAccessModule('event_dashboard'), 403, 'Sin permiso.');

        $type      = in_array($request->get('type'), ['interest', 'quotes']) ? $request->get('type') : 'interest';
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;
        $service   = app(LeadCrmService::class);

        $created = 0;
        $skipped = 0;

        if ($type === 'quotes') {
            VehicleQuote::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->chunkById(200, function ($chunk) use ($service, $user, &$created, &$skipped) {
                    foreach ($chunk as $quote) {
                        $r = $service->migrateQuote($quote, $user->id, $user->company_id ?? $quote->company_id);
                        $r['created'] ? $created++ : $skipped++;
                    }
                });
        } else {
            EventLead::when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->chunkById(200, function ($chunk) use ($service, $user, &$created, &$skipped) {
                    foreach ($chunk as $lead) {
                        $r = $service->migrateEventLead($lead, $user->id, $user->company_id ?? $lead->company_id);
                        $r['created'] ? $created++ : $skipped++;
                    }
                });
        }

        return response()->json([
            'success' => true,
            'created' => $created,
            'skipped' => $skipped,
            'message' => "Se integraron {$created} registro(s) al CRM. {$skipped} ya existían u omitidos.",
        ]);
    }

    private function interestStats(?int $companyId): array
    {
        $base = fn() => EventLead::when($companyId, fn($q) => $q->where('company_id', $companyId));

        return [
            'total'   => $base()->count(),
            'today'   => $base()->whereDate('created_at', today())->count(),
            'hot'     => $base()->where('interest_level', 'hot')->count(),
            'pending' => $base()->where('contacted', false)->count(),
        ];
    }

    private function quoteStats(?int $companyId): array
    {
        $base = fn() => VehicleQuote::when($companyId, fn($q) => $q->where('company_id', $companyId));

        return [
            'total' => $base()->count(),
            'today' => $base()->whereDate('created_at', today())->count(),
        ];
    }
}
