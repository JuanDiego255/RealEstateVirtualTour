<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Lead;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    /**
     * Display a listing of message templates, grouped by channel.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = MessageTemplate::where('company_id', $companyId)
            ->orderBy('channel')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        $templates  = $query->paginate(20)->withQueryString();
        $byChannel  = MessageTemplate::where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('channel');

        return view('admin.crm.message-templates.index', compact('templates', 'byChannel'));
    }

    /**
     * Show the form for creating a new message template.
     */
    public function create()
    {
        return view('admin.crm.message-templates.create');
    }

    /**
     * Store a newly created message template.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'channel'    => 'required|in:whatsapp,email',
            'stage'      => 'nullable|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'subject'    => 'nullable|string|max:200',
            'body'       => 'required|string|max:5000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        MessageTemplate::create($data);

        return redirect()->route('admin.crm.message-templates.index')
            ->with('success', 'Plantilla de mensaje creada correctamente.');
    }

    /**
     * Show the form for editing the specified message template.
     */
    public function edit(MessageTemplate $template)
    {
        $this->authorizeTemplate($template);

        return view('admin.crm.message-templates.edit', compact('template'));
    }

    /**
     * Update the specified message template.
     */
    public function update(Request $request, MessageTemplate $template)
    {
        $this->authorizeTemplate($template);

        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'channel'    => 'required|in:whatsapp,email',
            'stage'      => 'nullable|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'subject'    => 'nullable|string|max:200',
            'body'       => 'required|string|max:5000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', false);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $template->update($data);

        return redirect()->route('admin.crm.message-templates.index')
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    /**
     * Delete the specified message template.
     */
    public function destroy(MessageTemplate $template)
    {
        $this->authorizeTemplate($template);

        $template->delete();

        return redirect()->route('admin.crm.message-templates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    /**
     * Preview an interpolated message for a given lead (AJAX / POST).
     * Expects: template_id, lead_id, portal_url (optional)
     */
    public function preview(Request $request)
    {
        $request->validate([
            'template_id' => 'required|integer',
            'lead_id'     => 'required|integer',
            'portal_url'  => 'nullable|url',
        ]);

        $companyId = auth()->user()->company_id;

        $template = MessageTemplate::where('id', $request->template_id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $lead = Lead::where('id', $request->lead_id)
            ->where('company_id', $companyId)
            ->with(['user', 'property'])
            ->firstOrFail();

        $agent = $lead->user ?? auth()->user();

        $interpolated = $template->interpolate($lead, $agent, $request->portal_url);

        return response()->json([
            'success'  => true,
            'subject'  => $template->subject,
            'body'     => $interpolated,
            'channel'  => $template->channel,
            'template' => [
                'id'   => $template->id,
                'name' => $template->name,
            ],
        ]);
    }

    /**
     * Ensure the template belongs to the current user's company.
     */
    private function authorizeTemplate(MessageTemplate $template): void
    {
        if ($template->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}
