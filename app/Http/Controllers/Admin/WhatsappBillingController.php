<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Models\CompanyWhatsappBot;
use App\Models\WhatsappBotUsage;
use Illuminate\Http\Request;

/**
 * Facturación y consumo del bot de WhatsApp (visión del superadmin): por empresa
 * y por periodo (mes). Reúne lo que factura cada plan, el costo real y el margen.
 * La unidad es la conversación (ventana de 24 h), no el mensaje.
 */
class WhatsappBillingController extends Controller
{
    /**
     * Resumen de todas las empresas con bot para un periodo.
     */
    public function index(Request $request)
    {
        $period  = $this->validPeriod($request->input('period'));
        $periods = $this->recentPeriods();

        $bots = CompanyWhatsappBot::with('company')->get();

        $rows = $bots->map(function (CompanyWhatsappBot $bot) use ($period) {
            $b = WhatsappBotUsage::billing($bot, $period);
            $b['company']      = optional($bot->company)->name ?: ('Empresa #' . $bot->company_id);
            $b['company_id']   = $bot->company_id;
            $b['plan']         = $bot->plan;
            $b['allow_overage']= (bool) $bot->allow_overage;
            $b['enabled']      = (bool) $bot->enabled;
            return $b;
        })->sortByDesc('used')->values();

        $totals = [
            'used'     => $rows->sum('used'),
            'total'    => $rows->sum('total'),
            'realCost' => $rows->sum('realCost'),
            'profit'   => $rows->sum('profit'),
        ];

        return view('admin.whatsapp.billing.index', compact('rows', 'totals', 'period', 'periods'));
    }

    /**
     * Detalle de una empresa: conversaciones del periodo.
     */
    public function show(Request $request, Company $company)
    {
        $period  = $this->validPeriod($request->input('period'));
        $periods = $this->recentPeriods();

        $bot = CompanyWhatsappBot::where('company_id', $company->id)->firstOrFail();
        $billing = WhatsappBotUsage::billing($bot, $period);

        $conversations = WhatsappBotUsage::where('company_id', $company->id)
            ->where('period', $period)
            ->orderByDesc('window_started_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.whatsapp.billing.show', compact('company', 'bot', 'billing', 'conversations', 'period', 'periods'));
    }

    /**
     * Periodo válido en formato YYYY-MM (default: mes actual).
     */
    private function validPeriod(?string $period): string
    {
        return ($period && preg_match('/^\d{4}-\d{2}$/', $period)) ? $period : now()->format('Y-m');
    }

    /**
     * Últimos 12 meses para el selector.
     *
     * @return array<string,string>  YYYY-MM => "Agosto 2026"
     */
    private function recentPeriods(): array
    {
        $out = [];
        $cursor = now()->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $out[$cursor->format('Y-m')] = ucfirst($cursor->translatedFormat('F Y'));
            $cursor->subMonth();
        }
        return $out;
    }
}
