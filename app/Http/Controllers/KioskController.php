<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Properties;
use App\Models\KioskSetting;
use App\Models\VehicleEventView;
use App\Models\QrScan;
use App\Models\VehicleQuote;
use App\Models\EventLead;
use App\Models\ClientWishlist;
use App\Models\SpinHotspot;
use App\Models\VehicleColor;
use App\Models\TestDriveVideo;
use App\Models\Spin;
use App\Models\RafflePrize;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class KioskController extends Controller
{
    /**
     * Vista principal del modo kiosko
     * Ahora usa Properties con property_type = 'vehicle'
     * Muestra todos los vehículos (con o sin spin 360)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Bifurcación: si la empresa usa el kiosko "other" (sin catálogo), redirigir a esa vista
        if ($user->company && $user->company->other_kiosk) {
            return $this->indexOther($request, $user->company);
        }

        // Super admin puede filtrar por company_id en query string; los demás usan su propia empresa
        $companyId = $user->isSuperAdmin()
            ? $request->get('company_id', $user->company_id ?? 1)
            : $user->company_id;

        $eventName = $request->get('event');

        $settings = KioskSetting::getActiveForCompany($companyId)
            ?? new KioskSetting(KioskSetting::defaults());

        // Obtener IDs de categorías de la empresa del usuario
        $companyCategoryIds = \App\Category::where('company_id', $companyId)->pluck('id');

        // Obtener todos los vehiculos para el kiosko (con o sin spin), filtrados por sucursal
        $query = Properties::vehicles()
            ->whereIn('status', ['available', 'reserved', 'negotiating'])
            ->where(function ($q) use ($companyCategoryIds) {
                $q->whereIn('category_id', $companyCategoryIds)
                    ->orWhereHas('subcategory', function ($sq) use ($companyCategoryIds) {
                        $sq->whereIn('category_id', $companyCategoryIds);
                    });
            });

        // Filtrar por vehiculos destacados si estan configurados
        if (!empty($settings->featured_vehicle_ids)) {
            $query->whereIn('id', $settings->featured_vehicle_ids);
        }

        // Excluir vehiculos si estan configurados
        if (!empty($settings->excluded_vehicle_ids)) {
            $query->whereNotIn('id', $settings->excluded_vehicle_ids);
        }

        $vehicles = $query->with(['scenes' => function ($q) {
            $q->with('spin');
        }])->get();

        return view('kiosk.index', compact('vehicles', 'settings', 'eventName'));
    }

    /**
     * Kiosko "other": captura de leads/cotizaciones sin catálogo de productos
     * Se activa cuando company->other_kiosk = true
     */
    private function indexOther(Request $request, $company)
    {
        $settings = KioskSetting::getActiveForCompany($company->id)
            ?? new KioskSetting(KioskSetting::defaults());
        $eventName = $request->get('event', $settings->event_name ?? null);

        // Categorías configuradas por la empresa (guardadas en company->settings JSON)
        $companySettings = $company->settings ?? [];
        $categoriesRaw   = $companySettings['other_kiosk_categories'] ?? '';
        $categories      = array_filter(array_map('trim', explode(',', $categoriesRaw)));

        // Número de WhatsApp: contact_info del KioskSetting, o teléfono de la empresa
        $whatsappNumber = $settings->contact_info ?? $company->phone ?? null;

        // PIN del modo asesor y datos del agente actual
        $agentPin = $companySettings['agent_kiosk_pin'] ?? null;
        $agent    = Auth::user();

        return view('kiosk.other-index', compact(
            'settings',
            'eventName',
            'company',
            'categories',
            'whatsappNumber',
            'agentPin',
            'agent'
        ));
    }

    /**
     * Vista de vehiculo individual en modo kiosko
     * Soporta vehículos con o sin spin 360
     */
    public function vehicle(Request $request, $id)
    {
        $vehicle = Properties::vehicles()
            ->with(['scenes' => function ($q) {
                $q->with('spin');
            }])
            ->findOrFail($id);

        $eventName = $request->get('event');
        $source = $request->get('qr') ? 'qr' : 'kiosk';

        // Registrar vista
        $sessionId = $request->session()->getId();
        VehicleEventView::create([
            'property_id' => $id,
            'session_id' => $sessionId,
            'source' => $source,
            'device_type' => $this->detectDeviceType($request),
            'event_name' => $eventName,
        ]);

        // Si viene de QR, registrar escaneo
        if ($qrCode = $request->get('qr')) {
            $qr = QrScan::where('qr_code', $qrCode)->first();
            if ($qr) {
                $qr->recordScan();
            }
        }

        // Obtener datos adicionales
        $spin = $vehicle->activeSpin;
        $hotspots = $spin ? SpinHotspot::getForSpin($spin->id) : collect();
        $colors = VehicleColor::getForProperty($id);
        $testDriveVideos = TestDriveVideo::getForProperty($id);
        $qrData = QrScan::generateForProperty($id, $eventName);

        $settings = KioskSetting::getActiveForCompany($vehicle->category->company_id ?? 1)
            ?? new KioskSetting(KioskSetting::defaults());

        return view('kiosk.vehicle', compact(
            'vehicle',
            'spin',
            'hotspots',
            'colors',
            'testDriveVideos',
            'qrData',
            'settings',
            'eventName'
        ));
    }

    /**
     * API: Obtener datos del vehiculo (para navegacion AJAX)
     * Soporta vehículos con o sin spin
     */
    public function vehicleData(Request $request, $id)
    {
        $vehicle = Properties::vehicles()
            ->with(['scenes' => function ($q) {
                $q->with('spin');
            }])
            ->findOrFail($id);

        $spin = $vehicle->activeSpin;

        return response()->json([
            'vehicle' => $vehicle,
            'spin' => $spin,
            'hotspots' => $spin ? SpinHotspot::getForSpin($spin->id) : [],
            'colors' => VehicleColor::getForProperty($id),
            'test_drive_videos' => TestDriveVideo::getForProperty($id),
        ]);
    }

    /**
     * Generar codigo QR para un vehiculo
     * Usa SVG por defecto para evitar dependencia de imagick
     */
    public function generateQr(Request $request, $vehicleId)
    {
        $eventName = $request->get('event');
        $qrData = QrScan::generateForProperty($vehicleId, $eventName);

        $url = url("/kiosk/vehicle/{$vehicleId}?qr={$qrData->qr_code}");

        // PNG solo si se solicita explícitamente (requiere imagick)
        if ($request->get('format') === 'png') {
            $qr = QrCode::format('png')
                ->size(300)
                ->margin(2)
                ->generate($url);

            return response($qr)->header('Content-Type', 'image/png');
        }

        // SVG por defecto (no requiere imagick)
        $qr = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($url);

        return response($qr)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Calcular cotizacion de financiamiento
     */
    public function calculateQuote(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:properties,id',
            'vehicle_price' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'term_months' => 'required|integer|in:12,24,36,48,60,72,84',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'payment_frequency' => 'nullable|in:monthly,annual',
        ]);

        $quote = VehicleQuote::generateQuote(
            $request->vehicle_price,
            $request->down_payment,
            $request->term_months,
            $request->interest_rate,
            $request->get('payment_frequency', 'monthly')
        );

        return response()->json($quote);
    }

    /**
     * Guardar y enviar cotizacion
     */
    public function saveQuote(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'nullable|exists:properties,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'required|string|max:20',
            'vehicle_price' => 'nullable|numeric',
            'down_payment' => 'nullable|numeric',
            'term_months' => 'nullable|integer',
            'interest_rate' => 'nullable|numeric',
            'payment_frequency' => 'nullable|in:monthly,annual',
            'monthly_payment' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $paymentFrequency = $request->get('payment_frequency', 'monthly');
        $vehiclePrice     = (float) $request->vehicle_price;
        $downPayment      = (float) $request->down_payment;

        // Use pre-calculated values sent by the frontend (exact amounts the user saw in the form).
        // Fall back to server-side calculation only if the frontend didn't send them.
        if ($request->has('monthly_payment') && $request->has('total_amount')) {
            $monthlyPayment       = (float) $request->monthly_payment;
            $totalAmount          = (float) $request->total_amount;
            $totalInterest        = (float) $request->total_interest;
            $downPaymentPercent   = $vehiclePrice > 0 ? round(($downPayment / $vehiclePrice) * 100, 2) : 0;
        } else {
            $quoteData            = VehicleQuote::generateQuote($vehiclePrice, $downPayment, $request->term_months, $request->interest_rate, $paymentFrequency);
            $monthlyPayment       = $quoteData['monthly_payment'];
            $totalAmount          = $quoteData['total_amount'];
            $totalInterest        = $quoteData['total_interest'];
            $downPaymentPercent   = $quoteData['down_payment_percent'];
        }

        $quote = VehicleQuote::create([
            'property_id'          => $request->vehicle_id,
            'company_id'           => Auth::user()->company_id,
            'customer_name'        => $request->customer_name,
            'customer_email'       => $request->customer_email,
            'customer_phone'       => $request->customer_phone,
            'vehicle_price'        => $vehiclePrice,
            'down_payment'         => $downPayment,
            'down_payment_percent' => $downPaymentPercent,
            'term_months'          => $request->term_months ? (int) $request->term_months : null,
            'interest_rate'        => $request->interest_rate ? (float) $request->interest_rate : null,
            'monthly_payment'      => $monthlyPayment,
            'total_interest'       => $totalInterest,
            'total_amount'         => $totalAmount,
            'currency'             => $request->get('currency', 'CRC'),
            'event_name'           => $request->get('event_name'),
            'payment_frequency'    => $paymentFrequency,
            'description'          => $request->get('description'),
            'captured_by_user_id'  => Auth::id(),
        ]);

        // Actualizar estadisticas
        VehicleEventView::where('property_id', $request->vehicle_id)
            ->where('session_id', $request->session()->getId())
            ->latest()
            ->first()
            ?->update(['quoted' => true]);

        return response()->json([
            'success' => true,
            'quote_id' => $quote->id,
            'quote' => $quote,
        ]);
    }

    /**
     * Generar PDF de cotizacion
     */
    public function quotePdf($quoteId)
    {
        $quote = VehicleQuote::with('property')->findOrFail($quoteId);

        $pdf = Pdf::loadView('kiosk.quote-pdf', compact('quote'));

        $quote->update([
            'pdf_generated' => true,
            'pdf_path' => "quotes/quote-{$quoteId}.pdf",
        ]);

        $filename = $quote->property
            ? "cotizacion-{$quote->property->brand}-{$quote->property->model}.pdf"
            : "cotizacion-{$quote->customer_name}-{$quote->id}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Enviar cotizacion por email
     */
    public function sendQuoteEmail(Request $request, $quoteId)
    {
        $quote = VehicleQuote::with('property')->findOrFail($quoteId);

        if (!$quote->customer_email) {
            return response()->json(['error' => 'No hay email registrado'], 400);
        }

        // Aqui se enviaria el email (simplificado)
        // Mail::to($quote->customer_email)->send(new QuoteMail($quote));

        $quote->update(['email_sent' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Capturar lead del evento
     */
    public function captureLead(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'email'      => 'nullable|email',
            'vehicle_id' => 'nullable|exists:properties,id',
            'photo'      => 'nullable|image|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('event-leads/photos', 'public');
        }

        $lead = EventLead::create([
            'name'                => $request->name,
            'phone'               => $request->phone,
            'email'               => $request->email,
            'property_id'         => $request->vehicle_id,
            'company_id'          => Auth::user()->company_id,
            'source'              => $request->get('source', 'kiosk'),
            'event_name'          => $request->get('event_name'),
            'interest_level'      => $request->get('interest_level', 'medium'),
            'lead_category'       => $request->get('lead_category', 'prospect'),
            'notes'               => $request->get('notes'),
            'description'         => $request->get('description'),
            'photo_path'          => $photoPath,
            'vehicles_viewed'     => $request->get('vehicles_viewed', []),
            'captured_by_user_id' => Auth::id(),
        ]);

        // Actualizar estadisticas
        if ($request->vehicle_id) {
            VehicleEventView::where('property_id', $request->vehicle_id)
                ->where('session_id', $request->session()->getId())
                ->latest()
                ->first()
                ?->update(['lead_captured' => true]);
        }

        $agent = Auth::user();

        return response()->json([
            'success'  => true,
            'lead_id'  => $lead->id,
            'agent'    => [
                'name'   => $agent->name,
                'avatar' => $agent->avatar_url,
            ],
        ]);
    }

    /**
     * Exportar leads del evento a CSV
     */
    public function exportLeads(Request $request)
    {
        $eventName = $request->get('event');
        $user      = Auth::user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        $leads = EventLead::with('capturedBy')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($eventName,  fn($q) => $q->where('event_name', $eventName))
            ->latest()
            ->get();

        $filename = 'leads-' . ($eventName ? Str::slug($eventName) : 'evento') . '-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM para compatibilidad con Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Nombre', 'Teléfono', 'Email', 'Descripción', 'Nivel Interés', 'Urgencia', 'Evento', 'Origen', 'Fecha/Hora', 'Agente', 'Contactado']);

            foreach ($leads as $lead) {
                preg_match('/\[Urgencia:\s*([^\]]+)\]/', $lead->notes ?? '', $m);
                fputcsv($file, [
                    $lead->name,
                    $lead->phone,
                    $lead->email ?? '',
                    $lead->description ?? '',
                    $lead->interest_level,
                    $m[1] ?? '',
                    $lead->event_name ?? '',
                    $lead->source,
                    $lead->created_at->format('d/m/Y H:i'),
                    $lead->capturedBy?->name ?? '',
                    $lead->contacted ? 'Sí' : 'No',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── RAFFLE ──────────────────────────────────────────────────────────────

    public function rafflePrizes(Request $request)
    {
        $company = Auth::user()->company;
        if (!$company) return response()->json(['prizes' => []]);

        $prizes = RafflePrize::where('company_id', $company->id)
            ->available()
            ->get(['id', 'name', 'emoji', 'color', 'weight', 'quantity', 'claimed']);

        return response()->json(['prizes' => $prizes]);
    }

    public function raffleSpin(Request $request)
    {
        $request->validate([
            'name'       => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'capture'    => 'boolean',
            'event_name' => 'nullable|string|max:255',
        ]);

        $user    = Auth::user();
        $company = $user->company;
        if (!$company) return response()->json(['success' => false, 'message' => 'Sin empresa']);

        $prizes = RafflePrize::where('company_id', $company->id)->available()->get();
        if ($prizes->isEmpty()) {
            return response()->json(['success' => false, 'message' => '¡Los premios se han agotado!']);
        }

        // Selección aleatoria ponderada
        $totalWeight = $prizes->sum('weight');
        $rand        = rand(1, $totalWeight);
        $accumulated = 0;
        $winner      = null;
        foreach ($prizes as $prize) {
            $accumulated += $prize->weight;
            if ($rand <= $accumulated) {
                $winner = $prize;
                break;
            }
        }
        if (!$winner) $winner = $prizes->first();

        $winner->increment('claimed');

        if ($request->boolean('capture') && $request->name && $request->phone) {
            EventLead::create([
                'company_id'     => $company->id,
                'name'           => $request->name,
                'phone'          => $request->phone,
                'source'         => 'kiosk_raffle',
                'description'    => 'Premio ruleta: ' . $winner->name,
                'interest_level' => 'medium',
                'lead_category'  => 'raffle',
                'event_name'     => $request->event_name,
                'captured_by'    => $user->id,
            ]);
        }

        return response()->json(['success' => true, 'prize' => $winner]);
    }

    /**
     * Actualizar duracion de vista
     */
    public function updateViewDuration(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:properties,id',
            'duration' => 'required|integer|min:0',
        ]);

        VehicleEventView::where('property_id', $request->vehicle_id)
            ->where('session_id', $request->session()->getId())
            ->latest()
            ->first()
            ?->update([
                'view_duration_seconds' => $request->duration,
                'spin_interacted' => $request->get('spin_interacted', false),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Wishlist: Crear o actualizar
     */
    public function updateWishlist(Request $request)
    {
        $token = $request->get('token');

        if ($token) {
            $wishlist = ClientWishlist::findByToken($token);
            if (!$wishlist) {
                return response()->json(['error' => 'Wishlist no encontrada'], 404);
            }
        } else {
            $wishlist = ClientWishlist::create([
                'client_name' => $request->get('client_name'),
                'client_email' => $request->get('client_email'),
                'client_phone' => $request->get('client_phone'),
                'property_ids' => [],
                'vehicle_ids' => [],
                'event_name' => $request->get('event_name'),
            ]);
        }

        if ($request->has('add_vehicle')) {
            $wishlist->addProperty($request->add_vehicle);
        }

        if ($request->has('remove_vehicle')) {
            $wishlist->removeProperty($request->remove_vehicle);
        }

        return response()->json([
            'success' => true,
            'wishlist' => $wishlist->fresh(),
            'share_url' => $wishlist->share_url,
        ]);
    }

    /**
     * Ver wishlist compartida
     */
    public function viewWishlist($token)
    {
        $wishlist = ClientWishlist::findByToken($token);

        if (!$wishlist || !$wishlist->isValid()) {
            abort(404, 'Wishlist no encontrada o expirada');
        }

        $wishlist->recordAccess();
        $vehicles = $wishlist->properties;

        return view('kiosk.wishlist', compact('wishlist', 'vehicles'));
    }

    /**
     * Comparador side-by-side con spins sincronizados
     */
    public function compare(Request $request)
    {
        $vehicleIds = $request->get('vehicles', []);

        if (count($vehicleIds) < 2) {
            return redirect()->route('kiosk.index')
                ->with('error', 'Selecciona al menos 2 vehiculos para comparar');
        }

        $vehicles = Properties::vehicles()
            ->with(['scenes' => function ($q) {
                $q->with('spin');
            }])
            ->whereIn('id', $vehicleIds)
            ->get();

        // Registrar comparacion
        foreach ($vehicleIds as $vehicleId) {
            VehicleEventView::where('property_id', $vehicleId)
                ->where('session_id', $request->session()->getId())
                ->latest()
                ->first()
                ?->update(['compared' => true]);
        }

        $eventName = $request->get('event');

        return view('kiosk.compare', compact('vehicles', 'eventName'));
    }

    /**
     * Dashboard de estadisticas del evento
     */
    public function dashboard(Request $request)
    {
        $eventName = $request->get('event');
        $user = Auth::user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        // Top vehiculos vistos
        $topViewed = VehicleEventView::topViewed($eventName, 10);

        // Top QR escaneados
        $topQrScans = QrScan::topScanned($eventName, 10);

        // Estadisticas de leads (filtradas por empresa)
        $leadStats = EventLead::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($eventName, fn($q) => $q->where('event_name', $eventName))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN interest_level = "hot" THEN 1 ELSE 0 END) as hot,
                SUM(CASE WHEN contacted = 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN contacted = 1 THEN 1 ELSE 0 END) as contacted
            ')->first()->toArray();

        // Cotizaciones del dia (filtradas por empresa)
        $quotesToday = VehicleQuote::whereDate('created_at', today())
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($eventName, fn($q) => $q->where('event_name', $eventName))
            ->count();

        // Vistas por hora (ultimas 24h)
        $viewsByHour = VehicleEventView::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', now()->subHours(24))
            ->when($eventName, fn($q) => $q->where('event_name', $eventName))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Leads recientes (filtrados por empresa)
        $recentLeads = EventLead::with(['property', 'capturedBy'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($eventName, fn($q) => $q->where('event_name', $eventName))
            ->latest()
            ->limit(20)
            ->get();

        // Cotizaciones recientes (filtradas por empresa)
        $recentQuotes = VehicleQuote::with(['property', 'capturedBy'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($eventName, fn($q) => $q->where('event_name', $eventName))
            ->latest()
            ->limit(15)
            ->get();

        return view('kiosk.dashboard', compact(
            'topViewed',
            'topQrScans',
            'leadStats',
            'quotesToday',
            'viewsByHour',
            'recentLeads',
            'recentQuotes',
            'eventName'
        ) + ['isOtherKiosk' => $user->company?->other_kiosk ?? false]);
    }

    /**
     * Marcar lead del evento como contactado
     */
    public function markLeadContacted(Request $request, $id)
    {
        $lead = EventLead::findOrFail($id);
        $lead->markAsContacted(auth()->user()->name ?? 'Sistema');

        return response()->json(['success' => true]);
    }

    /**
     * Agregar lead del evento al CRM principal
     */
    public function addEventLeadToCRM(Request $request, $id)
    {
        $eventLead = EventLead::with('property')->findOrFail($id);

        // Verificar si ya existe un lead con el mismo teléfono
        $existingLead = \App\Lead::where(function ($query) use ($eventLead) {
            $query->where('phone', $eventLead->phone);

            if (!empty($eventLead->email)) {
                $query->orWhere('email', $eventLead->email);
            }
        })->first();

        if ($existingLead) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un lead con este teléfono o email en el CRM',
                'lead_id' => $existingLead->id
            ]);
        }

        // Mapear nivel de interés a prioridad
        $priorityMap = [
            'hot' => 'urgent',
            'high' => 'high',
            'medium' => 'medium',
            'low' => 'low'
        ];

        // Mapear fuente del evento a fuente del CRM
        $sourceMap = [
            'event' => 'event',
            'kiosk' => 'kiosk',
            'qr' => 'qr',
            'compare' => 'event',
            'quote' => 'quote',
        ];

        // Crear lead en CRM
        $lead = \App\Lead::create([
            'company_id' => auth()->user()->company_id ?? $eventLead->company_id,
            'user_id' => auth()->id(),
            'property_id' => $eventLead->property_id,
            'name' => $eventLead->name,
            'email' => $eventLead->email,
            'phone' => $eventLead->phone,
            'status' => 'new',
            'source' => $sourceMap[$eventLead->source] ?? 'event',
            'priority' => $priorityMap[$eventLead->interest_level] ?? 'medium',
            'interest_type' => 'buy',
            'notes' => "Lead capturado en evento: {$eventLead->event_name}\n" .
                "Origen: {$eventLead->source}\n" .
                "Nivel de interés: {$eventLead->interest_level}\n" .
                ($eventLead->notes ? "Notas: {$eventLead->notes}" : ''),
            'first_contact_at' => $eventLead->created_at,
            'event_name' => $eventLead->event_name,
            'event_lead_id' => $eventLead->id,
        ]);

        // Registrar actividad inicial
        $lead->logActivity('note', [
            'subject' => 'Lead importado desde evento',
            'description' => "Importado automáticamente desde el evento '{$eventLead->event_name}'. " .
                ($eventLead->property ? "Interesado en: {$eventLead->property->brand} {$eventLead->property->model}" : ''),
        ]);

        // Marcar el event lead como migrado
        $eventLead->update([
            'follow_up_status' => 'completed',
            'notes' => ($eventLead->notes ? $eventLead->notes . "\n" : '') . "Migrado a CRM Lead #{$lead->id}"
        ]);

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'message' => 'Lead agregado al CRM exitosamente'
        ]);
    }

    /**
     * Crear lead en CRM desde una cotización
     */
    public function addQuoteToCRM(Request $request, $quoteId)
    {
        $quote = VehicleQuote::with('property')->findOrFail($quoteId);

        if (!$quote->customer_name && !$quote->customer_phone) {
            return response()->json([
                'success' => false,
                'message' => 'La cotización no tiene datos del cliente'
            ]);
        }

        // Verificar si ya existe un lead con el mismo teléfono
        if ($quote->customer_phone) {
            $existingLead = \App\Lead::where('phone', $quote->customer_phone)->first();
            if ($existingLead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un lead con este teléfono en el CRM',
                    'lead_id' => $existingLead->id
                ]);
            }
        }

        // Crear lead en CRM
        $lead = \App\Lead::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'property_id' => $quote->property_id,
            'name' => $quote->customer_name ?? 'Sin nombre',
            'email' => $quote->customer_email,
            'phone' => $quote->customer_phone,
            'status' => 'new',
            'source' => 'quote', // Fuente específica de cotización
            'priority' => 'high', // Si cotizó, tiene alto interés
            'interest_type' => 'buy',
            'budget_min' => $quote->vehicle_price * 0.8,
            'budget_max' => $quote->vehicle_price * 1.2,
            'budget_currency' => $quote->currency,
            'notes' => "Lead creado desde cotización del evento: {$quote->event_name}\n" .
                "Cotización: Cuota mensual ₡" . number_format($quote->monthly_payment) . "\n" .
                "Prima: " . $quote->down_payment_percent . "% - Plazo: {$quote->term_months} meses\n" .
                "Tasa: {$quote->interest_rate}%",
            'first_contact_at' => $quote->created_at,
            'event_name' => $quote->event_name,
        ]);

        // Registrar actividad
        $lead->logActivity('note', [
            'subject' => 'Lead creado desde cotización',
            'description' => "Cliente solicitó cotización en evento. " .
                ($quote->property ? "Vehículo: {$quote->property->brand} {$quote->property->model}" : '') .
                " - Cuota estimada: ₡" . number_format($quote->monthly_payment),
        ]);

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'message' => 'Lead creado en el CRM exitosamente'
        ]);
    }

    /**
     * API: Estadisticas en tiempo real
     */
    public function statsRealtime(Request $request)
    {
        $eventName = $request->get('event');
        $user = Auth::user();
        $companyId = $user->isSuperAdmin() ? null : $user->company_id;

        $leads = EventLead::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($eventName, fn($q) => $q->where('event_name', $eventName))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN interest_level = "hot" THEN 1 ELSE 0 END) as hot,
                SUM(CASE WHEN contacted = 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN contacted = 1 THEN 1 ELSE 0 END) as contacted
            ')->first()->toArray();

        return response()->json([
            'leads' => $leads,
            'views_today' => VehicleEventView::whereDate('created_at', today())
                ->when($eventName, fn($q) => $q->where('event_name', $eventName))
                ->count(),
            'quotes_today' => VehicleQuote::whereDate('created_at', today())
                ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->when($eventName, fn($q) => $q->where('event_name', $eventName))
                ->count(),
            'qr_scans_today' => QrScan::whereDate('last_scanned_at', today())
                ->when($eventName, fn($q) => $q->where('event_name', $eventName))
                ->sum('scan_count'),
            'top_vehicle' => VehicleEventView::topViewed($eventName, 1)->first(),
        ]);
    }

    /**
     * Detectar tipo de dispositivo
     */
    private function detectDeviceType(Request $request)
    {
        $userAgent = $request->header('User-Agent', '');

        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }
        if (preg_match('/mobile|android|iphone/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }
}
