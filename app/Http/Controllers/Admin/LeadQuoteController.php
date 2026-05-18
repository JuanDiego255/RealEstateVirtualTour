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
        $quoteType = $request->input('quote_type', 'property');

        $baseRules = [
            'lead_id'       => 'required|exists:leads,id',
            'property_id'   => 'nullable|exists:properties,id',
            'title'         => 'required|string|max:200',
            'notes'         => 'nullable|string|max:2000',
            'validity_days' => 'nullable|integer|min:1|max:365',
            'currency'      => 'nullable|in:CRC,USD',
            'quote_type'    => 'nullable|in:property,vehicle',
        ];

        if ($quoteType === 'vehicle') {
            $baseRules += [
                'vehicle_quote_id'    => 'nullable|exists:vehicle_quotes,id',
                'vq_vehicle_price'    => 'nullable|numeric|min:0',
                'vq_down_payment'     => 'nullable|numeric|min:0',
                'vq_down_payment_pct' => 'nullable|numeric|min:0|max:100',
                'vq_term_months'      => 'nullable|integer|in:12,24,36,48,60,72,84',
                'vq_interest_rate'    => 'nullable|numeric|min:0|max:1',
                'vq_payment_frequency'=> 'nullable|in:monthly,annual',
            ];
        } else {
            $baseRules += [
                'discount_pct'        => 'nullable|numeric|min:0|max:100',
                'items'               => 'nullable|array',
                'items.*.description' => 'required_with:items|string|max:300',
                'items.*.qty'         => 'required_with:items|numeric|min:0',
                'items.*.price'       => 'required_with:items|numeric|min:0',
            ];
        }

        $validated = $request->validate($baseRules);

        $user = Auth::user();
        Lead::where('company_id', $user->company_id)->findOrFail($validated['lead_id']);

        if ($quoteType === 'vehicle') {
            // For vehicle quotes, calculate financials
            $vehiclePrice  = (float)($request->vq_vehicle_price ?? 0);
            $downPayPct    = (float)($request->vq_down_payment_pct ?? 50);
            $downPayment   = $vehiclePrice * ($downPayPct / 100);
            $principal     = $vehiclePrice - $downPayment;
            $termMonths    = (int)($request->vq_term_months ?? 36);
            $rate          = (float)($request->vq_interest_rate ?? 0);
            $freq          = $request->vq_payment_frequency ?? 'monthly';

            // Calculate payment using French amortization
            if ($rate > 0) {
                $periodsPerYear = $freq === 'annual' ? 1 : 12;
                $periodicRate   = $rate / $periodsPerYear;
                $nPeriods       = $freq === 'annual' ? ceil($termMonths / 12) : $termMonths;
                $payment = $principal * ($periodicRate * pow(1 + $periodicRate, $nPeriods)) / (pow(1 + $periodicRate, $nPeriods) - 1);
            } else {
                $periodsPerYear = $freq === 'annual' ? 1 : 12;
                $nPeriods = $freq === 'annual' ? ceil($termMonths / 12) : $termMonths;
                $payment  = $nPeriods > 0 ? $principal / $nPeriods : 0;
            }
            $totalPaid     = $payment * ($freq === 'annual' ? ceil($termMonths / 12) : $termMonths);
            $totalInterest = $totalPaid - $principal;

            $subtotal  = $vehiclePrice;
            $total     = $vehiclePrice;
            $extraData = [
                'quote_type'          => 'vehicle',
                'vq_vehicle_price'    => $vehiclePrice,
                'vq_down_payment'     => $downPayment,
                'vq_down_payment_pct' => $downPayPct,
                'vq_term_months'      => $termMonths,
                'vq_interest_rate'    => $rate,
                'vq_payment_frequency'=> $freq,
                'vq_monthly_payment'  => round($payment, 2),
                'vq_total_interest'   => round(max($totalInterest, 0), 2),
                'vq_total_amount'     => round($totalPaid + $downPayment, 2),
                'vehicle_quote_id'    => $request->vehicle_quote_id,
                'items'               => [],
                'discount_pct'        => 0,
            ];
        } else {
            // Property quote — existing logic
            $items     = $validated['items'] ?? [];
            $subtotal  = count($items) ? array_sum(array_map(fn($i) => $i['qty'] * $i['price'], $items)) : 0;
            $discount  = $validated['discount_pct'] ?? 0;
            $total     = $subtotal * (1 - $discount / 100);
            $extraData = [
                'quote_type'  => 'property',
                'items'       => $items,
                'discount_pct'=> $discount,
            ];
        }

        $quote = LeadQuote::create(array_merge([
            'company_id'    => $user->company_id,
            'user_id'       => $user->id,
            'lead_id'       => $validated['lead_id'],
            'property_id'   => $validated['property_id'] ?? null,
            'title'         => $validated['title'],
            'notes'         => $validated['notes'] ?? null,
            'validity_days' => $validated['validity_days'] ?? 30,
            'currency'      => $validated['currency'] ?? 'CRC',
            'subtotal'      => $subtotal,
            'total'         => $total,
            'status'        => 'draft',
        ], $extraData));

        // If created from lead detail modal, go back to the lead's cotizaciones tab
        if (request()->filled('_back')) {
            return redirect(request()->_back)->with('success', 'Cotización creada correctamente.');
        }

        // If property quote with no items, redirect to quote detail to add items
        if ($quoteType === 'property' && !count($extraData['items'])) {
            return redirect()->route('admin.crm.quotes.show', $quote)
                ->with('success', 'Cotización creada. Agrega los ítems a continuación.');
        }

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
