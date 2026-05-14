<?php

namespace App\Http\Controllers;

use App\Lead;
use App\Models\CrmForm;
use App\Models\CrmFormSubmission;
use Illuminate\Http\Request;

class CrmFormPublicController extends Controller
{
    public function show(string $slug)
    {
        $form = CrmForm::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return view('crm.form-public', compact('form'));
    }

    public function submit(Request $request, string $slug)
    {
        $form = CrmForm::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Build dynamic validation rules from form fields
        $rules = [];
        foreach ($form->fields as $field) {
            $fieldName = $field['name'];
            $required  = !empty($field['required']);
            $type      = $field['type'] ?? 'text';

            $rule = $required ? 'required' : 'nullable';

            if ($type === 'email') {
                $rule .= '|email';
            } elseif ($type === 'number') {
                $rule .= '|numeric';
            } elseif ($type === 'tel') {
                $rule .= '|string|max:30';
            } elseif ($type === 'textarea' || $type === 'text') {
                $rule .= '|string|max:1000';
            } elseif ($type === 'checkbox') {
                $rule = 'nullable|boolean';
            }

            $rules[$fieldName] = $rule;
        }

        $data = $request->validate($rules);

        // Map common field names to Lead columns
        $leadName  = $data['name']    ?? $data['nombre']    ?? null;
        $leadEmail = $data['email']   ?? $data['correo']    ?? null;
        $leadPhone = $data['phone']   ?? $data['telefono']  ?? $data['tel'] ?? null;
        $leadMsg   = $data['message'] ?? $data['mensaje']   ?? null;

        // Create Lead
        $lead = Lead::create([
            'company_id'      => $form->company_id,
            'user_id'         => $form->default_assigned_to,
            'name'            => $leadName  ?? 'Sin nombre',
            'email'           => $leadEmail,
            'phone'           => $leadPhone,
            'notes'           => $leadMsg,
            'source'          => $form->default_source,
            'status'          => Lead::STATUS_NEW,
            'first_contact_at'=> now(),
        ]);

        // Create submission record
        CrmFormSubmission::create([
            'crm_form_id' => $form->id,
            'lead_id'     => $lead->id,
            'data'        => $data,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'referrer'    => $request->headers->get('referer'),
        ]);

        // Increment counter
        $form->increment('submissions_count');

        // Response
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $form->success_message,
                'redirect'=> $form->redirect_url,
            ]);
        }

        if ($form->redirect_url) {
            return redirect($form->redirect_url);
        }

        return back()->with('success', $form->success_message);
    }
}
