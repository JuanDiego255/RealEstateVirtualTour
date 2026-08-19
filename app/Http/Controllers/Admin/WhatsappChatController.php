<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyWhatsappBot;
use App\Models\WhatsappChat;
use App\Models\WhatsappConversation;
use App\Services\WhatsAppCloudService;
use Illuminate\Http\Request;

/**
 * Panel de conversaciones de WhatsApp (operación del negocio): ver chats,
 * tomar el control (pausar el bot) y responder a mano dentro de la ventana 24 h.
 */
class WhatsappChatController extends Controller
{
    private function guard()
    {
        $user = auth()->user();
        abort_unless($user->canAccessModule('whatsapp'), 403, 'Sin permiso para el bot de WhatsApp.');
        return $user;
    }

    /**
     * Empresa por la que se filtra (null = superadmin ve todo).
     */
    private function companyScope($user): ?int
    {
        return $user->isSuperAdmin() ? null : $user->company_id;
    }

    private function authorizeChat($user, WhatsappChat $chat): void
    {
        if (!$user->isSuperAdmin() && $chat->company_id !== $user->company_id) {
            abort(403, 'No tienes acceso a este chat.');
        }
    }

    public function index(Request $request)
    {
        $user = $this->guard();
        $companyId = $this->companyScope($user);
        $search = trim((string) $request->get('q', ''));

        $chats = WhatsappChat::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($search, fn($q) => $q->where(function ($s) use ($search) {
                $s->where('phone', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%");
            }))
            ->orderByDesc('last_message_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.whatsapp.index', compact('chats', 'search'));
    }

    public function show(WhatsappChat $chat)
    {
        $user = $this->guard();
        $this->authorizeChat($user, $chat);

        $chat->loadMissing('lead');

        $messages = WhatsappConversation::forCompany($chat->company_id)
            ->forPhone($chat->phone)
            ->orderBy('created_at')
            ->limit(200)
            ->get();

        // Propuestas de prueba de manejo pendientes de confirmar.
        $proposals = \App\Models\TestDriveProposal::available()
            ? \App\Models\TestDriveProposal::with('vehicle')
                ->where('chat_id', $chat->id)->pending()->latest()->get()
            : collect();

        $chat->update(['last_seen_at' => now()]);

        return view('admin.whatsapp.show', compact('chat', 'messages', 'proposals'));
    }

    /**
     * Poll de mensajes nuevos (AJAX). Devuelve HTML del hilo desde un id.
     */
    public function messages(Request $request, WhatsappChat $chat)
    {
        $user = $this->guard();
        $this->authorizeChat($user, $chat);

        $sinceId = (int) $request->get('since', 0);

        $messages = WhatsappConversation::forCompany($chat->company_id)
            ->forPhone($chat->phone)
            ->when($sinceId, fn($q) => $q->where('id', '>', $sinceId))
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        return response()->json([
            'html'    => view('admin.whatsapp._bubbles', compact('messages'))->render(),
            'last_id' => $messages->max('id') ?: $sinceId,
        ]);
    }

    /**
     * Responder a mano. Envía por la Cloud API, guarda como saliente humano y
     * pausa el bot (una persona tomó el control).
     */
    public function reply(Request $request, WhatsappChat $chat)
    {
        $user = $this->guard();
        $this->authorizeChat($user, $chat);

        $request->validate(['message' => 'required|string|max:4000']);

        $bot = CompanyWhatsappBot::where('company_id', $chat->company_id)->first();
        if (!$bot || !$bot->isUsable()) {
            return back()->with('error', 'El bot no está configurado o está deshabilitado; no se puede enviar.');
        }

        $result = app(WhatsAppCloudService::class)->sendText($bot, $chat->phone, $request->message);

        if (!$result['ok']) {
            return back()->with('error', 'No se pudo enviar: ' . ($result['error'] ?? 'error desconocido'));
        }

        WhatsappConversation::create([
            'company_id'   => $chat->company_id,
            'phone'        => $chat->phone,
            'contact_name' => $chat->contact_name,
            'direction'    => WhatsappConversation::DIRECTION_OUTBOUND,
            'message'      => $request->message,
            'message_type' => 'text',
            'is_human'     => true,
            'wam_id'       => $result['wam_id'],
        ]);

        // Al responder una persona, se toma el control del chat.
        $chat->pauseBot('Respuesta manual del equipo');
        $chat->update(['last_message_at' => now()]);

        return back()->with('success', 'Mensaje enviado.');
    }

    public function pause(WhatsappChat $chat)
    {
        $user = $this->guard();
        $this->authorizeChat($user, $chat);
        $chat->pauseBot('Tomado por el equipo');
        return back()->with('success', 'Tomaste el control de este chat; el bot no responderá.');
    }

    public function resume(WhatsappChat $chat)
    {
        $user = $this->guard();
        $this->authorizeChat($user, $chat);
        $chat->resumeBot();
        return back()->with('success', 'El bot volverá a responder este chat.');
    }

    /**
     * Confirmar una propuesta de prueba de manejo: crea la cita real en la agenda.
     */
    public function confirmProposal(Request $request, \App\Models\TestDriveProposal $proposal)
    {
        $user = $this->guard();
        if (!$user->isSuperAdmin() && $proposal->company_id !== $user->company_id) {
            abort(403);
        }

        $data = $request->validate([
            'date'             => 'required|date',
            'time'             => 'required',
            'duration_minutes' => 'nullable|integer|min:15|max:180',
        ]);

        $when = \Carbon\Carbon::parse($data['date'] . ' ' . $data['time']);
        if ($when->isPast()) {
            return back()->with('error', 'La fecha y hora deben ser a futuro.');
        }
        $duration = (int) ($data['duration_minutes'] ?? $proposal->duration_minutes ?: 45);

        $res = \App\Services\TestDriveScheduler::confirmProposal($proposal, $when, $duration);
        if (!$res['ok']) {
            return back()->with('error', $res['error']);
        }

        return back()->with('success', 'Cita creada y agregada al calendario.');
    }

    /**
     * Descartar una propuesta de prueba de manejo.
     */
    public function dismissProposal(\App\Models\TestDriveProposal $proposal)
    {
        $user = $this->guard();
        if (!$user->isSuperAdmin() && $proposal->company_id !== $user->company_id) {
            abort(403);
        }
        $proposal->update(['status' => \App\Models\TestDriveProposal::STATUS_DISMISSED]);

        return back()->with('success', 'Propuesta descartada.');
    }
}
