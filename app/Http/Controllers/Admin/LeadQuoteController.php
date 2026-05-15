<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadQuote;
use App\Lead;
use App\Properties;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadQuoteController extends Controller
{
    private function authorizeQuote(LeadQuote $quote): void
    {
        if ($quote->company_id !== Auth::user()->company_id) abort(403);
    }

    public function index(Request $request)
    {
        $user      = Auth::user();
        $companyId = $user->company_id;

        $query = LeadQuote::with(['lead', 'creator', 'property'])
            ->where('company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('quote_number', 'like', "%{$s}%");
            });
        }

        $quotes = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = [
            'total'    => LeadQuote::where('company_id', $companyId)->count(),
            'draft'    => LeadQuote::where('company_id', $companyId)->where('status', 'draft')->count(),
            'sent'     => LeadQuote::where('company_id', $companyId)->whereIn('status', ['sent','viewed'])->count(),
            'accepted' => LeadQuote::where('company_id', $companyId)->where('status', 'accepted')->count(),
        ];

        $statuses = LeadQuote::getStatuses();
        return view('admin.crm.quotes.index', compact('quotes', 'stats', 'statuses'));
    }

    public function create(Request $request)
    {
        $user      = Auth::user();
        $companyId = $user->company_id;

        $leads = Lead::where('company_id', $companyId)->orderBy('name')->get();
        $properties = Properties::realEstate()
            ->whereHas('category', fn($q) => $q->where('company_id', $companyId))
            ->orderBy('name')
            ->get();

        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::where('company_id', $companyId)->find($request->lead_id);
        }

        return view('admin.crm.quotes.create', compact('leads', 'properties', 'lead'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id'       => 'required|exists:leads,id',
            'property_id'   => 'nullable|exists:properties,id',
            'title'         => 'required|string|max:200',
            'notes'         => 'nullable|string|max:2000',
            'validity_days' => 'required|integer|min:1|max:365',
            'currency'      => 'required|in:CRC,USD',
            'discount_pct'  => 'nullable|numeric|min:0|max:100',
            'items'         => 'required|array|min:1',
            'items.*.description' => 'required|string|max:300',
            'items.*.qty'   => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        Lead::where('company_id', $user->company_id)->findOrFail($validated['lead_id']);

        $items    = $validated['items'];
        $subtotal = array_sum(array_map(fn($i) => $i['qty'] * $i['price'], $items));
        $discount = $validated['discount_pct'] ?? 0;
        $total    = $subtotal * (1 - $discount / 100);

        LeadQuote::create([
            'company_id'    => $user->company_id,
            'user_id'       => $user->id,
            'lead_id'       => $validated['lead_id'],
            'property_id'   => $validated['property_id'] ?? null,
            'title'         => $validated['title'],
            'notes'         => $validated['notes'] ?? null,
            'validity_days' => $validated['validity_days'],
            'currency'      => $validated['currency'],
            'items'         => $items,
            'subtotal'      => $subtotal,
            'discount_pct'  => $discount,
            'total'         => $total,
            'status'        => 'draft',
        ]);

        return redirect()->route('admin.crm.quotes.index')
            ->with('success', 'Cotización creada correctamente.');
    }

    public function show(LeadQuote $quote)
    {
        $this->authorizeQuote($quote);
        $quote->load(['lead', 'property', 'creator', 'company']);
        return view('admin.crm.quotes.show', compact('quote'));
    }

    public function updateStatus(Request $request, LeadQuote $quote)
    {
        $this->authorizeQuote($quote);
        $request->validate(['status' => 'required|in:draft,sent,viewed,accepted,rejected']);

        $data = ['status' => $request->status];
        if ($request->status === 'sent'     && !$quote->sent_at)     $data['sent_at']      = now();
        if ($request->status === 'viewed'   && !$quote->viewed_at)   $data['viewed_at']    = now();
        if (in_array($request->status, ['accepted','rejected']) && !$quote->responded_at) $data['responded_at'] = now();

        $quote->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $quote->status_label]);
        }
        return back()->with('success', 'Estado actualizado a: ' . $quote->status_label);
    }

    public function destroy(LeadQuote $quote)
    {
        $this->authorizeQuote($quote);
        $quote->delete();
        return redirect()->route('admin.crm.quotes.index')->with('success', 'Cotización eliminada.');
    }

    public function pdf(LeadQuote $quote)
    {
        $this->authorizeQuote($quote);
        $quote->load(['lead', 'property', 'creator', 'company']);

        $company   = $quote->company;
        $logoPath  = null;
        if ($company && $company->logo) {
            $p = public_path('storage/' . $company->logo);
            if (file_exists($p)) $logoPath = $p;
        }

        $printDate = now()->format('d/m/Y H:i');
        $filename  = 'cotizacion-' . $quote->quote_number;

        $pdf = Pdf::loadView('admin.crm.reports.pdf.quote', compact('quote','company','logoPath','printDate'))
            ->setPaper('letter', 'portrait')
            ->setOptions(['dpi'=>110,'defaultFont'=>'helvetica','isRemoteEnabled'=>false,'isHtml5ParserEnabled'=>true]);

        return $pdf->stream($filename . '.pdf');
    }
}
