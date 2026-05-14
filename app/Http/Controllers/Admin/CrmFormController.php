<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmForm;
use App\Models\CrmFormSubmission;
use App\Lead;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CrmFormController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $forms = CrmForm::where('company_id', $user->company_id)
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total'             => CrmForm::where('company_id', $user->company_id)->count(),
            'active'            => CrmForm::where('company_id', $user->company_id)->where('is_active', true)->count(),
            'submissions_total' => CrmFormSubmission::whereHas('form', fn($q) => $q->where('company_id', $user->company_id))
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('admin.crm.forms.index', compact('forms', 'stats'));
    }

    public function create()
    {
        $defaultFields = CrmForm::defaultFields();
        $sources       = Lead::getSources();
        return view('admin.crm.forms.create', compact('defaultFields', 'sources'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'title'            => 'required|string|max:200',
            'description'      => 'nullable|string|max:500',
            'default_source'   => 'required|string',
            'success_message'  => 'required|string|max:300',
            'redirect_url'     => 'nullable|url',
            'fields'           => 'required|array|min:1',
            'fields.*.name'    => 'required|string',
            'fields.*.label'   => 'required|string',
            'fields.*.type'    => 'required|in:text,email,tel,number,textarea,select,checkbox',
            'fields.*.required'=> 'boolean',
        ]);

        $user = auth()->user();

        CrmForm::create(array_merge($validated, [
            'company_id' => $user->company_id,
            'created_by' => $user->id,
            'is_active'  => true,
        ]));

        return redirect()->route('admin.crm.forms.index')
            ->with('success', 'Formulario creado exitosamente.');
    }

    public function show(CrmForm $form)
    {
        $this->authorizeForm($form);

        $submissions = CrmFormSubmission::where('crm_form_id', $form->id)
            ->with('lead')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.crm.forms.show', compact('form', 'submissions'));
    }

    public function edit(CrmForm $form)
    {
        $this->authorizeForm($form);
        $sources = Lead::getSources();
        return view('admin.crm.forms.edit', compact('form', 'sources'));
    }

    public function update(Request $request, CrmForm $form)
    {
        $this->authorizeForm($form);

        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'title'            => 'required|string|max:200',
            'description'      => 'nullable|string|max:500',
            'default_source'   => 'required|string',
            'success_message'  => 'required|string|max:300',
            'redirect_url'     => 'nullable|url',
            'fields'           => 'required|array|min:1',
            'fields.*.name'    => 'required|string',
            'fields.*.label'   => 'required|string',
            'fields.*.type'    => 'required|in:text,email,tel,number,textarea,select,checkbox',
            'fields.*.required'=> 'boolean',
        ]);

        $form->update($validated);

        return redirect()->route('admin.crm.forms.show', $form)
            ->with('success', 'Formulario actualizado.');
    }

    public function destroy(CrmForm $form)
    {
        $this->authorizeForm($form);
        $form->delete();

        return redirect()->route('admin.crm.forms.index')
            ->with('success', 'Formulario eliminado.');
    }

    public function toggle(CrmForm $form)
    {
        $this->authorizeForm($form);
        $form->update(['is_active' => !$form->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $form->is_active,
            'message'   => $form->is_active ? 'Formulario activado.' : 'Formulario desactivado.',
        ]);
    }

    protected function authorizeForm(CrmForm $form): void
    {
        if ($form->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}
