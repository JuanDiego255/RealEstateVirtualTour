<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vehicle;
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
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class KioskController extends Controller
{
    /**
     * Vista principal del modo kiosko
     */
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', 1);
        $eventName = $request->get('event');

        $settings = KioskSetting::getActiveForCompany($companyId)
            ?? new KioskSetting(KioskSetting::defaults());

        // Obtener vehículos para el kiosko
        $query = Vehicle::where('status', true)
            ->whereHas('scenes', function($q) {
                $q->whereNotNull('spin_id');
            });

        // Filtrar por vehículos destacados si están configurados
        if (!empty($settings->featured_vehicle_ids)) {
            $query->whereIn('id', $settings->featured_vehicle_ids);
        }

        // Excluir vehículos si están configurados
        if (!empty($settings->excluded_vehicle_ids)) {
            $query->whereNotIn('id', $settings->excluded_vehicle_ids);
        }

        $vehicles = $query->with(['scenes' => function($q) {
            $q->whereNotNull('spin_id')->with('spin');
        }])->get();

        return view('kiosk.index', compact('vehicles', 'settings', 'eventName'));
    }

    /**
     * Vista de vehículo individual en modo kiosko
     */
    public function vehicle(Request $request, $id)
    {
        $vehicle = Vehicle::with(['scenes' => function($q) {
            $q->whereNotNull('spin_id')->with('spin');
        }])->findOrFail($id);

        $eventName = $request->get('event');
        $source = $request->get('qr') ? 'qr' : 'kiosk';

        // Registrar vista
        $sessionId = $request->session()->getId();
        VehicleEventView::create([
            'vehicle_id' => $id,
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
        $colors = VehicleColor::getForVehicle($id);
        $testDriveVideos = TestDriveVideo::getForVehicle($id);
        $qrData = QrScan::generateForVehicle($id, $eventName);

        $settings = KioskSetting::getActiveForCompany($vehicle->category->company_id ?? 1)
            ?? new KioskSetting(KioskSetting::defaults());

        return view('kiosk.vehicle', compact(
            'vehicle', 'spin', 'hotspots', 'colors', 'testDriveVideos',
            'qrData', 'settings', 'eventName'
        ));
    }

    /**
     * API: Obtener datos del vehículo (para navegación AJAX)
     */
    public function vehicleData(Request $request, $id)
    {
        $vehicle = Vehicle::with(['scenes' => function($q) {
            $q->whereNotNull('spin_id')->with('spin');
        }])->findOrFail($id);

        $spin = $vehicle->activeSpin;

        return response()->json([
            'vehicle' => $vehicle,
            'spin' => $spin,
            'hotspots' => $spin ? SpinHotspot::getForSpin($spin->id) : [],
            'colors' => VehicleColor::getForVehicle($id),
            'test_drive_videos' => TestDriveVideo::getForVehicle($id),
        ]);
    }

    /**
     * Generar código QR para un vehículo
     */
    public function generateQr(Request $request, $vehicleId)
    {
        $eventName = $request->get('event');
        $qrData = QrScan::generateForVehicle($vehicleId, $eventName);

        $url = url("/kiosk/vehicle/{$vehicleId}?qr={$qrData->qr_code}");

        if ($request->get('format') === 'svg') {
            $qr = QrCode::format('svg')
                ->size(300)
                ->margin(2)
                ->generate($url);

            return response($qr)->header('Content-Type', 'image/svg+xml');
        }

        $qr = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($url);

        return response($qr)->header('Content-Type', 'image/png');
    }

    /**
     * Calcular cotización de financiamiento
     */
    public function calculateQuote(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'vehicle_price' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'term_months' => 'required|integer|in:12,24,36,48,60,72,84',
            'interest_rate' => 'required|numeric|min:0|max:100',
        ]);

        $quote = VehicleQuote::generateQuote(
            $request->vehicle_price,
            $request->down_payment,
            $request->term_months,
            $request->interest_rate
        );

        return response()->json($quote);
    }

    /**
     * Guardar y enviar cotización
     */
    public function saveQuote(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'required|string|max:20',
            'vehicle_price' => 'required|numeric',
            'down_payment' => 'required|numeric',
            'term_months' => 'required|integer',
            'interest_rate' => 'required|numeric',
        ]);

        $quoteData = VehicleQuote::generateQuote(
            $request->vehicle_price,
            $request->down_payment,
            $request->term_months,
            $request->interest_rate
        );

        $quote = VehicleQuote::create([
            'vehicle_id' => $request->vehicle_id,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'vehicle_price' => $quoteData['vehicle_price'],
            'down_payment' => $quoteData['down_payment'],
            'down_payment_percent' => $quoteData['down_payment_percent'],
            'term_months' => $quoteData['term_months'],
            'interest_rate' => $quoteData['interest_rate'],
            'monthly_payment' => $quoteData['monthly_payment'],
            'total_interest' => $quoteData['total_interest'],
            'total_amount' => $quoteData['total_amount'],
            'currency' => $request->get('currency', 'CRC'),
            'event_name' => $request->get('event_name'),
        ]);

        // Actualizar estadísticas
        VehicleEventView::where('vehicle_id', $request->vehicle_id)
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
     * Generar PDF de cotización
     */
    public function quotePdf($quoteId)
    {
        $quote = VehicleQuote::with('vehicle')->findOrFail($quoteId);

        $pdf = Pdf::loadView('kiosk.quote-pdf', compact('quote'));

        $quote->update([
            'pdf_generated' => true,
            'pdf_path' => "quotes/quote-{$quoteId}.pdf",
        ]);

        return $pdf->download("cotizacion-{$quote->vehicle->brand}-{$quote->vehicle->model}.pdf");
    }

    /**
     * Enviar cotización por email
     */
    public function sendQuoteEmail(Request $request, $quoteId)
    {
        $quote = VehicleQuote::with('vehicle')->findOrFail($quoteId);

        if (!$quote->customer_email) {
            return response()->json(['error' => 'No hay email registrado'], 400);
        }

        // Aquí se enviaría el email (simplificado)
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
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        $lead = EventLead::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'vehicle_id' => $request->vehicle_id,
            'company_id' => $request->get('company_id'),
            'source' => $request->get('source', 'event'),
            'event_name' => $request->get('event_name'),
            'interest_level' => $request->get('interest_level', 'medium'),
            'notes' => $request->get('notes'),
            'vehicles_viewed' => $request->get('vehicles_viewed', []),
        ]);

        // Actualizar estadísticas
        if ($request->vehicle_id) {
            VehicleEventView::where('vehicle_id', $request->vehicle_id)
                ->where('session_id', $request->session()->getId())
                ->latest()
                ->first()
                ?->update(['lead_captured' => true]);
        }

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
        ]);
    }

    /**
     * Actualizar duración de vista
     */
    public function updateViewDuration(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'duration' => 'required|integer|min:0',
        ]);

        VehicleEventView::where('vehicle_id', $request->vehicle_id)
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
                'vehicle_ids' => [],
                'event_name' => $request->get('event_name'),
            ]);
        }

        if ($request->has('add_vehicle')) {
            $wishlist->addVehicle($request->add_vehicle);
        }

        if ($request->has('remove_vehicle')) {
            $wishlist->removeVehicle($request->remove_vehicle);
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
        $vehicles = $wishlist->vehicles;

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
                ->with('error', 'Selecciona al menos 2 vehículos para comparar');
        }

        $vehicles = Vehicle::with(['scenes' => function($q) {
            $q->whereNotNull('spin_id')->with('spin');
        }])->whereIn('id', $vehicleIds)->get();

        // Registrar comparación
        foreach ($vehicleIds as $vehicleId) {
            VehicleEventView::where('vehicle_id', $vehicleId)
                ->where('session_id', $request->session()->getId())
                ->latest()
                ->first()
                ?->update(['compared' => true]);
        }

        $eventName = $request->get('event');

        return view('kiosk.compare', compact('vehicles', 'eventName'));
    }

    /**
     * Dashboard de estadísticas del evento
     */
    public function dashboard(Request $request)
    {
        $eventName = $request->get('event');

        // Top vehículos vistos
        $topViewed = VehicleEventView::topViewed($eventName, 10);

        // Top QR escaneados
        $topQrScans = QrScan::topScanned($eventName, 10);

        // Estadísticas de leads
        $leadStats = EventLead::eventStats($eventName);

        // Cotizaciones del día
        $quotesToday = VehicleQuote::whereDate('created_at', today())
            ->when($eventName, fn($q) => $q->where('event_name', $eventName))
            ->count();

        // Vistas por hora (últimas 24h)
        $viewsByHour = VehicleEventView::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', now()->subHours(24))
            ->when($eventName, fn($q) => $q->where('event_name', $eventName))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Leads recientes
        $recentLeads = EventLead::with('vehicle')
            ->when($eventName, fn($q) => $q->where('event_name', $eventName))
            ->latest()
            ->limit(20)
            ->get();

        return view('kiosk.dashboard', compact(
            'topViewed', 'topQrScans', 'leadStats', 'quotesToday',
            'viewsByHour', 'recentLeads', 'eventName'
        ));
    }

    /**
     * API: Estadísticas en tiempo real
     */
    public function statsRealtime(Request $request)
    {
        $eventName = $request->get('event');

        return response()->json([
            'leads' => EventLead::eventStats($eventName),
            'views_today' => VehicleEventView::whereDate('created_at', today())
                ->when($eventName, fn($q) => $q->where('event_name', $eventName))
                ->count(),
            'quotes_today' => VehicleQuote::whereDate('created_at', today())
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
