<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PipelineRule;
use Illuminate\Http\Request;

class PipelineRuleController extends Controller
{
    private function authorizeRule(PipelineRule $rule): void
    {
        if ($rule->company_id !== auth()->user()->company_id) abort(403);
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;
        $rules = PipelineRule::where('company_id', $companyId)->orderBy('trigger_stage')->get();
        $actions = PipelineRule::getActions();
        $statuses = \App\Lead::getStatuses();
        return view('admin.crm.pipeline-rules.index', compact('rules', 'actions', 'statuses'));
    }

    public function create()
    {
        $actions  = PipelineRule::getActions();
        $statuses = \App\Lead::getStatuses();
        return view('admin.crm.pipeline-rules.create', compact('actions', 'statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'trigger_stage'  => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'days_inactive'  => 'required|integer|min:1|max:365',
            'action'         => 'required|in:alert,create_reminder,notify_admin',
            'action_message' => 'nullable|string|max:500',
            'is_active'      => 'nullable|boolean',
        ]);
        $data['company_id'] = auth()->user()->company_id;
        $data['is_active']  = $request->boolean('is_active', true);
        PipelineRule::create($data);
        return redirect()->route('admin.crm.pipeline-rules.index')->with('success', 'Regla creada correctamente.');
    }

    public function edit(PipelineRule $rule)
    {
        $this->authorizeRule($rule);
        $actions  = PipelineRule::getActions();
        $statuses = \App\Lead::getStatuses();
        return view('admin.crm.pipeline-rules.edit', compact('rule', 'actions', 'statuses'));
    }

    public function update(Request $request, PipelineRule $rule)
    {
        $this->authorizeRule($rule);
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'trigger_stage'  => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'days_inactive'  => 'required|integer|min:1|max:365',
            'action'         => 'required|in:alert,create_reminder,notify_admin',
            'action_message' => 'nullable|string|max:500',
            'is_active'      => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', false);
        $rule->update($data);
        return redirect()->route('admin.crm.pipeline-rules.index')->with('success', 'Regla actualizada.');
    }

    public function destroy(PipelineRule $rule)
    {
        $this->authorizeRule($rule);
        $rule->delete();
        return redirect()->route('admin.crm.pipeline-rules.index')->with('success', 'Regla eliminada.');
    }

    public function toggle(PipelineRule $rule)
    {
        $this->authorizeRule($rule);
        $rule->update(['is_active' => !$rule->is_active]);
        return response()->json(['success' => true, 'is_active' => $rule->is_active]);
    }
}
