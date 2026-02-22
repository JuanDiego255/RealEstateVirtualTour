<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\CommissionRequest;
use App\CommissionNegotiation;
use App\Properties;
use App\Sale;
use App\User;
use App\Notifications\CommissionRequestReceived;
use App\Notifications\CommissionRequestUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BolsaController extends Controller
{
    /**
     * Dashboard de la bolsa inmobiliaria.
     */
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;

        // Stats
        $stats = [
            'available_properties' => Properties::acceptsCommission()->count(),
            'my_requests_pending' => CommissionRequest::where('requester_id', $user->id)->active()->count(),
            'received_pending' => CommissionRequest::where('owner_id', $user->id)->active()->count(),
            'my_sales' => $company ? Sale::forCompany($company)->confirmed()->count() : 0,
        ];

        // Recent activity
        $recentRequests = CommissionRequest::forUser($user)
            ->with(['property', 'requester', 'owner'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.bolsa.index', compact('stats', 'recentRequests'));
    }

    /**
     * Inmuebles disponibles para comisión.
     */
    public function available(Request $request)
    {
        $user = Auth::user();

        $query = Properties::acceptsCommission()
            ->with(['category.sector', 'category.company', 'user'])
            ->where('user_id', '!=', $user->id); // Excluir mis propios inmuebles

        // Filters
        if ($request->filled('sector_id')) {
            $query->inSector($request->sector_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        $properties = $query->orderBy('created_at', 'desc')->paginate(12);

        $sectors = \App\Sector::where('status', true)->orderBy('name')->get();

        return view('admin.bolsa.available', compact('properties', 'sectors'));
    }

    /**
     * Mis solicitudes enviadas.
     */
    public function myRequests(Request $request)
    {
        $user = Auth::user();

        $query = CommissionRequest::where('requester_id', $user->id)
            ->with(['property', 'owner', 'ownerCompany']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.bolsa.my-requests', compact('requests'));
    }

    /**
     * Solicitudes recibidas.
     */
    public function incoming(Request $request)
    {
        $user = Auth::user();

        $query = CommissionRequest::where('owner_id', $user->id)
            ->with(['property', 'requester', 'requesterCompany']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.bolsa.incoming', compact('requests'));
    }

    /**
     * Crear solicitud de comisión.
     */
    public function createRequest(Properties $property)
    {
        $user = Auth::user();

        // Validations
        if ($property->user_id === $user->id) {
            return redirect()->back()
                ->with('error', 'No puede solicitar comisión de sus propios inmuebles.');
        }

        if (!$property->canAcceptCommission()) {
            return redirect()->back()
                ->with('error', 'Este inmueble no acepta solicitudes de comisión.');
        }

        // Check if already has an active request
        $existingRequest = CommissionRequest::where('property_id', $property->id)
            ->where('requester_id', $user->id)
            ->active()
            ->first();

        if ($existingRequest) {
            return redirect()->route('admin.bolsa.negotiation', $existingRequest)
                ->with('info', 'Ya tiene una solicitud activa para este inmueble.');
        }

        return view('admin.bolsa.create-request', compact('property'));
    }

    /**
     * Enviar solicitud de comisión.
     */
    public function storeRequest(Request $request, Properties $property)
    {
        $user = Auth::user();
        $company = $user->company;

        $request->validate([
            'proposed_commission' => 'required|numeric|min:0.1|max:100',
            'initial_message' => 'required|string|min:10|max:1000',
        ]);

        // Validations
        if ($property->user_id === $user->id) {
            return redirect()->back()
                ->with('error', 'No puede solicitar comisión de sus propios inmuebles.');
        }

        if (!$property->canAcceptCommission()) {
            return redirect()->back()
                ->with('error', 'Este inmueble no acepta solicitudes de comisión.');
        }

        $commissionRequest = CommissionRequest::create([
            'property_id' => $property->id,
            'requester_id' => $user->id,
            'requester_company_id' => $company->id,
            'owner_id' => $property->user_id,
            'owner_company_id' => $property->category->company_id,
            'proposed_commission' => $request->proposed_commission,
            'initial_message' => $request->initial_message,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        // Notificar al propietario del inmueble
        $owner = User::find($property->user_id);
        if ($owner) {
            $owner->notify(new CommissionRequestReceived($commissionRequest));
        }

        return redirect()->route('admin.bolsa.negotiation', $commissionRequest)
            ->with('success', 'Solicitud enviada correctamente. Espere la respuesta del propietario.');
    }

    /**
     * Ver negociación/chat de una solicitud.
     */
    public function negotiation(CommissionRequest $commissionRequest)
    {
        $user = Auth::user();

        // Verify access
        if ($commissionRequest->requester_id !== $user->id && $commissionRequest->owner_id !== $user->id) {
            abort(403, 'No tiene acceso a esta negociación.');
        }

        $commissionRequest->load(['property', 'requester', 'requesterCompany', 'owner', 'ownerCompany', 'negotiations']);

        // Mark messages as read
        $commissionRequest->markAsReadFor($user);

        return view('admin.bolsa.negotiation', compact('commissionRequest'));
    }

    /**
     * Enviar mensaje en negociación.
     */
    public function sendMessage(Request $request, CommissionRequest $commissionRequest)
    {
        $user = Auth::user();

        // Verify access
        if ($commissionRequest->requester_id !== $user->id && $commissionRequest->owner_id !== $user->id) {
            abort(403);
        }

        // Verify can respond
        if (!$commissionRequest->canRespond()) {
            return redirect()->back()
                ->with('error', 'Esta negociación ya no acepta mensajes.');
        }

        $request->validate([
            'message' => 'required|string|min:1|max:1000',
            'proposed_percentage' => 'nullable|numeric|min:0.1|max:100',
        ]);

        $toUserId = $commissionRequest->requester_id === $user->id
            ? $commissionRequest->owner_id
            : $commissionRequest->requester_id;

        CommissionNegotiation::create([
            'commission_request_id' => $commissionRequest->id,
            'from_user_id' => $user->id,
            'to_user_id' => $toUserId,
            'message' => $request->message,
            'proposed_percentage' => $request->proposed_percentage,
            'is_counter_offer' => $request->filled('proposed_percentage'),
        ]);

        // Update request status if needed
        if ($commissionRequest->isPending()) {
            $commissionRequest->startNegotiation();
        }

        // Update proposed commission if counter offer
        if ($request->filled('proposed_percentage')) {
            $commissionRequest->update(['proposed_commission' => $request->proposed_percentage]);

            // Notificar contra-oferta
            $recipient = User::find($toUserId);
            if ($recipient) {
                $recipient->notify(new CommissionRequestUpdated($commissionRequest, 'counter_offer'));
            }
        }

        return redirect()->back()
            ->with('success', 'Mensaje enviado.');
    }

    /**
     * Aceptar solicitud de comisión.
     */
    public function acceptRequest(Request $request, CommissionRequest $commissionRequest)
    {
        $user = Auth::user();

        // Only owner can accept
        if ($commissionRequest->owner_id !== $user->id) {
            abort(403);
        }

        if (!$commissionRequest->canRespond()) {
            return redirect()->back()
                ->with('error', 'Esta solicitud ya no puede ser aceptada.');
        }

        $request->validate([
            'final_commission' => 'nullable|numeric|min:0.1|max:100',
        ]);

        $finalCommission = $request->final_commission ?? $commissionRequest->proposed_commission;
        $commissionRequest->accept($finalCommission);

        // Notificar al solicitante que fue aceptada
        $requester = User::find($commissionRequest->requester_id);
        if ($requester) {
            $requester->notify(new CommissionRequestUpdated($commissionRequest, 'accepted'));
        }

        return redirect()->back()
            ->with('success', 'Solicitud aceptada. El agente ahora puede vender su inmueble.');
    }

    /**
     * Rechazar solicitud de comisión.
     */
    public function rejectRequest(Request $request, CommissionRequest $commissionRequest)
    {
        $user = Auth::user();

        // Only owner can reject
        if ($commissionRequest->owner_id !== $user->id) {
            abort(403);
        }

        if (!$commissionRequest->canRespond()) {
            return redirect()->back()
                ->with('error', 'Esta solicitud ya no puede ser rechazada.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $commissionRequest->reject();

        // Add rejection message if provided
        if ($request->filled('reason')) {
            CommissionNegotiation::create([
                'commission_request_id' => $commissionRequest->id,
                'from_user_id' => $user->id,
                'to_user_id' => $commissionRequest->requester_id,
                'message' => 'Solicitud rechazada: ' . $request->reason,
            ]);
        }

        // Notificar al solicitante que fue rechazada
        $requester = User::find($commissionRequest->requester_id);
        if ($requester) {
            $requester->notify(new CommissionRequestUpdated($commissionRequest, 'rejected'));
        }

        return redirect()->route('admin.bolsa.incoming')
            ->with('success', 'Solicitud rechazada.');
    }

    /**
     * Cancelar mi solicitud.
     */
    public function cancelRequest(CommissionRequest $commissionRequest)
    {
        $user = Auth::user();

        // Only requester can cancel
        if ($commissionRequest->requester_id !== $user->id) {
            abort(403);
        }

        if (!in_array($commissionRequest->status, ['pending', 'negotiating'])) {
            return redirect()->back()
                ->with('error', 'Esta solicitud no puede ser cancelada.');
        }

        $commissionRequest->cancel();

        return redirect()->route('admin.bolsa.my-requests')
            ->with('success', 'Solicitud cancelada.');
    }

    /**
     * Registrar una venta.
     */
    public function registerSale(Request $request, Properties $property)
    {
        $user = Auth::user();

        // Verify ownership
        if ($property->user_id !== $user->id) {
            abort(403, 'Solo el propietario puede registrar ventas.');
        }

        if ($request->isMethod('get')) {
            // Show form
            $acceptedRequests = CommissionRequest::where('property_id', $property->id)
                ->accepted()
                ->with(['requester', 'requesterCompany'])
                ->get();

            return view('admin.bolsa.register-sale', compact('property', 'acceptedRequests'));
        }

        // Process sale registration
        $request->validate([
            'sale_price' => 'required|numeric|min:1',
            'currency' => 'required|in:CRC,USD',
            'total_commission_percentage' => 'required|numeric|min:0|max:100',
            'commission_request_id' => 'nullable|exists:commission_requests,id',
            'agent_share_percentage' => 'nullable|numeric|min:0|max:100',
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'nullable|string|max:50',
            'buyer_email' => 'nullable|email|max:255',
            'buyer_id_number' => 'nullable|string|max:50',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $company = $user->company;

        // Calculate commission split
        $agentShare = $request->filled('commission_request_id') ? $request->agent_share_percentage : null;
        $commissionData = Sale::calculateCommissionSplit(
            $request->sale_price,
            $request->total_commission_percentage,
            $agentShare
        );

        // Get external agent info if applicable
        $externalAgentId = null;
        $externalCompanyId = null;
        $commissionRequestId = null;

        if ($request->filled('commission_request_id')) {
            $commissionRequest = CommissionRequest::findOrFail($request->commission_request_id);
            $externalAgentId = $commissionRequest->requester_id;
            $externalCompanyId = $commissionRequest->requester_company_id;
            $commissionRequestId = $commissionRequest->id;
        }

        $sale = Sale::create([
            'property_id' => $property->id,
            'seller_id' => $user->id,
            'seller_company_id' => $company->id,
            'external_agent_id' => $externalAgentId,
            'external_company_id' => $externalCompanyId,
            'commission_request_id' => $commissionRequestId,
            'sale_price' => $request->sale_price,
            'currency' => $request->currency,
            'total_commission_percentage' => $commissionData['total_commission_percentage'],
            'total_commission_amount' => $commissionData['total_commission_amount'],
            'owner_share_percentage' => $commissionData['owner_share_percentage'],
            'owner_share_amount' => $commissionData['owner_share_amount'],
            'agent_share_percentage' => $commissionData['agent_share_percentage'],
            'agent_share_amount' => $commissionData['agent_share_amount'],
            'buyer_name' => $request->buyer_name,
            'buyer_phone' => $request->buyer_phone,
            'buyer_email' => $request->buyer_email,
            'buyer_id_number' => $request->buyer_id_number,
            'sale_date' => $request->sale_date,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return redirect()->route('admin.sales.show', $sale)
            ->with('success', 'Venta registrada. Pendiente de confirmación.');
    }

    /**
     * Calculadora de comisiones (AJAX).
     */
    public function calculateCommission(Request $request)
    {
        $request->validate([
            'sale_price' => 'required|numeric|min:0',
            'total_commission' => 'required|numeric|min:0|max:100',
            'agent_share' => 'nullable|numeric|min:0|max:100',
        ]);

        $result = Sale::calculateCommissionSplit(
            $request->sale_price,
            $request->total_commission,
            $request->agent_share
        );

        return response()->json($result);
    }
}
