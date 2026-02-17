<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            $query = Sale::with(['property', 'seller', 'sellerCompany', 'externalAgent']);
        } else {
            // Show sales where user's company is involved (seller or external)
            $query = Sale::forCompany($user->company)
                ->with(['property', 'seller', 'sellerCompany', 'externalAgent']);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inPeriod($request->start_date, $request->end_date);
        }

        $sales = $query->orderBy('sale_date', 'desc')->paginate(15);

        // Stats
        $stats = [
            'total_sales' => $query->count(),
            'total_amount' => $query->sum('sale_price'),
            'total_commission' => $query->sum('total_commission_amount'),
        ];

        return view('admin.sales.index', compact('sales', 'stats'));
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        $user = Auth::user();

        // Verify access
        if (!$user->isSuperAdmin()) {
            if ($sale->seller_company_id !== $user->company_id && $sale->external_company_id !== $user->company_id) {
                abort(403);
            }
        }

        $sale->load(['property', 'seller', 'sellerCompany', 'externalAgent', 'externalCompany', 'commissionRequest', 'confirmedBy']);

        return view('admin.sales.show', compact('sale'));
    }

    /**
     * Confirm a sale (super admin or seller).
     */
    public function confirm(Sale $sale)
    {
        $user = Auth::user();

        // Only super admin or seller can confirm
        if (!$user->isSuperAdmin() && $sale->seller_id !== $user->id) {
            abort(403);
        }

        if (!$sale->isPending()) {
            return redirect()->back()
                ->with('error', 'Esta venta ya fue procesada.');
        }

        $sale->confirm($user);

        // TODO: Send notification emails to all parties

        return redirect()->back()
            ->with('success', 'Venta confirmada correctamente.');
    }

    /**
     * Cancel a sale.
     */
    public function cancel(Request $request, Sale $sale)
    {
        $user = Auth::user();

        // Only super admin or seller can cancel
        if (!$user->isSuperAdmin() && $sale->seller_id !== $user->id) {
            abort(403);
        }

        if ($sale->isConfirmed()) {
            return redirect()->back()
                ->with('error', 'No se puede cancelar una venta confirmada.');
        }

        $sale->cancel();

        return redirect()->back()
            ->with('success', 'Venta cancelada.');
    }

    /**
     * Generate report of sales.
     */
    public function report(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($user->isSuperAdmin()) {
            $query = Sale::confirmed();
        } else {
            $query = Sale::forCompany($user->company)->confirmed();
        }

        $sales = $query->inPeriod($request->start_date, $request->end_date)
            ->with(['property', 'sellerCompany', 'externalAgent'])
            ->orderBy('sale_date')
            ->get();

        $stats = [
            'period' => [
                'start' => $request->start_date,
                'end' => $request->end_date,
            ],
            'total_sales' => $sales->count(),
            'total_amount' => $sales->sum('sale_price'),
            'total_commission' => $sales->sum('total_commission_amount'),
            'by_currency' => $sales->groupBy('currency')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('sale_price'),
                    'commission' => $group->sum('total_commission_amount'),
                ];
            }),
        ];

        // If user is company (not super admin), calculate their share
        if (!$user->isSuperAdmin()) {
            $companyId = $user->company_id;
            $stats['my_earnings'] = $sales->sum(function ($sale) use ($companyId) {
                if ($sale->seller_company_id === $companyId) {
                    return $sale->owner_share_amount;
                } elseif ($sale->external_company_id === $companyId) {
                    return $sale->agent_share_amount ?? 0;
                }
                return 0;
            });
        }

        return view('admin.sales.report', compact('sales', 'stats'));
    }

    /**
     * Dashboard stats for home page (AJAX).
     */
    public function stats()
    {
        $user = Auth::user();
        $company = $user->company;

        if ($user->isSuperAdmin()) {
            $thisMonth = Sale::confirmed()
                ->whereMonth('sale_date', now()->month)
                ->whereYear('sale_date', now()->year);
        } else {
            $thisMonth = Sale::forCompany($company)
                ->confirmed()
                ->whereMonth('sale_date', now()->month)
                ->whereYear('sale_date', now()->year);
        }

        return response()->json([
            'this_month' => [
                'count' => $thisMonth->count(),
                'amount' => $thisMonth->sum('sale_price'),
                'commission' => $thisMonth->sum('total_commission_amount'),
            ],
        ]);
    }
}
