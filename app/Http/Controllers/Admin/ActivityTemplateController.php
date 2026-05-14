<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\ActivityTemplate;
use Illuminate\Http\Request;

class ActivityTemplateController extends Controller
{
    /**
     * Display a listing of activity templates, grouped by type.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $templates = ActivityTemplate::where('company_id', $companyId)
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $byType = ActivityTemplate::where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return view('admin.crm.activity-templates.index', compact('templates', 'byType'));
    }

    /**
     * Show the form for creating a new activity template.
     */
    public function create()
    {
        return view('admin.crm.activity-templates.create');
    }

    /**
     * Store a newly created activity template.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'type'    => 'required|in:call,email,whatsapp,visit,meeting,note,other',
            'subject' => 'nullable|string|max:200',
            'body'    => 'required|string|max:2000',
            'stage'   => 'nullable|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();
        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        ActivityTemplate::create($data);

        return redirect()->route('admin.crm.activity-templates.index')
            ->with('success', 'Plantilla de actividad creada correctamente.');
    }

    /**
     * Show the form for editing the specified activity template.
     */
    public function edit(ActivityTemplate $template)
    {
        $this->authorizeTemplate($template);

        return view('admin.crm.activity-templates.edit', compact('template'));
    }

    /**
     * Update the specified activity template.
     */
    public function update(Request $request, ActivityTemplate $template)
    {
        $this->authorizeTemplate($template);

        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'type'    => 'required|in:call,email,whatsapp,visit,meeting,note,other',
            'subject' => 'nullable|string|max:200',
            'body'    => 'required|string|max:2000',
            'stage'   => 'nullable|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', false);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $template->update($data);

        return redirect()->route('admin.crm.activity-templates.index')
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    /**
     * Delete the specified activity template.
     */
    public function destroy(ActivityTemplate $template)
    {
        $this->authorizeTemplate($template);

        $template->delete();

        return redirect()->route('admin.crm.activity-templates.index')
            ->with('success', 'Plantilla eliminada.');
    }

    /**
     * Toggle the is_active flag and return JSON.
     */
    public function toggle(ActivityTemplate $template)
    {
        $this->authorizeTemplate($template);

        $template->update(['is_active' => !$template->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $template->is_active,
            'message'   => $template->is_active ? 'Plantilla activada.' : 'Plantilla desactivada.',
        ]);
    }

    /**
     * Return JSON list of active templates for a given stage + type (AJAX).
     */
    public function forStage(Request $request)
    {
        $request->validate([
            'stage' => 'nullable|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'type'  => 'nullable|in:call,email,whatsapp,visit,meeting,note,other',
        ]);

        $companyId = auth()->user()->company_id;

        $query = ActivityTemplate::where('company_id', $companyId)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('stage')) {
            $query->forStage($request->stage);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $templates = $query->get(['id', 'name', 'type', 'subject', 'body', 'stage']);

        return response()->json($templates);
    }

    /**
     * Ensure the template belongs to the current user's company.
     */
    private function authorizeTemplate(ActivityTemplate $template): void
    {
        if ($template->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}
