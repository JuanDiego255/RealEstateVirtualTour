<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Scene;
use App\Hotspot;
use App\Properties;
use App\Sector;
use App\Category;
use App\Company;
use App\Subscription;
use App\Sale;
use App\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        } elseif ($user->isCompanyAdmin()) {
            return $this->companyAdminDashboard();
        } else {
            return $this->agentDashboard();
        }
    }

    /**
     * Dashboard para Super Admin - Vista global del sistema
     */
    private function superAdminDashboard()
    {
        $stats = [
            'total_companies' => Company::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'total_properties' => Properties::count(),
            'total_sales' => Sale::count(),
            'revenue_this_month' => Sale::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('sale_price'),
        ];

        // Ventas por mes (año actual)
        $salesByMonth = Sale::selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(sale_price) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // Llenar meses faltantes con ceros
        $months = [];
        $totals = [];
        for ($i = 1; $i <= 12; $i++) {
            $data = $salesByMonth->get($i);
            $months[] = date('M', mktime(0, 0, 0, $i, 1));
            $totals[] = $data ? (float)$data->total : 0;
        }

        // Propiedades por tipo
        $propertiesByType = Properties::selectRaw('property_type, COUNT(*) as count')
            ->groupBy('property_type')
            ->get();

        // Top 10 empresas por ventas
        $topCompanies = Company::withCount('sales')
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get();

        // Ventas recientes
        $recentSales = Sale::with(['property', 'seller', 'buyer'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard.super-admin', compact(
            'stats',
            'months',
            'totals',
            'propertiesByType',
            'topCompanies',
            'recentSales'
        ));
    }

    /**
     * Dashboard para Company Admin - Vista de su empresa
     */
    private function companyAdminDashboard()
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $stats = [
            'total_agents' => User::where('company_id', $companyId)
                ->where('role', User::ROLE_AGENT)
                ->count(),
            'total_properties' => Properties::whereHas('user', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->count(),
            'active_properties' => Properties::whereHas('user', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->available()->count(),
            'total_sales' => Sale::whereHas('seller', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->count(),
            'revenue_this_month' => Sale::whereHas('seller', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('sale_price'),
        ];

        // Ventas por agente
        $salesByAgent = Sale::with('seller')
            ->whereHas('seller', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->selectRaw('seller_id, COUNT(*) as count, SUM(sale_price) as total')
            ->groupBy('seller_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Propiedades por estado
        $propertiesByStatus = Properties::whereHas('user', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Ventas recientes de la empresa
        $recentSales = Sale::with(['property', 'seller'])
            ->whereHas('seller', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard.company-admin', compact(
            'stats',
            'salesByAgent',
            'propertiesByStatus',
            'recentSales'
        ));
    }

    /**
     * Dashboard para Agente - Vista personal
     */
    private function agentDashboard()
    {
        $user = auth()->user();

        $stats = [
            'my_properties' => Properties::where('user_id', $user->id)->count(),
            'active_properties' => Properties::where('user_id', $user->id)->available()->count(),
            'my_sales' => Sale::where('seller_id', $user->id)->count(),
            'total_earned' => Sale::where('seller_id', $user->id)->sum('sale_price'),
            'properties_views' => Properties::where('user_id', $user->id)->sum('views_count'),
        ];

        // Ventas por mes del agente (año actual)
        $salesByMonth = Sale::where('seller_id', $user->id)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(sale_price) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // Llenar meses faltantes
        $months = [];
        $salesCounts = [];
        $totals = [];
        for ($i = 1; $i <= 12; $i++) {
            $data = $salesByMonth->get($i);
            $months[] = date('M', mktime(0, 0, 0, $i, 1));
            $salesCounts[] = $data ? (int)$data->count : 0;
            $totals[] = $data ? (float)$data->total : 0;
        }

        // Propiedades por estado
        $myPropertiesByStatus = Properties::where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Mis propiedades más vistas
        $mostViewedProperties = Properties::where('user_id', $user->id)
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        // Mis ventas recientes
        $myRecentSales = Sale::with(['property', 'buyer'])
            ->where('seller_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard.agent', compact(
            'stats',
            'months',
            'salesCounts',
            'totals',
            'myPropertiesByStatus',
            'mostViewedProperties',
            'myRecentSales'
        ));
    }
}
